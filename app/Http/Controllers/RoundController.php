<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Tournament;
use App\Services\NotificationService;
use App\Services\StandingsService;
use App\Services\SwissPairingService;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function __construct(
        private SwissPairingService $pairingService,
        private StandingsService    $standingsService,
        private NotificationService $notificationService
    ) {}

    public function show(Round $round)
    {
        $round->load(['tournament', 'pairings.player1', 'pairings.player2', 'pairings.matchResult']);
        return view('rounds.show', compact('round'));
    }

    public function startRound(Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        abort_if($tournament->status !== 'ongoing', 422, 'El torneo debe estar en curso.');
        abort_if($tournament->rounds()->where('status', 'active')->exists(), 422, 'Ya hay una ronda activa.');

        // Initialize standings if this is round 1
        if ($tournament->currentRoundNumber() === 0) {
            $this->standingsService->initializeStandings($tournament);
        }

        $round = $this->pairingService->generateRound($tournament);
        $this->notificationService->notifyPairingsReady($round);

        return redirect()->route('rounds.show', $round)
            ->with('success', "¡Ronda {$round->number} iniciada! Los jugadores han sido notificados.");
    }

    public function finishRound(Round $round)
    {
        $this->authorize('update', $round->tournament);
        abort_if($round->status !== 'active', 422, 'La ronda no está activa.');
        abort_if(!$round->isFinished(), 422, 'Hay resultados pendientes de registrar.');

        $round->update(['status' => 'finished', 'finished_at' => now()]);
        $this->standingsService->recalculate($round->tournament);
        $this->notificationService->notifyRoundFinished($round);

        return redirect()->route('tournaments.show', $round->tournament)
            ->with('success', "Ronda {$round->number} finalizada. Standings actualizados.");
    }
}
