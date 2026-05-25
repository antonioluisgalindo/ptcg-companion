@extends('layouts.app')
@section('title', 'Inicio')

@section('content')
<div class="hero-banner animate-fade-in-up">
    <div style="flex:1;">
        <h2 class="hero-banner-title">¡Hola, {{ auth()->user()->name }}! 👋</h2>
        <p class="hero-banner-subtitle">
            @if(auth()->user()->hasRole('admin'))
                Panel de administración de PTCG Companion.
            @elseif(auth()->user()->hasRole('organizador'))
                Gestiona tus torneos y coordina participantes.
            @elseif(auth()->user()->hasRole('juez'))
                Supervisa los torneos en curso y gestiona resultados.
            @else
                Bienvenido/a. Explora los torneos disponibles o únete a uno.
            @endif
        </p>
    </div>

    <!-- Join by Code Box -->
    @if(!auth()->user()->hasAnyRole(['admin', 'organizador', 'juez']))
    <div class="join-code-box mt-2">
        <form action="{{ route('tournaments.joinByCode') }}" method="POST">
            @csrf
            <label class="join-code-label">¿Tienes un código de evento?</label>
            <div style="display:flex;gap:8px;">
                <input type="text" name="access_code" class="form-control-ptcg join-code-input" placeholder="ABC-123" required>
                <button type="submit" class="btn-ptcg btn-primary-ptcg" style="padding:0 16px;">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    @if(auth()->user()->hasRole('admin'))
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-trophy"></i></div>
            <div><p class="stat-label">Torneos Totales</p><h3 class="stat-value">{{ $stats['tournaments_total'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-lightning-charge"></i></div>
            <div><p class="stat-label">En Curso / Inscripciones</p><h3 class="stat-value">{{ $stats['tournaments_active'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-dark"><i class="bi bi-flag"></i></div>
            <div><p class="stat-label">Finalizados</p><h3 class="stat-value">{{ $stats['tournaments_finished'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-people"></i></div>
            <div><p class="stat-label">Usuarios</p><h3 class="stat-value">{{ $stats['users_total'] ?? 0 }}</h3></div>
        </div>

    @elseif(auth()->user()->hasRole('organizador'))
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-trophy"></i></div>
            <div><p class="stat-label">Mis Torneos</p><h3 class="stat-value">{{ $stats['my_tournaments'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-lightning-charge"></i></div>
            <div><p class="stat-label">Torneos Activos</p><h3 class="stat-value">{{ $stats['my_active_tournaments'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-people"></i></div>
            <div><p class="stat-label">Total Participantes</p><h3 class="stat-value">{{ $stats['total_participants'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-dark"><i class="bi bi-flag"></i></div>
            <div><p class="stat-label">Finalizados</p><h3 class="stat-value">{{ $stats['my_finished_tournaments'] ?? 0 }}</h3></div>
        </div>

    @elseif(auth()->user()->hasRole('juez'))
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-shield-check"></i></div>
            <div><p class="stat-label">Torneos Activos</p><h3 class="stat-value">{{ $stats['active_tournaments'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-clipboard-pulse"></i></div>
            <div><p class="stat-label">Resultados Pendientes</p><h3 class="stat-value">{{ $stats['pending_results'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-controller"></i></div>
            <div><p class="stat-label">Mis Partidas</p><h3 class="stat-value">{{ $stats['my_active_pairings'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-journal-bookmark"></i></div>
            <div><p class="stat-label">Mis Inscripciones</p><h3 class="stat-value">{{ $stats['my_registrations'] ?? 0 }}</h3></div>
        </div>

    @else
        <!-- Jugador -->
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-controller"></i></div>
            <div><p class="stat-label">Mis Inscripciones</p><h3 class="stat-value">{{ $stats['my_registrations'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-lightning-charge"></i></div>
            <div><p class="stat-label">Partidas Activas</p><h3 class="stat-value">{{ $stats['active_pairings'] ?? 0 }}</h3></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-info"><i class="bi bi-trophy"></i></div>
            <div><p class="stat-label">Torneos Disponibles</p><h3 class="stat-value">{{ $stats['upcoming_tournaments'] ?? 0 }}</h3></div>
        </div>
    @endif
</div>

<!-- Active Match Focus (MTG Companion Style) -->
@if(isset($activePairings) && $activePairings->count() > 0)
    <div class="active-match-focus animate-fade-in-up">
        @foreach($activePairings as $pairing)
        <div class="match-focus-card">
            <div class="match-focus-header">
                <div>
                    <span class="match-status-pill">Partida en curso</span>
                    <h3 class="match-focus-title">{{ $pairing->round->tournament->name }}</h3>
                    <p class="match-focus-meta">Ronda {{ $pairing->round->number }} — @if($pairing->table_number) Mesa <strong>{{ $pairing->table_number }}</strong> @else Mesa asignada pronto @endif</p>
                </div>
                <!-- Optional: Countdown timer placeholder -->
                <div class="match-timer" data-limit="{{ $pairing->round->time_limit_at?->toIso8601String() }}">
                    <i class="bi bi-clock-history"></i> <span class="timer-display">Ronda activa</span>
                </div>
            </div>

            <div class="match-focus-vs">
                <div class="match-player">
                    <img src="{{ $pairing->player1->avatar_url }}" class="match-player-avatar">
                    <span class="match-player-name">{{ $pairing->player1->name }}</span>
                </div>
                <div class="match-vs-badge">VS</div>
                <div class="match-player">
                    <img src="{{ $pairing->player2 ? $pairing->player2->avatar_url : 'https://ui-avatars.com/api/?name=BYE&background=var(--color-bg-tertiary)&color=fff' }}" class="match-player-avatar">
                    <span class="match-player-name">{{ $pairing->player2 ? $pairing->player2->name : 'BYE' }}</span>
                </div>
            </div>

            <div class="match-focus-footer">
                <a href="{{ route('pairings.show', $pairing) }}" class="btn-ptcg btn-primary-ptcg match-action-btn">
                    <i class="bi bi-clipboard-check"></i> Reportar Resultado
                </a>
            </div>
        </div>
        @endforeach
    </div>
@endif

<!-- Empty state for active pairings if not in focus -->
@if(isset($activePairings) && $activePairings->count() === 0 && !auth()->user()->hasAnyRole(['admin','organizador','juez']))
    <div class="card-custom mt-lg" style="margin-bottom: var(--spacing-lg);">
        <div class="card-custom-body">
            <div class="empty-state">
                <i class="bi bi-controller empty-state-icon"></i>
                <div class="empty-state-title">Sin partida activa</div>
                <div class="empty-state-desc">No estás participando en ninguna ronda en curso en este momento.</div>
            </div>
        </div>
    </div>
@endif

<!-- Recent Activity (Admins / Org / Judges) -->
@if(isset($activities) && auth()->user()->hasAnyRole(['admin', 'organizador', 'juez']))
<div class="card-custom" style="margin-top: var(--spacing-lg);">
    <div class="card-custom-header">
        <h3 class="card-custom-title">Actividad Reciente</h3>
        @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('activity-logs.index') }}" class="card-custom-action">Ver Todo <i class="bi bi-arrow-right"></i></a>
        @endif
    </div>
    <div class="card-custom-body p-0">
        @if($activities->count() > 0)
            @foreach($activities as $activity)
            <div class="activity-item" style="border-bottom:1px solid var(--color-border); padding:12px var(--spacing-md);">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($activity->causer->name ?? 'Sistema') }}&background=E3350D&color=fff&bold=true"
                     alt="{{ $activity->causer->name ?? 'Sistema' }}" class="activity-avatar" style="width:32px;height:32px;">
                <div style="flex:1;">
                    <p class="activity-text" style="font-size:0.875rem;">
                        <strong>{{ $activity->causer->name ?? 'Sistema' }}</strong>
                        <span style="color:var(--color-text-secondary);">{{ $activity->description }}</span>
                        @if($activity->subject)
                            <span style="color:var(--color-text-muted);">en</span>
                            <strong style="color:var(--color-primary-light);">{{ class_basename($activity->subject_type) }}</strong>
                        @endif
                    </p>

                    @if($activity->event === 'updated' && isset($activity->properties['attributes']))
                        <div style="font-size:0.75rem; color:var(--color-text-muted); margin-top:2px; display:flex; gap:8px; flex-wrap:wrap;">
                            @foreach(array_keys($activity->properties['attributes']) as $key)
                                @if(!in_array($key, ['updated_at', 'created_at']) && isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] !== $activity->properties['attributes'][$key])
                                    <span class="badge-custom badge-dark" style="font-size:0.65rem; border:none; background:rgba(255,255,255,0.03);">
                                        {{ $key }}: <span style="text-decoration:line-through; opacity:0.6;">{{ is_array($activity->properties['old'][$key]) ? '...' : $activity->properties['old'][$key] }}</span>
                                        ➔ {{ is_array($activity->properties['attributes'][$key]) ? '...' : $activity->properties['attributes'][$key] }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <p class="activity-time" style="font-size:0.7rem; color:var(--color-text-muted); margin-top:4px;">
                        <i class="bi bi-clock"></i> {{ $activity->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="bi bi-journal-text empty-state-icon"></i>
                <div class="empty-state-title">Sin actividad</div>
                <div class="empty-state-desc">Aún no se ha registrado ninguna actividad en el sistema.</div>
            </div>
        @endif
    </div>
</div>
@endif

<!-- My Registrations for Players -->
@if(isset($registrations))
<div class="card-custom" style="margin-top:var(--spacing-lg);">
    <div class="card-custom-header">
        <h3 class="card-custom-title"><i class="bi bi-bookmark-star" style="color:var(--color-primary);margin-right:6px;"></i> Mis Inscripciones Recientes</h3>
        <a href="{{ route('tournaments.index', ['view' => 'my_registrations']) }}" class="card-custom-action">Ver Todas <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card-custom-body p-0 table-responsive">
        @if($registrations->count() > 0)
            <table class="table-ptcg">
                <thead><tr><th>Torneo</th><th>Estado</th><th>Mazo</th><th></th></tr></thead>
                <tbody>
                    @foreach($registrations as $reg)
                    <tr>
                        <td>
                            <strong style="color:var(--color-text-primary);">{{ $reg->tournament->name }}</strong><br>
                            <span style="font-size:0.75rem;color:var(--color-text-muted);">{{ $reg->tournament->format_label }}</span>
                        </td>
                        <td>
                            <span class="badge-custom badge-{{ match($reg->status) { 'confirmed' => 'success', 'pending' => 'warning', 'dropped' => 'dark', default => 'danger' } }}">
                                {{ $reg->status_badge['label'] }}
                            </span>
                        </td>
                        <td><span style="font-size:0.8rem;color:var(--color-text-secondary);">{{ $reg->deck_name ?? '—' }}</span></td>
                        <td><a href="{{ route('tournaments.show', $reg->tournament) }}" class="btn-ptcg btn-secondary-ptcg btn-sm">Ver</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <i class="bi bi-bookmark-x empty-state-icon"></i>
                <div class="empty-state-title">Aún no hay inscripciones</div>
                <div class="empty-state-desc">Explora el catálogo de torneos disponibles para apuntarte.</div>
            </div>
        @endif
    </div>
</div>
@endif

<!-- Upcoming Tournaments for Players -->
@if(isset($upcomingTournaments))
<div class="card-custom" style="margin-top:var(--spacing-lg);">
    <div class="card-custom-header">
        <h3 class="card-custom-title"><i class="bi bi-trophy" style="color:var(--color-warning);margin-right:6px;"></i> Torneos Disponibles</h3>
        <a href="{{ route('tournaments.index') }}" class="card-custom-action">Ver Todos <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="card-custom-body p-0 table-responsive">
        @if($upcomingTournaments->count() > 0)
            <table class="table-ptcg">
                <thead><tr><th>Torneo</th><th>Formato</th><th>Plazas</th><th></th></tr></thead>
                <tbody>
                    @foreach($upcomingTournaments as $t)
                    <tr>
                        <td><strong style="color:var(--color-text-primary);">{{ $t->name }}</strong></td>
                        <td><span class="badge-custom badge-info">{{ $t->format_label }}</span></td>
                        <td style="color:var(--color-text-muted);">{{ $t->confirmedRegistrations()->count() }}/{{ $t->max_players }}</td>
                        <td><a href="{{ route('tournaments.show', $t) }}" class="btn-ptcg btn-secondary-ptcg btn-sm">Ver</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <i class="bi bi-calendar-x empty-state-icon"></i>
                <div class="empty-state-title">No hay nuevos torneos</div>
                <div class="empty-state-desc">Actualmente no existen torneos con inscripciones abiertas.</div>
            </div>
        @endif
    </div>
</div>
@endif
@push('scripts')
<script>
function updateTimers() {
    const timerElements = document.querySelectorAll('.match-timer');
    timerElements.forEach(el => {
        const timeLimit = el.getAttribute('data-limit');
        if (!timeLimit) return;

        const limitDate = new Date(timeLimit);
        const now = new Date();
        const diff = limitDate - now;

        const display = el.querySelector('.timer-display');

        if (diff <= 0) {
            display.textContent = "Tiempo agotado";
            display.parentElement.style.color = "var(--color-danger)";
        } else {
            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            display.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            if (minutes < 5) {
                display.parentElement.style.color = "var(--color-warning)";
            }
        }
    });
}

setInterval(updateTimers, 1000);
updateTimers();
</script>
@endpush
@endsection
