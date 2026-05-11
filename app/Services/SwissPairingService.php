<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Round;
use App\Models\Pairing;
use App\Models\Standing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SwissPairingService
{
    /**
     * Generate pairings for the next Swiss round.
     */
    public function generateRound(Tournament $tournament): Round
    {
        return DB::transaction(function () use ($tournament) {
            $currentRoundNumber = $tournament->currentRoundNumber();
            $nextRoundNumber    = $currentRoundNumber + 1;

            // Create the new round
            $round = $tournament->rounds()->create([
                'number'        => $nextRoundNumber,
                'type'          => 'swiss',
                'status'        => 'active',
                'started_at'    => now(),
                'time_limit_at' => now()->addMinutes($tournament->match_time_minutes ?? 50),
            ]);

            $players = $this->getActivePlayers($tournament);

            if ($currentRoundNumber === 0) {
                // Round 1: random pairings
                $players = $players->shuffle();
            } else {
                // Sort by match_points desc, then OWP desc
                $players = $this->sortPlayersByStanding($tournament, $players);
            }

            $this->createPairings($round, $players, $tournament);

            return $round;
        });
    }

    /**
     * Get all active (confirmed, not dropped) players for the tournament.
     */
    private function getActivePlayers(Tournament $tournament): Collection
    {
        return $tournament->confirmedRegistrations()
            ->whereIn('status', ['confirmed'])
            ->with('user')
            ->get()
            ->map(fn($r) => $r->user);
    }

    /**
     * Sort players by their current standing (points → OWP → OOWP).
     */
    private function sortPlayersByStanding(Tournament $tournament, Collection $players): Collection
    {
        $standings = $tournament->standings()
            ->whereIn('user_id', $players->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return $players->sort(function ($a, $b) use ($standings) {
            $sa = $standings->get($a->id);
            $sb = $standings->get($b->id);
            $pa = $sa?->match_points ?? 0;
            $pb = $sb?->match_points ?? 0;
            if ($pa !== $pb) return $pb - $pa;
            $owpa = $sa?->opponent_win_pct ?? 0;
            $owpb = $sb?->opponent_win_pct ?? 0;
            if ($owpa !== $owpb) return $owpb <=> $owpa;
            return ($sb?->opp_opp_win_pct ?? 0) <=> ($sa?->opp_opp_win_pct ?? 0);
        })->values();
    }

    /**
     * Create pairings from a sorted list of players.
     * Uses a simple bracket-style Swiss: pair from top, respecting no-repeat constraint.
     */
    private function createPairings(Round $round, Collection $players, Tournament $tournament): void
    {
        $paired    = [];
        $tableNum  = 1;

        // Get previously played opponent pairs to avoid repeats
        $previousPairs = $this->getPreviousPairs($tournament);

        $playerList = $players->values()->all();
        $n = count($playerList);

        // Handle odd number → bye
        $byePlayer = null;
        if ($n % 2 !== 0) {
            $byePlayer = $this->selectByePlayer($playerList, $tournament, $previousPairs);
            $playerList = array_filter($playerList, fn($p) => $p->id !== $byePlayer->id);
            $playerList = array_values($playerList);
        }

        // Create bye pairing
        if ($byePlayer) {
            Pairing::create([
                'round_id'    => $round->id,
                'player1_id'  => $byePlayer->id,
                'player2_id'  => null,
                'table_number' => null,
                'result'      => 'bye',
                'result_confirmed' => true,
            ]);
        }

        // Pair players using Swiss algorithm
        $used = [];
        for ($i = 0; $i < count($playerList); $i++) {
            if (in_array($i, $used)) continue;
            $p1 = $playerList[$i];
            $matchedJ = null;

            // Find the first non-used player that hasn't played p1 before
            for ($j = $i + 1; $j < count($playerList); $j++) {
                if (in_array($j, $used)) continue;
                $p2 = $playerList[$j];
                $pairKey = $this->pairKey($p1->id, $p2->id);
                if (!isset($previousPairs[$pairKey])) {
                    $matchedJ = $j;
                    break;
                }
            }

            // If no valid match (all played each other), just pick the next available
            if ($matchedJ === null) {
                for ($j = $i + 1; $j < count($playerList); $j++) {
                    if (!in_array($j, $used)) {
                        $matchedJ = $j;
                        break;
                    }
                }
            }

            if ($matchedJ !== null) {
                $p2 = $playerList[$matchedJ];
                Pairing::create([
                    'round_id'    => $round->id,
                    'player1_id'  => $p1->id,
                    'player2_id'  => $p2->id,
                    'table_number' => $tableNum++,
                    'result'      => 'pending',
                ]);
                $used[] = $i;
                $used[] = $matchedJ;
            }
        }
    }

    /**
     * Select bye candidate: lowest points, hasn't had bye before.
     */
    private function selectByePlayer(array $players, Tournament $tournament, array $previousPairs): object
    {
        $userIds = array_map(fn($p) => $p->id, $players);
        $standings = $tournament->standings()
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $playersWithByes = $tournament->rounds()
            ->with('pairings')
            ->get()
            ->flatMap(fn($r) => $r->pairings()->whereNull('player2_id')->pluck('player1_id'))
            ->unique()
            ->all();

        // Prefer players who haven't had a bye, with lowest points
        usort($players, function ($a, $b) use ($standings, $playersWithByes) {
            $aHadBye = in_array($a->id, $playersWithByes) ? 1 : 0;
            $bHadBye = in_array($b->id, $playersWithByes) ? 1 : 0;
            if ($aHadBye !== $bHadBye) return $aHadBye - $bHadBye;
            $pa = $standings->get($a->id)?->match_points ?? 0;
            $pb = $standings->get($b->id)?->match_points ?? 0;
            return $pa - $pb; // Ascending: lowest points first
        });

        // Lowest points and no-bye will theoretically end up at array index 0,
        // Wait, usort $pa - $pb makes smaller values come BEFORE larger values.
        // So the player with the lowest points is at $players[0] after sorting.
        return $players[0];
    }

    private function getPreviousPairs(Tournament $tournament): array
    {
        $pairs = [];
        $rounds = $tournament->rounds()->with('pairings')->get();
        foreach ($rounds as $round) {
            foreach ($round->pairings as $pairing) {
                if ($pairing->player2_id) {
                    $pairs[$this->pairKey($pairing->player1_id, $pairing->player2_id)] = true;
                }
            }
        }
        return $pairs;
    }

    private function pairKey(int $a, int $b): string
    {
        return min($a, $b) . '-' . max($a, $b);
    }

    /**
     * Calculate the number of Swiss rounds needed for a given number of players.
     */
    public static function calculateRounds(int $playerCount): int
    {
        if ($playerCount <= 4)   return 3;
        if ($playerCount <= 8)   return 3;
        if ($playerCount <= 16)  return 4;
        if ($playerCount <= 32)  return 5;
        if ($playerCount <= 64)  return 6;
        if ($playerCount <= 128) return 7;
        return 8;
    }
}
