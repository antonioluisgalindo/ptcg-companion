<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Round;
use App\Models\Pairing;
use App\Models\Standing;
use Illuminate\Support\Facades\DB;

class TopCutService
{
    private $bracketMaps = [
        4 => [
            [1, 4],
            [2, 3],
        ],
        8 => [
            [1, 8],
            [4, 5],
            [2, 7],
            [3, 6],
        ],
        16 => [
            [1, 16],
            [8, 9],
            [4, 13],
            [5, 12],
            [2, 15],
            [7, 10],
            [3, 14],
            [6, 11],
        ],
        32 => [
            [1, 32], [16, 17],
            [8, 25], [9, 24],
            [4, 29], [13, 20],
            [5, 28], [12, 21],
            [2, 31], [15, 18],
            [7, 26], [10, 23],
            [3, 30], [14, 19],
            [6, 27], [11, 22],
        ]
    ];

    public function generateRound(Tournament $tournament): Round
    {
        return DB::transaction(function () use ($tournament) {
            $currentRoundNum = $tournament->currentRoundNumber();
            $topCutRounds = $tournament->rounds()->where('type', 'top_cut')->orderBy('number')->get();
            $topCutRoundCount = $topCutRounds->count();

            $round = Round::create([
                'tournament_id' => $tournament->id,
                'number'        => $currentRoundNum + 1,
                'type'          => 'top_cut',
                'status'        => 'active',
                'started_at'    => now(),
                'time_limit_at' => $tournament->match_time_minutes ? now()->addMinutes($tournament->match_time_minutes) : null,
            ]);

            if ($topCutRoundCount === 0) {
                // First Top Cut Round
                $this->generateFirstRound($tournament, $round);
            } else {
                // Subsequent Top Cut Round
                $this->generateSubsequentRound($topCutRounds->last(), $round);
            }

            return $round;
        });
    }

    private function generateFirstRound(Tournament $tournament, Round $newRound)
    {
        $cutSize = $tournament->top_cut_size;
        $standings = $tournament->standings()->with('user')->orderBy('position')->take($cutSize)->get();

        if ($standings->count() < $cutSize) {
            throw new \Exception("Not enough players for a Top Cut of size {$cutSize}. Only {$standings->count()} available.");
        }

        $players = $standings->pluck('user')->toArray(); // 0-indexed, so player 1 is index 0
        $map = $this->bracketMaps[$cutSize];

        $tableNumber = 1;
        foreach ($map as $matchup) {
            $p1Index = $matchup[0] - 1;
            $p2Index = $matchup[1] - 1;

            Pairing::create([
                'round_id'     => $newRound->id,
                'player1_id'   => $players[$p1Index]->id,
                'player2_id'   => $players[$p2Index]->id,
                'table_number' => $tableNumber++,
                'result'       => 'pending',
            ]);
        }
    }

    private function generateSubsequentRound(Round $previousRound, Round $newRound)
    {
        // Get the pairings of the previous round in order of table_number
        $prevPairings = $previousRound->pairings()->orderBy('table_number')->get();
        
        $winners = [];
        foreach ($prevPairings as $pairing) {
            if ($pairing->result === 'player1_win') {
                $winners[] = $pairing->player1_id;
            } elseif ($pairing->result === 'player2_win') {
                $winners[] = $pairing->player2_id;
            } elseif ($pairing->result === 'bye') {
                $winners[] = $pairing->player1_id; // Bye advances player 1
            } else {
                // If draw or pending, this shouldn't happen because previous round must be finished,
                // and top cut doesn't allow draws. If draw, higher seed should advance, but let's assume
                // it was manually resolved by a judge to a win.
                throw new \Exception("A match in the previous Top Cut round does not have a clear winner.");
            }
        }

        if (count($winners) < 2) {
            throw new \Exception("Tournament is already finished.");
        }

        $tableNumber = 1;
        // In our bracket layout, consecutive matches pair up
        for ($i = 0; $i < count($winners); $i += 2) {
            Pairing::create([
                'round_id'     => $newRound->id,
                'player1_id'   => $winners[$i],
                'player2_id'   => $winners[$i + 1],
                'table_number' => $tableNumber++,
                'result'       => 'pending',
            ]);
        }
    }
}
