<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TournamentController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $query = Tournament::with('organizer')
            ->withCount('confirmedRegistrations');

        $user = Auth::user();

        // Draft protection: only admins and the owner (organizer) can see drafts
        if (!$user->hasRole('admin')) {
            $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'draft')
                  ->orWhere('organizer_id', $user->id);
            });
        }

        if (!$user->hasAnyRole(['admin', 'organizador', 'juez'])) {
            $query->where(function ($q) {
                $q->where('is_public', true)
                  ->orWhereHas('registrations', function ($sq) {
                      $sq->where('user_id', Auth::id());
                  });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->format) {
            $query->where('format', $request->format);
        }
        if ($request->province_id) {
            $query->where('province_id', $request->province_id);
        }
        if ($request->locality_id) {
            $query->where('locality_id', $request->locality_id);
        }
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->view === 'my_tournaments') {
            $query->where('organizer_id', Auth::id());
        } elseif ($request->view === 'my_registrations') {
            $query->whereHas('registrations', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $tournaments = $query->latest()->paginate(12);
        $provinces = \App\Models\Province::orderBy('name')->get();
        $localities = $request->province_id 
            ? \App\Models\Locality::where('province_id', $request->province_id)->orderBy('name')->get()
            : collect();

        return view('tournaments.index', compact('tournaments', 'provinces', 'localities'));
    }

    public function create()
    {
        $this->authorize('create', Tournament::class);
        $provinces = \App\Models\Province::orderBy('name')->get();
        return view('tournaments.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tournament::class);

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'description'           => ['nullable', 'string'],
            'format'                 => ['required', 'in:standard,expanded,unlimited'],
            'match_format'           => ['required', 'in:bo1,bo3'],
            'max_players'            => ['required', 'integer', 'min:4', 'max:512'],
            'swiss_rounds'          => ['nullable', 'integer', 'min:3', 'max:15'],
            'top_cut_enabled'       => ['boolean'],
            'top_cut_size'          => ['nullable', 'in:4,8,16,32'],
            'match_time_minutes'    => ['required', 'integer', 'min:15', 'max:90'],
            'registration_opens_at' => ['nullable', 'date'],
            'registration_closes_at' => ['nullable', 'date', 'after_or_equal:registration_opens_at'],
            'starts_at'             => ['nullable', 'date'],
            'venue'                 => ['nullable', 'string', 'max:200'],
            'province_id'           => ['nullable', 'exists:provinces,id'],
            'locality_id'           => ['nullable', 'exists:localities,id'],
            'city'                  => ['nullable', 'string', 'max:100'],
            'is_public'             => ['boolean'],
            'require_deck_list'     => ['boolean'],
        ]);

        $validated['organizer_id'] = Auth::id();
        $validated['top_cut_enabled'] = $request->boolean('top_cut_enabled');
        $validated['is_public'] = $request->boolean('is_public');
        $validated['require_deck_list'] = $request->boolean('require_deck_list');

        $tournament = Tournament::create($validated);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', '¡Torneo creado correctamente!');
    }

    public function show(Tournament $tournament)
    {
        $user = Auth::user();

        // Restriction: Only owner or admin can see draft tournaments
        if ($tournament->status === 'draft') {
            abort_if($tournament->organizer_id !== $user->id && !$user->hasRole('admin'), 403, 'Este torneo aún está en borrador.');
        }

        $tournament->load([
            'organizer',
            'rounds.pairings.player1',
            'rounds.pairings.player2',
            'rounds.pairings.matchResult',
            'standings.user',
            'confirmedRegistrations.user',
        ]);

        $userRegistration = $user->getRegistrationIn($tournament);
        $activeRound = $tournament->activeRound();
        $userPairing = null;

        if ($activeRound) {
            $userPairing = $activeRound->pairings()
                ->where(fn($q) => $q->where('player1_id', $user->id)->orWhere('player2_id', $user->id))
                ->with(['player1', 'player2', 'matchResult'])
                ->first();
        }

        return view('tournaments.show', compact(
            'tournament', 'userRegistration', 'activeRound', 'userPairing'
        ));
    }

    public function edit(Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        $provinces = \App\Models\Province::orderBy('name')->get();
        $localities = $tournament->province_id 
            ? \App\Models\Locality::where('province_id', $tournament->province_id)->orderBy('name')->get()
            : collect();
            
        return view('tournaments.edit', compact('tournament', 'provinces', 'localities'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        abort_if(in_array($tournament->status, ['ongoing', 'finished']), 403, 'No se puede editar un torneo en curso o finalizado.');

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'description'           => ['nullable', 'string'],
            'format'                 => ['required', 'in:standard,expanded,unlimited'],
            'match_format'           => ['required', 'in:bo1,bo3'],
            'max_players'            => ['required', 'integer', 'min:4', 'max:512'],
            'swiss_rounds'          => ['nullable', 'integer', 'min:3', 'max:15'],
            'top_cut_enabled'       => ['boolean'],
            'top_cut_size'          => ['nullable', 'in:4,8,16,32'],
            'match_time_minutes'    => ['required', 'integer', 'min:15', 'max:90'],
            'registration_opens_at' => ['nullable', 'date'],
            'registration_closes_at' => ['nullable', 'date'],
            'starts_at'             => ['nullable', 'date'],
            'venue'                 => ['nullable', 'string', 'max:200'],
            'province_id'           => ['nullable', 'exists:provinces,id'],
            'locality_id'           => ['nullable', 'exists:localities,id'],
            'city'                  => ['nullable', 'string', 'max:100'],
            'is_public'             => ['boolean'],
            'require_deck_list'     => ['boolean'],
        ]);

        $validated['top_cut_enabled'] = $request->boolean('top_cut_enabled');
        $validated['is_public'] = $request->boolean('is_public');
        $validated['require_deck_list'] = $request->boolean('require_deck_list');

        $tournament->update($validated);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Torneo actualizado correctamente.');
    }

    public function destroy(Tournament $tournament)
    {
        $this->authorize('delete', $tournament);
        abort_if($tournament->status !== 'draft', 403, 'Solo se pueden eliminar torneos en borrador.');
        $tournament->delete();
        return redirect()->route('tournaments.index')->with('success', 'Torneo eliminado.');
    }

    public function updateStatus(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);
        $validated = $request->validate(['status' => ['required', 'in:draft,registration,ongoing,finished,cancelled']]);
        $tournament->update(['status' => $validated['status']]);

        $messages = [
            'registration' => 'Las inscripciones están abiertas.',
            'ongoing'      => 'El torneo ha comenzado.',
            'finished'     => 'El torneo ha finalizado.',
            'cancelled'    => 'El torneo ha sido cancelado.',
        ];

        if (isset($messages[$validated['status']])) {
            $this->notificationService->notifyTournamentUpdate($tournament, $messages[$validated['status']]);
        }

        return back()->with('success', 'Estado del torneo actualizado.');
    }

    // Player registration
    public function register(Request $request, Tournament $tournament)
    {
        abort_if(!$tournament->isOpenForRegistration(), 422, 'Las inscripciones no están abiertas.');
        abort_if(Auth::user()->isRegisteredIn($tournament), 422, 'Ya estás inscrito en este torneo.');
        abort_if(!Auth::user()->birth_date, 422, 'Debes configurar tu fecha de nacimiento en tu perfil para determinar tu categoría.');
        abort_if(!Auth::user()->player_id, 422, 'Debes configurar tu ID de Play! Pokémon en tu perfil para poder inscribirte.');

        $rules = ['deck_name' => ['nullable', 'string', 'max:100']];
        if ($tournament->require_deck_list) {
            $rules['deck_list'] = ['required', 'string', 'min:10'];
        } else {
            $rules['deck_list'] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        TournamentRegistration::create([
            'tournament_id' => $tournament->id,
            'user_id'       => Auth::id(),
            'deck_name'     => $validated['deck_name'] ?? null,
            'deck_list'     => $validated['deck_list'] ?? null,
            'status'        => 'pending',
        ]);

        return back()->with('success', '¡Inscripción enviada! Espera confirmación del organizador.');
    }

    public function confirmRegistration(Request $request, TournamentRegistration $registration)
    {
        $this->authorize('update', $registration->tournament);
        $registration->update(['status' => 'confirmed']);
        $this->notificationService->notifyRegistrationConfirmed($registration->user, $registration->tournament);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => 'confirmed',
                'label' => $registration->status_badge['label'],
                'badge_class' => 'success'
            ]);
        }

        return back()->with('success', 'Inscripción confirmada.');
    }

    public function dropPlayer(Request $request, TournamentRegistration $registration)
    {
        $this->authorize('update', $registration->tournament);
        $registration->update(['status' => 'dropped', 'dropped_at' => now()]);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => 'dropped',
                'label' => $registration->status_badge['label'],
                'badge_class' => 'dark'
            ]);
        }

        return back()->with('success', 'Jugador dado de baja del torneo.');
    }

    /**
     * Allow a player to cancel their own registration (only during registration phase).
     */
    public function cancelRegistration(TournamentRegistration $registration)
    {
        // Only the registrant themselves can cancel
        abort_if($registration->user_id !== Auth::id(), 403, 'No puedes cancelar la inscripción de otro jugador.');

        // Only allowed while the tournament is in registration phase
        abort_if(
            !in_array($registration->tournament->status, ['draft', 'registration']),
            422,
            'No puedes cancelar tu inscripción una vez que el torneo ha comenzado. Contacta con el organizador.'
        );

        abort_if($registration->status === 'dropped', 422, 'Tu inscripción ya está cancelada.');

        $tournamentName = $registration->tournament->name;
        $registration->update(['status' => 'dropped', 'dropped_at' => now()]);

        return redirect()->route('tournaments.show', $registration->tournament)
            ->with('success', "Tu inscripción en \"{$tournamentName}\" ha sido cancelada.");
    }

    public function joinByCode(Request $request)
    {
        $request->validate(['access_code' => ['required', 'string', 'max:10']]);
        
        $code = strtoupper($request->access_code);
        $tournament = Tournament::where('access_code', $code)->first();

        if (!$tournament) {
            return back()->with('error', 'No se ha encontrado ningún torneo con ese código.');
        }

        if ($tournament->status === 'draft') {
            return back()->with('error', 'Este torneo aún no está abierto al público.');
        }

        // Redirect to tournament show where they can register
        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Torneo encontrado. Ahora puedes completar tu inscripción.');
    }

    public function joinByQR($access_code)
    {
        $tournament = Tournament::where('access_code', $access_code)->firstOrFail();
        
        if ($tournament->status === 'draft') {
            return redirect()->route('tournaments.index')->with('error', 'Este torneo aún no está abierto al público.');
        }

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', '¡Has accedido mediante código QR! Ya puedes inscribirte.');
    }
}
