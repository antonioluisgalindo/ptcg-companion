<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Round;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Pairing;

class NotificationService
{
    /**
     * Notify all players when a new round's pairings are ready.
     */
    public function notifyPairingsReady(Round $round): void
    {
        $tournament = $round->tournament;
        $pairings   = $round->pairings()->with(['player1', 'player2'])->get();

        foreach ($pairings as $pairing) {
            // Notify player 1
            $this->notifyPlayerPairing($pairing, $pairing->player1, $tournament, $round);

            // Notify player 2 (if not bye)
            if ($pairing->player2) {
                $this->notifyPlayerPairing($pairing, $pairing->player2, $tournament, $round);
            }
        }
    }

    private function notifyPlayerPairing(Pairing $pairing, User $player, Tournament $tournament, Round $round): void
    {
        $opponent = $pairing->getOpponentOf($player->id);
        $isBye    = $pairing->isBye();

        if ($isBye) {
            $message = "Tienes BYE en la Ronda {$round->number}. Recibes 3 puntos automáticamente.";
        } else {
            $tableStr = $pairing->table_number ? " — Mesa {$pairing->table_number}" : '';
            $message  = "Tu rival en Ronda {$round->number} es {$opponent->full_name}{$tableStr}.";
        }

        Notification::create([
            'user_id' => $player->id,
            'type'    => 'pairing_ready',
            'title'   => "🎮 Ronda {$round->number} — {$tournament->name}",
            'message' => $message,
            'icon'    => 'bi-controller',
            'link'    => route('tournaments.show', $tournament->id),
            'data'    => [
                'tournament_id' => $tournament->id,
                'round_id'      => $round->id,
                'pairing_id'    => $pairing->id,
                'table_number'  => $pairing->table_number,
                'opponent_name' => $opponent?->full_name,
            ],
        ]);
    }

    /**
     * Notify players when the round is finished and standings are updated.
     */
    public function notifyRoundFinished(Round $round): void
    {
        $tournament = $round->tournament;
        $playerIds  = $tournament->confirmedRegistrations()->pluck('user_id');

        foreach ($playerIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type'    => 'round_finished',
                'title'   => "🏁 Ronda {$round->number} finalizada — {$tournament->name}",
                'message' => "La Ronda {$round->number} ha concluido. Consulta los standings actualizados.",
                'icon'    => 'bi-flag-fill',
                'link'    => route('tournaments.show', $tournament->id),
                'data'    => [
                    'tournament_id' => $tournament->id,
                    'round_id'      => $round->id,
                ],
            ]);
        }
    }

    /**
     * Notify a player when their registration is confirmed.
     */
    public function notifyRegistrationConfirmed(User $player, Tournament $tournament): void
    {
        Notification::create([
            'user_id' => $player->id,
            'type'    => 'registration_confirmed',
            'title'   => "✅ Inscripción confirmada — {$tournament->name}",
            'message' => "Tu inscripción en {$tournament->name} ha sido confirmada. ¡Buena suerte!",
            'icon'    => 'bi-check-circle',
            'link'    => route('tournaments.show', $tournament->id),
            'data'    => ['tournament_id' => $tournament->id],
        ]);
    }

    /**
     * Notify players when tournament status changes.
     */
    public function notifyTournamentUpdate(Tournament $tournament, string $message): void
    {
        $playerIds = $tournament->confirmedRegistrations()->pluck('user_id');

        foreach ($playerIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type'    => 'tournament_update',
                'title'   => "🏆 Actualización — {$tournament->name}",
                'message' => $message,
                'icon'    => 'bi-trophy',
                'link'    => route('tournaments.show', $tournament->id),
                'data'    => ['tournament_id' => $tournament->id],
            ]);
        }
    }
}
