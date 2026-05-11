<?php

namespace App\Http\Controllers;

use App\Models\Pairing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PairingController extends Controller
{
    public function show(Pairing $pairing)
    {
        $pairing->load(['round.tournament', 'player1', 'player2', 'matchResult.reportedBy']);
        return view('pairings.show', compact('pairing'));
    }

    public function reportResult(Request $request, Pairing $pairing)
    {
        $user = Auth::user();
        abort_if(!$pairing->involvesUser($user->id) && !$user->hasAnyRole(['admin', 'juez', 'organizador']), 403);
        abort_if($pairing->result !== 'pending', 422, 'Este pairing ya tiene un resultado registrado.');

        $isBo1   = $pairing->round->tournament->match_format === 'bo1';
        $maxWins = $isBo1 ? 1 : 2;

        $validated = $request->validate([
            'player1_wins' => ['required', 'integer', 'min:0', "max:{$maxWins}"],
            'player2_wins' => ['required', 'integer', 'min:0', "max:{$maxWins}"],
            'ties'         => ['required', 'integer', 'min:0', 'max:1'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        // For Bo1, ensure the total games is exactly 1 (0+1 or 1+0)
        if ($isBo1) {
            abort_if(
                $validated['player1_wins'] + $validated['player2_wins'] !== 1,
                422,
                'En formato Bo1 debe haber exactamente 1 juego: uno de los jugadores debe ganar 1 y el otro 0.'
            );
        }

        // Derive match result
        $matchResult = $this->deriveMatchResult($validated);

        // Create or update MatchResult
        $pairing->matchResult()->updateOrCreate(
            ['pairing_id' => $pairing->id],
            array_merge($validated, [
                'reported_by'    => $user->id,
                'match_result'   => $matchResult,
                'is_judge_entry' => $user->hasAnyRole(['admin', 'juez']),
                'games_played'   => $validated['player1_wins'] + $validated['player2_wins'] + $validated['ties'],
            ])
        );

        // Update pairing result
        $resultMap = [
            'player1_win' => 'player1_win',
            'player2_win' => 'player2_win',
            'draw'        => 'draw',
        ];

        $pairing->update([
            'result' => $resultMap[$matchResult],
            'result_confirmed' => $user->hasAnyRole(['admin', 'juez']),
        ]);

        return redirect()->route('rounds.show', $pairing->round)
            ->with('success', 'Resultado registrado correctamente.');
    }

    public function editResult(Request $request, Pairing $pairing)
    {
        abort_if(!Auth::user()->hasAnyRole(['admin', 'juez']), 403);

        $isBo1   = $pairing->round->tournament->match_format === 'bo1';
        $maxWins = $isBo1 ? 1 : 2;

        $validated = $request->validate([
            'player1_wins' => ['required', 'integer', 'min:0', "max:{$maxWins}"],
            'player2_wins' => ['required', 'integer', 'min:0', "max:{$maxWins}"],
            'ties'         => ['required', 'integer', 'min:0', 'max:1'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        if ($isBo1) {
            abort_if(
                $validated['player1_wins'] + $validated['player2_wins'] !== 1,
                422,
                'En formato Bo1 debe haber exactamente 1 juego: uno de los jugadores debe ganar 1 y el otro 0.'
            );
        }

        $matchResult = $this->deriveMatchResult($validated);

        $pairing->matchResult()->updateOrCreate(
            ['pairing_id' => $pairing->id],
            array_merge($validated, [
                'reported_by'    => Auth::id(),
                'match_result'   => $matchResult,
                'is_judge_entry' => true,
                'games_played'   => $validated['player1_wins'] + $validated['player2_wins'] + $validated['ties'],
            ])
        );

        $resultMap = [
            'player1_win' => 'player1_win',
            'player2_win' => 'player2_win',
            'draw'        => 'draw',
        ];

        $pairing->update([
            'result'           => $resultMap[$matchResult],
            'result_confirmed' => true,
        ]);

        return redirect()->route('rounds.show', $pairing->round)
            ->with('success', 'Resultado editado por el juez.');
    }

    private function deriveMatchResult(array $data): string
    {
        if ($data['player1_wins'] > $data['player2_wins']) return 'player1_win';
        if ($data['player2_wins'] > $data['player1_wins']) return 'player2_win';
        return 'draw';
    }
}
