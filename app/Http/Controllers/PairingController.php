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

        $validated = $request->validate([
            'player1_wins' => ['required', 'integer', 'min:0', 'max:2'],
            'player2_wins' => ['required', 'integer', 'min:0', 'max:2'],
            'ties'         => ['required', 'integer', 'min:0', 'max:1'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        // Derive match result
        $matchResult = $this->deriveMatchResult($validated);

        // Create or update MatchResult
        $pairing->matchResult()->updateOrCreate(
            ['pairing_id' => $pairing->id],
            array_merge($validated, [
                'reported_by'   => $user->id,
                'match_result'  => $matchResult,
                'is_judge_entry' => $user->hasAnyRole(['admin', 'juez']),
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

        $validated = $request->validate([
            'player1_wins' => ['required', 'integer', 'min:0', 'max:2'],
            'player2_wins' => ['required', 'integer', 'min:0', 'max:2'],
            'ties'         => ['required', 'integer', 'min:0', 'max:1'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $matchResult = $this->deriveMatchResult($validated);

        $pairing->matchResult()->updateOrCreate(
            ['pairing_id' => $pairing->id],
            array_merge($validated, [
                'reported_by'    => Auth::id(),
                'match_result'   => $matchResult,
                'is_judge_entry' => true,
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
