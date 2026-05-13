<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Standing;
use App\Models\Pairing;

class StandingsService
{
    /**
     * Recalculate all standings for a tournament after a round.
     */
    public function recalculate(Tournament $tournament): void
    {
        $players = $tournament->confirmedRegistrations()->pluck('user_id');

        // First pass: calculate basic stats
        foreach ($players as $userId) {
            $this->updatePlayerStats($tournament, $userId);
        }

        // Second pass: calculate OWP for each player
        foreach ($players as $userId) {
            $this->updatePlayerOWP($tournament, $userId);
        }

        // Third pass: calculate OOWP
        foreach ($players as $userId) {
            $this->updatePlayerOOWP($tournament, $userId);
        }

        // Final pass: assign positions
        $this->assignPositions($tournament);
    }

    private function updatePlayerStats(Tournament $tournament, int $userId): void
    {
        $pairings = $this->getPlayerPairings($tournament, $userId);

        $match_points = 0;
        $matches_played = 0;
        $matches_won = 0;
        $matches_lost = 0;
        $matches_drawn = 0;
        $games_won = 0;
        $games_lost = 0;
        $games_played = 0;
        $byes_received = 0;

        foreach ($pairings as $pairing) {
            if ($pairing->result === 'pending') continue;

            if ($pairing->result === 'bye') {
                $match_points += 3;
                $byes_received++;
                continue;
            }

            $playerResult = $pairing->getResultForPlayer($userId);
            $matches_played++;

            switch ($playerResult) {
                case 'win':
                    $match_points += 3;
                    $matches_won++;
                    break;
                case 'loss':
                    $matches_lost++;
                    break;
                case 'draw':
                    $match_points += 1;
                    $matches_drawn++;
                    break;
            }

            // Game details — calculate games_played from individual wins+ties, don't trust the stored field
            if ($pairing->matchResult) {
                $mr = $pairing->matchResult;
                if ($pairing->player1_id === $userId) {
                    $games_won  += $mr->player1_wins;
                    $games_lost += $mr->player2_wins;
                } else {
                    $games_won  += $mr->player2_wins;
                    $games_lost += $mr->player1_wins;
                }
                // Auto-derive games_played so we never depend on the stored value being correct
                $games_played += $mr->player1_wins + $mr->player2_wins + ($mr->ties ?? 0);
            }
        }

        $game_win_pct = $games_played > 0
            ? max(0.25, $games_won / $games_played)
            : 0.0;

        Standing::updateOrCreate(
            ['tournament_id' => $tournament->id, 'user_id' => $userId],
            compact(
                'match_points', 'matches_played', 'matches_won', 'matches_lost',
                'matches_drawn', 'games_won', 'games_lost', 'games_played',
                'game_win_pct', 'byes_received'
            )
        );
    }

    private function updatePlayerOWP(Tournament $tournament, int $userId): void
    {
        $pairings = $this->getPlayerPairings($tournament, $userId);
        $opponentWinPcts = [];

        foreach ($pairings as $pairing) {
            if ($pairing->result === 'pending' || $pairing->result === 'bye') continue;
            $opponentId = $pairing->player1_id === $userId ? $pairing->player2_id : $pairing->player1_id;
            if (!$opponentId) continue;

            $oppStanding = Standing::where('tournament_id', $tournament->id)
                ->where('user_id', $opponentId)
                ->first();

            if ($oppStanding && $oppStanding->matches_played > 0) {
                $owp = $oppStanding->matches_won / $oppStanding->matches_played;
                $opponentWinPcts[] = max(0.25, $owp);
            }
        }

        $owp = count($opponentWinPcts) > 0
            ? array_sum($opponentWinPcts) / count($opponentWinPcts)
            : 0.0;

        Standing::where('tournament_id', $tournament->id)
            ->where('user_id', $userId)
            ->update(['opponent_win_pct' => $owp]);
    }

    private function updatePlayerOOWP(Tournament $tournament, int $userId): void
    {
        $pairings = $this->getPlayerPairings($tournament, $userId);
        $oppOwps = [];

        foreach ($pairings as $pairing) {
            if ($pairing->result === 'pending' || $pairing->result === 'bye') continue;
            $opponentId = $pairing->player1_id === $userId ? $pairing->player2_id : $pairing->player1_id;
            if (!$opponentId) continue;

            $oppStanding = Standing::where('tournament_id', $tournament->id)
                ->where('user_id', $opponentId)
                ->first();

            if ($oppStanding) {
                $oppOwps[] = $oppStanding->opponent_win_pct;
            }
        }

        $oowp = count($oppOwps) > 0
            ? array_sum($oppOwps) / count($oppOwps)
            : 0.0;

        Standing::where('tournament_id', $tournament->id)
            ->where('user_id', $userId)
            ->update(['opp_opp_win_pct' => $oowp]);
    }

    private function assignPositions(Tournament $tournament): void
    {
        $standings = $tournament->standings()
            ->orderByDesc('match_points')
            ->orderByDesc('opponent_win_pct')
            ->orderByDesc('opp_opp_win_pct')
            ->orderByDesc('game_win_pct')
            ->get();

        $position = 1;
        foreach ($standings as $standing) {
            $standing->update(['position' => $position++]);
        }
    }

    private function getPlayerPairings(Tournament $tournament, int $userId)
    {
        return Pairing::whereHas('round', fn($q) => $q->where('tournament_id', $tournament->id)->where('type', 'swiss'))
            ->where(function ($q) use ($userId) {
                $q->where('player1_id', $userId)->orWhere('player2_id', $userId);
            })
            ->with(['matchResult', 'round'])
            ->get();
    }

    /**
     * Initialize standings for all confirmed players at tournament start.
     */
    public function initializeStandings(Tournament $tournament): void
    {
        $playerIds = $tournament->confirmedRegistrations()->pluck('user_id');
        foreach ($playerIds as $userId) {
            Standing::firstOrCreate(
                ['tournament_id' => $tournament->id, 'user_id' => $userId],
                ['match_points' => 0]
            );
        }
    }
}
