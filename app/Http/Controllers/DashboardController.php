<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stats = [];
        $activities = collect();

        if ($user->hasRole('admin')) {
            $stats = [
                'tournaments_total'  => Tournament::count(),
                'tournaments_active' => Tournament::whereIn('status', ['registration', 'ongoing'])->count(),
                'tournaments_finished' => Tournament::where('status', 'finished')->count(),
                'users_total'        => User::count(),
            ];
            $activities = \Spatie\Activitylog\Models\Activity::with('causer')
                ->latest()->take(5)->get();

        } elseif ($user->hasRole('organizador')) {
            $myTournaments = $user->organizedTournaments();
            $stats = [
                'my_tournaments'         => $myTournaments->count(),
                'my_active_tournaments'  => $myTournaments->whereIn('status', ['registration', 'ongoing'])->count(),
                'my_finished_tournaments' => $myTournaments->where('status', 'finished')->count(),
                'total_participants'     => $myTournaments->withCount('confirmedRegistrations')
                    ->get()->sum('confirmed_registrations_count'),
            ];
            $activities = \Spatie\Activitylog\Models\Activity::with('causer')
                ->latest()->take(5)->get();

        } elseif ($user->hasRole('juez')) {
            $activeTournaments = Tournament::whereIn('status', ['ongoing'])->with('activeRound')->get();
            $stats = [
                'active_tournaments'  => $activeTournaments->count(),
                'pending_results'     => \App\Models\Pairing::whereIn('result', ['pending'])
                    ->whereHas('round', fn($q) => $q->whereIn('status', ['active']))
                    ->count(),
            ];
            $activities = \Spatie\Activitylog\Models\Activity::with('causer')
                ->latest()->take(5)->get();

        } else {
            // Jugador / Espectador
            $registrations = $user->registrations()->with('tournament')->latest()->take(5)->get();
            $activePairings = \App\Models\Pairing::where(function ($q) use ($user) {
                    $q->where('player1_id', $user->id)->orWhere('player2_id', $user->id);
                })
                ->whereHas('round', fn($q) => $q->where('status', 'active'))
                ->with(['round.tournament', 'player1', 'player2', 'matchResult'])
                ->get();

            $upcomingTournaments = Tournament::where('status', 'registration')
                ->where('is_public', true)->take(5)->get();

            $stats = [
                'my_registrations'   => $user->registrations()->count(),
                'active_pairings'    => $activePairings->count(),
                'upcoming_tournaments' => $upcomingTournaments->count(),
            ];

            return view('dashboard', compact('stats', 'registrations', 'activePairings', 'upcomingTournaments'));
        }

        return view('dashboard', compact('stats', 'activities'));
    }
}
