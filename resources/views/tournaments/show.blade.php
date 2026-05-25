@extends('layouts.app')
@section('title', $tournament->name)

@section('content')
@php
    $user = auth()->user();
    $isOrgOrAdmin = $user->hasAnyRole(['admin','organizador']);
    $isJudge = $user->hasRole('juez');
    $badge = $tournament->status_badge;
@endphp

<!-- Header -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:var(--spacing-lg);gap:var(--spacing-md);flex-wrap:wrap;">
    <div>
        <div style="display:flex;align-items:center;gap:var(--spacing-sm);margin-bottom:var(--spacing-xs);">
            <a href="{{ route('tournaments.index') }}" style="color:var(--color-text-muted);font-size:0.875rem;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Torneos
            </a>
        </div>
        <h2 style="font-size:1.75rem;font-weight:800;color:var(--color-text-primary);">{{ $tournament->name }}</h2>
        <div style="display:flex;align-items:center;gap:var(--spacing-sm);margin-top:var(--spacing-sm);flex-wrap:wrap;">
            <span class="badge-custom badge-{{ match($tournament->status) {
                'registration' => 'info', 'ongoing' => 'success',
                'finished' => 'dark', 'cancelled' => 'danger', default => 'secondary'
            } }}">{{ $badge['label'] }}</span>
            <span class="badge-custom badge-secondary">{{ $tournament->format_label }}</span>
            @if($tournament->top_cut_enabled)
                <span class="badge-custom badge-warning">Top {{ $tournament->top_cut_size }}</span>
            @endif

            @if($isOrgOrAdmin)
                <div class="access-code-badge" title="Comparte este código con los jugadores">
                    <i class="bi bi-key-fill"></i> Código: <strong>{{ $tournament->access_code }}</strong>
                </div>
            @endif

            <span style="color:var(--color-text-muted);font-size:0.8rem;">
                Organiza: {{ $tournament->organizer->full_name }}
            </span>
        </div>
    </div>

    <!-- Actions -->
    <div style="display:flex;gap:var(--spacing-sm);flex-wrap:wrap;">
        @if($isOrgOrAdmin && $tournament->organizer_id === $user->id)
            <a href="{{ route('tournaments.edit', $tournament) }}" class="btn-ptcg btn-secondary-ptcg btn-sm">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <button type="button" onclick="showQRModal()" class="btn-ptcg btn-secondary-ptcg btn-sm">
                <i class="bi bi-qr-code"></i> Código QR
            </button>
        @endif

        @if($isOrgOrAdmin)
            <div style="position:relative;">
                <button class="btn-ptcg btn-secondary-ptcg btn-sm" onclick="document.getElementById('status-dropdown').classList.toggle('show')" style="gap:4px;">
                    Estado <i class="bi bi-chevron-down" style="font-size:0.7rem;"></i>
                </button>
                <div id="status-dropdown" class="dropdown-menu-custom" style="min-width:160px;">
                    @foreach(['draft'=>'Borrador','registration'=>'Inscripciones','ongoing'=>'En Curso','finished'=>'Finalizado','cancelled'=>'Cancelado'] as $s => $label)
                    @if($tournament->status !== $s)
                    <form method="POST" action="{{ route('tournaments.status', $tournament) }}">
                        @csrf <input type="hidden" name="status" value="{{ $s }}">
                        <button type="submit" class="dropdown-item-custom {{ $s === 'cancelled' ? 'danger' : '' }}">{{ $label }}</button>
                    </form>
                    @endif
                    @endforeach
                </div>
            </div>
        @endif

        @if($isOrgOrAdmin)
            <button class="btn-ptcg btn-secondary-ptcg btn-sm" onclick="showRegistrationsModal()">
                <i class="bi bi-people-fill"></i> Inscritos ({{ $tournament->registrations->count() }})
            </button>
        @endif

        @if($tournament->status === 'ongoing' && ($isOrgOrAdmin))
            <form method="POST" action="{{ route('rounds.start', $tournament) }}">
                @csrf
                <button type="submit" class="btn-ptcg btn-primary-ptcg btn-sm pulse-glow"
                    onclick="return confirm('¿Iniciar la siguiente ronda?')">
                    <i class="bi bi-play-circle"></i> Iniciar Ronda {{ $tournament->currentRoundNumber() + 1 }}
                </button>
            </form>
        @endif
    </div>
</div>

<!-- User's Current Pairing Alert -->
@if($activeRound && $userPairing)
<div class="alert-ptcg alert-warning animate-fade-in-up" style="margin-bottom:var(--spacing-lg);">
    <i class="bi bi-lightning-charge-fill" style="font-size:1.2rem;"></i>
    <div style="flex:1;">
        <strong>¡Tu partida está activa! — Ronda {{ $activeRound->number }}</strong>
        <p style="margin-top:4px;font-size:0.875rem;">
            @if($userPairing->isBye())
                Tienes BYE en esta ronda. Recibes 3 puntos automáticamente.
            @else
                Rival: <strong>{{ $userPairing->getOpponentOf($user->id)?->full_name }}</strong>
                @if($userPairing->table_number) — Mesa <strong>{{ $userPairing->table_number }}</strong> @endif
            @endif
        </p>
    </div>
    @if(!$userPairing->isBye() && $userPairing->result === 'pending')
        <a href="{{ route('pairings.show', $userPairing) }}" class="btn-ptcg btn-primary-ptcg btn-sm">Registrar Resultado</a>
    @endif
</div>
@endif

<!-- Registration Card for Players -->
@if($tournament->status === 'registration' && !$isOrgOrAdmin && !$isJudge)
<div class="card-custom" style="margin-bottom:var(--spacing-lg);border-color:rgba(227,53,13,0.25);">
    <div class="card-custom-body">
        @if(!$userRegistration)
            @if(!$user->birth_date || !$user->player_id)
                <div style="background:rgba(227,53,13,0.05); border:1px solid rgba(227,53,13,0.2); border-radius:var(--radius-md); padding:var(--spacing-md); display:flex; gap:var(--spacing-md); align-items:center;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:1.5rem; color:var(--color-primary);"></i>
                    <div style="flex:1;">
                        <p style="font-weight:700; color:var(--color-text-primary); margin-bottom:4px;">Perfil Incompleto</p>
                        <p style="font-size:0.875rem; color:var(--color-text-secondary); margin-bottom:var(--spacing-sm);">
                            Para inscribirte, necesitas completar los siguientes datos en tu perfil:
                            <strong>{{ !$user->birth_date ? 'Fecha de nacimiento' : '' }}{{ !$user->birth_date && !$user->player_id ? ' y ' : '' }}{{ !$user->player_id ? 'ID de Play! Pokémon' : '' }}</strong>.
                        </p>
                        <a href="{{ route('profile.edit') }}" class="btn-ptcg btn-primary-ptcg btn-sm">
                            <i class="bi bi-person-fill-gear"></i> Completar mi Perfil
                        </a>
                    </div>
                </div>
            @else
                <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--spacing-md);flex-wrap:wrap;">
                    <div>
                        <h3 style="font-weight:700;color:var(--color-text-primary);font-size:1rem;">¡Inscríbete al torneo!</h3>
                        <p style="color:var(--color-text-muted);font-size:0.875rem;margin-top:4px;">
                            {{ $tournament->confirmedRegistrations()->count() }}/{{ $tournament->max_players }} plazas ocupadas
                        </p>
                    </div>
                    <button class="btn-ptcg btn-primary-ptcg" onclick="var f = document.getElementById('register-form'); f.style.display = f.style.display === 'none' ? 'block' : 'none';">
                        <i class="bi bi-plus-circle"></i> Inscribirse
                    </button>
                </div>
            @endif
            <div id="register-form" style="display:none; margin-top:var(--spacing-md); padding-top:var(--spacing-md); border-top:1px solid var(--color-border);">
                <form method="POST" action="{{ route('tournaments.register', $tournament) }}" style="margin-top:var(--spacing-md);">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Nombre del mazo</label>
                        <input type="text" name="deck_name" class="form-control-ptcg" placeholder="Ej: Charizard ex" value="{{ old('deck_name') }}">
                    </div>
                    @if($tournament->require_deck_list)
                    <div class="form-group">
                        <label class="form-label">Lista del mazo *</label>
                        <textarea name="deck_list" class="form-control-ptcg" rows="8" placeholder="4 Charizard ex PAF 54&#10;..." required>{{ old('deck_list') }}</textarea>
                    </div>
                    @endif
                    <button type="submit" class="btn-ptcg btn-primary-ptcg">
                        <i class="bi bi-check-circle"></i> Confirmar Inscripción
                    </button>
                </form>
            </div>
        @else
            @php $statusBadge = $userRegistration->status_badge; @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--spacing-md);flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:var(--spacing-md);">
                    <i class="bi bi-check-circle-fill" style="font-size:1.5rem;color:var(--color-success);"></i>
                    <div>
                        <p style="font-weight:600;color:var(--color-text-primary);">Ya estás inscrito/a</p>
                        <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:2px;">
                            Estado: <span class="badge-custom badge-{{ match($userRegistration->status) {
                                'confirmed' => 'success', 'pending' => 'warning', default => 'danger'
                            } }}">{{ $statusBadge['label'] }}</span>
                        </p>
                    </div>
                </div>
                @if($tournament->status === 'registration' && $userRegistration->status !== 'dropped')
                <form method="POST" action="{{ route('registrations.cancel', $userRegistration) }}"
                    onsubmit="return confirm('¿Seguro que quieres cancelar tu inscripción? Esta acción no se puede deshacer fácilmente.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-ptcg btn-danger-ptcg btn-sm">
                        <i class="bi bi-x-circle"></i> Cancelar inscripción
                    </button>
                </form>
                @endif
            </div>
        @endif
    </div>
</div>
@endif

<!-- Main Content Grid -->
<div class="grid-dashboard-responsive">

    <!-- Left: Rounds & Pairings -->
    <div>
        @if($tournament->rounds->count() > 0)
            @foreach($tournament->rounds->sortByDesc('number') as $round)
            @php $roundBadge = $round->status_badge; @endphp
            <div class="card-custom" style="margin-bottom:var(--spacing-lg);">
                <div class="card-custom-header">
                    <div style="display:flex;align-items:center;gap:var(--spacing-sm);">
                        <span style="font-weight:700;color:var(--color-text-primary);">
                            Ronda {{ $round->number }}
                            @if($round->type === 'top_cut')<span style="font-size:0.75rem;color:var(--color-warning);">(Top Cut)</span>@endif
                        </span>
                        <span class="badge-custom badge-{{ match($round->status) {
                            'active' => 'success', 'finished' => 'dark', default => 'secondary'
                        } }}">{{ $roundBadge['label'] }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:var(--spacing-sm);">
                        @if($round->started_at)
                            <span style="font-size:0.75rem;color:var(--color-text-muted);">
                                <i class="bi bi-clock"></i> {{ $round->started_at->format('H:i') }}
                            </span>
                        @endif
                        @if($round->status === 'active' && ($isOrgOrAdmin || $isJudge))
                        <form method="POST" action="{{ route('rounds.finish', $round) }}">
                            @csrf
                            <button type="submit" class="btn-ptcg btn-success-ptcg btn-sm"
                                onclick="return confirm('¿Finalizar ronda? Asegúrate de que todos los resultados están registrados.')">
                                <i class="bi bi-flag-fill"></i> Finalizar
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('rounds.show', $round) }}" class="btn-ptcg btn-secondary-ptcg btn-sm">
                            <i class="bi bi-eye"></i> Ver detalle
                        </a>
                    </div>
                </div>

                <!-- Pairings Table (compact) -->
                <div class="table-responsive">
                    <!-- Header row -->
                    <div style="display:grid;grid-template-columns:80px 1fr auto 1fr;gap:var(--spacing-sm);padding:8px var(--spacing-lg);background:var(--color-bg-tertiary);border-bottom:1px solid var(--color-border);min-width:500px;">
                        <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;">Mesa</span>
                        <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;">Jugador 1</span>
                        <span></span>
                        <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;text-align:right;">Jugador 2</span>
                    </div>
                    @foreach($round->pairings as $pairing)
                    @php
                        $isMyPairing = $pairing->involvesUser($user->id);
                        $resultForMe = $isMyPairing ? $pairing->getResultForPlayer($user->id) : null;
                    @endphp
                    <div style="display:grid;grid-template-columns:80px 1fr auto 1fr;gap:var(--spacing-sm);padding:10px var(--spacing-lg);border-bottom:1px solid var(--color-border);{{ $isMyPairing ? 'background:rgba(227,53,13,0.06);' : '' }}align-items:center;min-width:500px;">
                        <!-- Mesa First -->
                        <div>
                            @if($pairing->table_number)
                                <span style="display:inline-block;background:var(--color-bg-tertiary);border:1px solid var(--color-border);border-radius:4px;padding:2px 8px;font-size:0.75rem;font-weight:600;color:var(--color-text-secondary);min-width:32px;text-align:center;">
                                    {{ $pairing->table_number }}
                                </span>
                            @else
                                <span style="font-size:0.7rem;color:var(--color-text-muted);">BYE</span>
                            @endif
                        </div>

                        <!-- Player 1 -->
                        <div style="display:flex;align-items:center;gap:var(--spacing-xs);">
                            @if($pairing->result === 'player1_win')
                                <i class="bi bi-trophy-fill" style="color:var(--color-warning);font-size:0.75rem;"></i>
                            @endif
                            <span style="font-size:0.875rem;{{ $pairing->result === 'player1_win' ? 'color:var(--color-text-primary);font-weight:600;' : 'color:var(--color-text-secondary);' }}">
                                <a href="{{ route('users.show', $pairing->player1) }}" style="color:inherit; text-decoration:none;" class="hover-underline">
                                    {{ $pairing->player1->full_name }}
                                </a>
                            </span>
                            <span style="font-size:0.65rem; color:var(--color-text-muted); font-weight:700;">{{ $pairing->player1->category_sigla }}</span>
                        </div>
                        <div style="text-align:center;">
                            @if($pairing->result === 'pending')
                                <span style="font-size:0.7rem;color:var(--color-text-muted);font-weight:600;">VS</span>
                            @elseif($pairing->isBye())
                                <span class="badge-custom badge-info" style="font-size:0.65rem;">BYE</span>
                            @else
                                @php $mr = $pairing->matchResult; @endphp
                                @if($mr)
                                    <span style="font-size:0.75rem;font-weight:700;color:var(--color-text-primary);">
                                        {{ $mr->player1_wins }}-{{ $mr->player2_wins }}
                                    </span>
                                @else
                                    <span class="badge-custom badge-{{ match($pairing->result) { 'draw' => 'secondary', default => 'success' } }}" style="font-size:0.65rem;">
                                        {{ $pairing->result === 'draw' ? 'Empate' : 'Resultado' }}
                                    </span>
                                @endif
                            @endif
                        </div>
                        <div style="text-align:right;display:flex;align-items:center;justify-content:flex-end;gap:var(--spacing-xs);">
                            @if($pairing->result === 'player2_win')
                                <i class="bi bi-trophy-fill" style="color:var(--color-warning);font-size:0.75rem;"></i>
                            @endif
                            <span style="font-size:0.65rem; color:var(--color-text-muted); font-weight:700;">{{ $pairing->player2?->category_sigla }}</span>
                            <span style="font-size:0.875rem;{{ $pairing->result === 'player2_win' ? 'color:var(--color-text-primary);font-weight:600;' : 'color:var(--color-text-secondary);' }}">
                                @if($pairing->player2)
                                    <a href="{{ route('users.show', $pairing->player2) }}" style="color:inherit; text-decoration:none;" class="hover-underline">
                                        {{ $pairing->player2->full_name }}
                                    </a>
                                @else
                                    — BYE —
                                @endif
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        @else
            <div class="card-custom">
                <div class="card-custom-body" style="text-align:center;padding:var(--spacing-2xl);color:var(--color-text-muted);">
                    <i class="bi bi-hourglass" style="font-size:2.5rem;opacity:0.3;display:block;margin-bottom:var(--spacing-md);"></i>
                    <p>El torneo aún no ha iniciado rondas.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Right: Info & Standings -->
    <div style="display:flex;flex-direction:column;gap:var(--spacing-lg);">

        <!-- Tournament Info -->
        <div class="card-custom">
            <div class="card-custom-header"><h3 class="card-custom-title">Información</h3></div>
            <div class="card-custom-body">
                <div style="display:flex;flex-direction:column;gap:var(--spacing-sm);">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--color-text-muted);font-size:0.8rem;">Formato</span>
                        <span style="font-weight:600;font-size:0.875rem;">{{ $tournament->format_label }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--color-text-muted);font-size:0.8rem;">Partidas</span>
                        <span style="font-weight:600;font-size:0.875rem;">{{ $tournament->match_format_label }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--color-text-muted);font-size:0.8rem;">Rondas</span>
                        <span style="font-weight:600;font-size:0.875rem;">Swiss: {{ $tournament->swiss_rounds_count }}
                            @if($tournament->top_cut_enabled) + Top {{ $tournament->top_cut_size }} @endif
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--color-text-muted);font-size:0.8rem;">Tiempo/Ronda</span>
                        <span style="font-weight:600;font-size:0.875rem;">{{ $tournament->match_time_minutes }} min</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--color-text-muted);font-size:0.8rem;">Jugadores</span>
                        <span style="font-weight:600;font-size:0.875rem;">{{ $tournament->confirmedRegistrations->count() }}/{{ $tournament->max_players }}</span>
                    </div>
                    @if($tournament->locality_id || $tournament->province_id || $tournament->city)
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--color-text-muted);font-size:0.8rem;">Ubicación</span>
                        <span style="font-weight:600;font-size:0.875rem;text-align:right;">
                            @if($tournament->locality_id)
                                {{ $tournament->locality->name }}, {{ $tournament->province->name }}
                            @else
                                {{ $tournament->city }}
                            @endif
                        </span>
                    </div>
                    @endif
                    @if($tournament->venue)
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--color-text-muted);font-size:0.8rem;">Sede</span>
                        <span style="font-weight:600;font-size:0.875rem;text-align:right;">{{ $tournament->venue }}</span>
                    </div>
                    @endif
                    @if($tournament->starts_at)
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--color-text-muted);font-size:0.8rem;">Fecha</span>
                        <span style="font-weight:600;font-size:0.875rem;">{{ $tournament->starts_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>

                @if($tournament->description)
                    <div style="margin-top:var(--spacing-md);padding-top:var(--spacing-md);border-top:1px solid var(--color-border);">
                        <p style="font-size:0.8rem;color:var(--color-text-secondary);line-height:1.6;">{{ $tournament->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Standings (if tournament has started) -->
        @if($tournament->standings->count() > 0)
        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-list-ol" style="margin-right:6px;color:var(--color-warning);"></i> Clasificación</h3>
                <span style="font-size:0.75rem;color:var(--color-text-muted);">Ronda {{ $tournament->currentRoundNumber() }} completada</span>
            </div>
            <div class="table-responsive">
                <table class="table-ptcg" style="margin:0;border:none;background:transparent;">
                    <thead>
                        <tr style="background:rgba(255,255,255,0.02);">
                            <th style="width:44px;text-align:center;">#</th>
                            <th>Jugador</th>
                            <th style="text-align:center;">Pts</th>
                            <th style="text-align:center;">W/L/D</th>
                            <th style="text-align:center;">GWP%</th>
                            <th style="text-align:center;">OWP%</th>
                            <th style="text-align:center;">OOWP%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tournament->standings->sortBy('position') as $standing)
                        @php
                            $isMe = $standing->user_id === $user->id;
                            $medal = match($standing->position) {
                                1 => ['icon' => 'bi-trophy-fill', 'color' => '#FFD700'],
                                2 => ['icon' => 'bi-trophy-fill', 'color' => '#C0C0C0'],
                                3 => ['icon' => 'bi-trophy-fill', 'color' => '#CD7F32'],
                                default => null,
                            };
                        @endphp
                        <tr class="row-hover" style="{{ $isMe ? 'background:rgba(227,53,13,0.07);' : '' }}">
                            <td style="text-align:center;font-weight:700;">
                                @if($medal)
                                    <i class="bi {{ $medal['icon'] }}" style="color:{{ $medal['color'] }};font-size:0.85rem;"></i>
                                @else
                                    <span style="color:var(--color-text-muted);font-size:0.875rem;">{{ $standing->position }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <img src="{{ $standing->user->avatar_url }}" style="width:28px;height:28px;border-radius:50%;border:1px solid {{ $isMe ? 'var(--color-primary)' : 'rgba(255,255,255,0.1)' }};">
                                    <div>
                                        <span style="font-weight:{{ $isMe ? '700' : '500' }};color:{{ $isMe ? 'var(--color-primary-light)' : 'var(--color-text-primary)' }};font-size:0.875rem;">
                                            <a href="{{ route('users.show', $standing->user) }}" style="color:inherit; text-decoration:none;" class="hover-underline">
                                                {{ $standing->user->full_name }}
                                            </a>
                                            @if($isMe) <span style="font-size:0.65rem;opacity:0.7;">(tú)</span> @endif
                                        </span>
                                        <span style="display:block;font-size:0.65rem;color:var(--color-text-muted);font-weight:700;">{{ $standing->user->category_sigla }}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:center;font-weight:800;font-size:1rem;color:{{ $isMe ? 'var(--color-primary-light)' : 'var(--color-text-primary)' }};">
                                {{ $standing->match_points }}
                            </td>
                            <td style="text-align:center;font-size:0.8rem;color:var(--color-text-secondary);">
                                <span style="color:var(--color-success);">{{ $standing->matches_won }}</span>/<span style="color:var(--color-danger);">{{ $standing->matches_lost }}</span>/<span style="color:var(--color-text-muted);">{{ $standing->matches_drawn }}</span>
                            </td>
                            <td style="text-align:center;font-size:0.8rem;color:var(--color-text-secondary);">
                                {{ number_format($standing->game_win_pct * 100, 1) }}%
                            </td>
                            <td style="text-align:center;font-size:0.8rem;color:var(--color-text-secondary);">
                                {{ number_format($standing->opponent_win_pct * 100, 1) }}%
                            </td>
                            <td style="text-align:center;font-size:0.8rem;color:var(--color-text-muted);">
                                {{ number_format($standing->opp_opp_win_pct * 100, 1) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Integration Tools (TOM) -->
        @if($isOrgOrAdmin)
        <div class="card-custom" style="border-color:rgba(59,130,246,0.3);">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-cpu-fill" style="margin-right:6px;color:var(--color-info);"></i> Integración TOM</h3>
            </div>
            <div class="card-custom-body">
                <!-- Manual Paste Section -->
                <div style="margin-bottom:var(--spacing-lg);">
                    <p style="font-size:0.75rem;font-weight:700;color:var(--color-text-primary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">Importación Manual (Copy-Paste)</p>
                    <form method="POST" action="{{ route('integration.import.text', $tournament) }}">
                        @csrf
                        <textarea name="raw_data" rows="5" class="form-control-ptcg" placeholder="Pega aquí la tabla de jugadores o de emparejamientos directamente desde TOM..." style="font-size:0.75rem; font-family:monospace; line-height:1.4;"></textarea>
                        
                        <div style="display:flex; flex-direction:column; gap:8px; margin-top:10px;">
                            <button type="submit" name="import_type" value="players" class="btn-ptcg btn-primary-ptcg" style="width:100%; justify-content:center;">
                                <i class="bi bi-person-plus"></i> Importar Jugadores
                            </button>
                            <button type="submit" name="import_type" value="pairings" class="btn-ptcg btn-success-ptcg" style="width:100%; justify-content:center;">
                                <i class="bi bi-dice-5"></i> Importar Ronda
                            </button>
                        </div>
                    </form>
                    <p style="font-size:0.6rem; color:var(--color-text-muted); margin-top:6px; font-style:italic;">
                        * Copia la tabla en TOM y pégala arriba. Se detectará automáticamente la estructura.
                    </p>
                </div>

                <!-- File Import (Fallback) -->
                <div style="padding-top:var(--spacing-md);border-top:1px solid var(--color-border);">
                    <p style="font-size:0.65rem; font-weight:700; color:var(--color-text-muted); margin-bottom:8px; text-transform:uppercase;">Carga de archivo (CSV)</p>
                    
                    <div class="grid-2-col-responsive" style="gap:var(--spacing-sm);">
                        <form method="POST" action="{{ route('integration.import.players', $tournament) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="csv_file" class="form-control-ptcg" accept=".csv,.txt" style="display:none;" id="csv_players_file" onchange="this.form.submit()">
                            <button type="button" class="btn-ptcg btn-secondary-ptcg btn-sm" style="width:100%; font-size:0.7rem;" onclick="document.getElementById('csv_players_file').click()">
                                <i class="bi bi-upload"></i> Jugadores CSV
                            </button>
                        </form>
                        <form method="POST" action="{{ route('integration.import.pairings', $tournament) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="csv_file" class="form-control-ptcg" accept=".csv,.txt" style="display:none;" id="csv_pairings_file" onchange="this.form.submit()">
                            <button type="button" class="btn-ptcg btn-secondary-ptcg btn-sm" style="width:100%; font-size:0.7rem;" onclick="document.getElementById('csv_pairings_file').click()">
                                <i class="bi bi-upload"></i> Ronda CSV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@if($isOrgOrAdmin)
<div id="modal-registrations" class="modal-ptcg" onclick="if(event.target === this) hideRegistrationsModal()">
    <div class="modal-ptcg-content animate-fade-in-up">
        <div class="modal-ptcg-header">
            <div>
                <h3 class="modal-ptcg-title">Entrenadores Inscritos ({{ $tournament->registrations->count() }})</h3>
                <div class="modal-ptcg-stats">
                    <span class="stat-item"><i class="bi bi-person-fill" style="color:var(--color-primary);"></i> <strong>{{ $tournament->registrations->where('user.category', 'master')->count() }}</strong> MA</span>
                    <span class="stat-item"><i class="bi bi-person-fill" style="color:var(--color-info);"></i> <strong>{{ $tournament->registrations->where('user.category', 'senior')->count() }}</strong> SR</span>
                    <span class="stat-item"><i class="bi bi-person-fill" style="color:var(--color-success);"></i> <strong>{{ $tournament->registrations->where('user.category', 'junior')->count() }}</strong> JR</span>
                </div>
            </div>
            <button class="modal-ptcg-close" onclick="hideRegistrationsModal()">&times;</button>
        </div>
        
        <!-- Search Bar -->
        <div class="modal-search-container">
            <i class="bi bi-search modal-search-icon"></i>
            <input type="text" id="reg-search" class="modal-search-input" placeholder="Buscar por nombre, apellidos o Player ID..." onkeyup="filterRegistrations()">
        </div>
        <div class="modal-ptcg-body p-0 table-responsive">
            <table class="table-ptcg" style="margin:0; border:none; background:transparent;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.02);">
                        <th class="sticky-th" style="padding-left:var(--spacing-xl);">Entrenador</th>
                        <th class="sticky-th">ID Jugador</th>
                        <th class="sticky-th">Cat.</th>
                        <th class="sticky-th">Estado</th>
                        <th class="sticky-th" style="text-align:right; padding-right:var(--spacing-xl);">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tournament->registrations->sortBy('status') as $reg)
                        <tr class="row-hover registration-row" data-search="{{ strtolower($reg->user->full_name . ' ' . ($reg->user->player_id ?? '')) }}">
                            <td style="padding-left:var(--spacing-xl);">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="{{ $reg->user->avatar_url }}" style="width:32px; height:32px; border-radius:50%; border:1px solid rgba(255,255,255,0.1);">
                                    <div>
                                        <p style="font-weight:700; color:var(--color-text-primary); font-size:0.875rem;">{{ $reg->user->full_name }}</p>
                                        @if($reg->deck_name)<span style="font-size:0.7rem; color:var(--color-text-muted);">{{ $reg->deck_name }}</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-family:monospace; color:var(--color-text-secondary);">{{ $reg->user->player_id ?? '--' }}</span>
                            </td>
                            <td>
                                <span class="badge-custom badge-secondary" style="font-size:0.65rem; border:none; background:rgba(255,255,255,0.1);">{{ $reg->user->category_sigla }}</span>
                            </td>
                            <td>
                                <span id="reg-badge-{{ $reg->id }}" class="badge-custom badge-{{ match($reg->status) { 'confirmed' => 'success', 'pending' => 'warning', 'dropped' => 'dark', default => 'danger' } }}" style="font-size:0.65rem;">
                                    {{ $reg->status_badge['label'] }}
                                </span>
                            </td>
                            <td style="text-align:right; padding-right:var(--spacing-xl);">
                                <div id="reg-actions-{{ $reg->id }}" style="display:flex; justify-content:flex-end; gap:6px;">
                                    <button type="button" class="btn-ptcg btn-success-ptcg btn-sm btn-confirm" 
                                        onclick="handleRegistrationAction({{ $reg->id }}, '{{ route('registrations.confirm', $reg) }}', 'confirm')"
                                        title="{{ $reg->status === 'dropped' ? 'Reactivar inscripción' : 'Confirmar inscripción' }}" 
                                        style="padding:0 8px; display: {{ ($reg->status === 'pending' || $reg->status === 'dropped') ? 'block' : 'none' }};">✓</button>
                                    
                                    <button type="button" class="btn-ptcg btn-danger-ptcg btn-sm btn-drop" 
                                        onclick="handleRegistrationAction({{ $reg->id }}, '{{ route('registrations.drop', $reg) }}', 'drop')"
                                        title="Dar de baja" 
                                        style="padding:0 8px; display: {{ ($reg->status !== 'dropped') ? 'block' : 'none' }};">✕</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:var(--spacing-xl); color:var(--color-text-muted);">Sin inscripciones</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-qr" class="modal-ptcg" onclick="if(event.target === this) hideQRModal()">
    <div class="modal-ptcg-content animate-fade-in-up" style="max-width:400px; text-align:center;">
        <div class="modal-ptcg-header">
            <h3 class="modal-ptcg-title">Código QR de Inscripción</h3>
            <button class="modal-ptcg-close" onclick="hideQRModal()">&times;</button>
        </div>
        <div class="modal-ptcg-body" style="padding:var(--spacing-xl); display:flex; flex-direction:column; align-items:center; gap:var(--spacing-lg);">
            <div id="qrcode" style="padding:15px; background:white; border-radius:var(--radius-md); box-shadow:0 10px 25px -5px rgba(0,0,0,0.1);"></div>
            <p style="font-size:0.875rem; color:var(--color-text-secondary); line-height:1.5;">
                Escanea este código para acceder directamente a la inscripción de <strong>{{ $tournament->name }}</strong>.
            </p>
            <div style="background:rgba(255,255,255,0.05); padding:var(--spacing-md); border-radius:var(--radius-sm); border:1px solid var(--color-border); width:100%;">
                <p style="font-size:0.65rem; color:var(--color-text-muted); text-transform:uppercase; margin-bottom:4px; font-weight:700;">Enlace Directo</p>
                <code style="font-size:0.75rem; color:var(--color-info); word-break:break-all;">{{ route('tournaments.joinByQR', $tournament->access_code) }}</code>
            </div>
        </div>
    </div>
</div>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let qrcodeInstance = null;

function showQRModal() {
    const modal = document.getElementById('modal-qr');
    const qrContainer = document.getElementById('qrcode');
    const joinUrl = "{{ route('tournaments.joinByQR', $tournament->access_code) }}";
    
    if (!qrcodeInstance) {
        qrcodeInstance = new QRCode(qrContainer, {
            text: joinUrl,
            width: 256,
            height: 256,
            colorDark: "#111827",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
    document.body.style.overflow = 'hidden';
}

function hideQRModal() {
    const modal = document.getElementById('modal-qr');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
}

function showRegistrationsModal() {
    const modal = document.getElementById('modal-registrations');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
    document.body.style.overflow = 'hidden';
}
function hideRegistrationsModal() {
    const modal = document.getElementById('modal-registrations');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
}

async function handleRegistrationAction(regId, url, actionType) {
    if (actionType === 'drop' && !confirm('¿Dar de baja al jugador?')) return;
    
    // Visual feedback (disabling)
    const container = document.getElementById(`reg-actions-${regId}`);
    const originalOpacity = container.style.opacity;
    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update badge
            const badge = document.getElementById(`reg-badge-${regId}`);
            badge.className = `badge-custom badge-${data.badge_class}`;
            badge.innerText = data.label;
            
            // Update buttons visibility
            const btnConfirm = container.querySelector('.btn-confirm');
            const btnDrop = container.querySelector('.btn-drop');

            if (data.status === 'confirmed') {
                btnConfirm.style.display = 'none';
                btnDrop.style.display = 'block';
            } else if (data.status === 'dropped') {
                btnConfirm.style.display = 'block';
                btnConfirm.title = 'Reactivar inscripción';
                btnDrop.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Registration action error:', error);
        alert('Hubo un error al procesar la acción. Inténtalo de nuevo.');
    } finally {
        container.style.opacity = originalOpacity || '1';
        container.style.pointerEvents = '';
    }
}

function filterRegistrations() {
    const query = document.getElementById('reg-search').value.toLowerCase();
    const rows = document.querySelectorAll('.registration-row');
    
    rows.forEach(row => {
        const searchText = row.getAttribute('data-search');
        if (searchText.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.addEventListener('click', () => {
    document.getElementById('status-dropdown')?.classList.remove('show');
});
document.querySelector('[onclick*="status-dropdown"]')?.addEventListener('click', (e) => e.stopPropagation());
</script>

@endsection
