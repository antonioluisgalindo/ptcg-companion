@extends('layouts.app')
@section('title', 'Perfil de ' . $user->name)

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">
    
    <!-- Header: Perfil -->
    <div class="card-custom" style="margin-bottom: var(--spacing-xl); position: relative; overflow: hidden;">
        <!-- Banner de fondo sutil -->
        <div style="position:absolute; top:0; left:0; right:0; height:100px; background:linear-gradient(135deg, rgba(227,53,13,0.1) 0%, rgba(59,130,246,0.1) 100%);"></div>
        
        <div class="card-custom-body" style="padding-top: 60px; position:relative; z-index:1; display:flex; gap:var(--spacing-lg); align-items:flex-start; flex-wrap:wrap;">
            <!-- Avatar -->
            <img src="{{ $user->avatar_url }}" alt="Avatar de {{ $user->name }}" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--color-bg-primary); box-shadow: var(--shadow-sm); background: var(--color-bg-secondary);">
            
            <!-- Info principal -->
            <div style="flex: 1; min-width: 280px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:var(--spacing-md);">
                    <div>
                        <h1 style="font-size: 2rem; font-weight: 800; color: var(--color-text-primary); margin-bottom: 4px;">
                            {{ $user->full_name }}
                        </h1>
                        <p style="font-size: 1rem; color: var(--color-text-muted); font-weight: 500; margin-bottom: var(--spacing-sm);">
                            @if($user->player_id)
                                Play! Pokémon ID: <strong>{{ $user->player_id }}</strong>
                            @else
                                Jugador sin ID registrado
                            @endif
                        </p>
                        
                        <div style="display:flex; gap:var(--spacing-sm); flex-wrap:wrap; margin-bottom:var(--spacing-md);">
                            @if($user->category_sigla)
                            <span class="badge-custom badge-info">
                                Categoría: {{ $user->category_sigla }}
                            </span>
                            @endif
                            @foreach($user->roles as $role)
                                <span class="badge-custom badge-secondary" style="text-transform: capitalize;">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </div>

                    @if(auth()->id() === $user->id)
                        <a href="{{ route('profile.edit') }}" class="btn-ptcg btn-secondary-ptcg">
                            <i class="bi bi-pencil"></i> Editar mi Perfil
                        </a>
                    @endif
                </div>

                @if($user->bio)
                    <div style="padding:var(--spacing-md); background:var(--color-bg-secondary); border-radius:var(--radius-md); border:1px solid var(--color-border); margin-top:var(--spacing-md);">
                        <p style="font-size: 0.95rem; color: var(--color-text-secondary); margin: 0; line-height: 1.5;">
                            "{{ $user->bio }}"
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <h3 style="font-size:1.25rem; font-weight:800; color:var(--color-text-primary); margin-bottom:var(--spacing-md);">Estadísticas Globales</h3>
    <div class="grid-dashboard-responsive" style="margin-bottom: var(--spacing-xl);">
        <div class="stat-card">
            <div class="stat-icon stat-icon-primary"><i class="bi bi-trophy"></i></div>
            <div>
                <p class="stat-label">Torneos Jugados</p>
                <h3 class="stat-value">{{ $stats['tournaments'] }}</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-success"><i class="bi bi-check-circle"></i></div>
            <div>
                <p class="stat-label">Tasa de Victorias</p>
                <h3 class="stat-value">{{ $stats['win_rate'] }}%</h3>
                <p style="font-size:0.75rem; color:var(--color-text-muted); margin-top:2px;">
                    {{ $stats['total_wins'] }}W - {{ $stats['total_losses'] }}L - {{ $stats['total_draws'] }}D
                </p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-warning"><i class="bi bi-star"></i></div>
            <div>
                <p class="stat-label">Match Points Totales</p>
                <h3 class="stat-value">{{ $stats['total_points'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Historial de Torneos -->
    <h3 style="font-size:1.25rem; font-weight:800; color:var(--color-text-primary); margin-bottom:var(--spacing-md);">Historial de Torneos</h3>
    
    @if($history->count() > 0)
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table-ptcg">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Torneo</th>
                            <th style="text-align:center;">Posición</th>
                            <th style="text-align:center;">W-L-D</th>
                            <th style="text-align:right;">Puntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $standing)
                            @php $t = $standing->tournament; @endphp
                            <tr>
                                <td style="color:var(--color-text-muted); font-size:0.875rem;">
                                    {{ $t->starts_at ? $t->starts_at->format('d/m/Y') : 'Desconocida' }}
                                </td>
                                <td>
                                    <a href="{{ route('tournaments.show', $t) }}" style="color:var(--color-text-primary); font-weight:600; text-decoration:none;">
                                        {{ $t->name }}
                                    </a>
                                    <div style="font-size:0.75rem; color:var(--color-text-muted); margin-top:2px;">
                                        {{ $t->format_label }}
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    @if($standing->position === 1)
                                        <span class="badge-custom badge-warning" style="font-size:0.85rem;"><i class="bi bi-trophy-fill"></i> 1º</span>
                                    @elseif($standing->position === 2)
                                        <span class="badge-custom badge-secondary" style="font-size:0.85rem; background:#cbd5e1; color:#334155;"><i class="bi bi-trophy-fill"></i> 2º</span>
                                    @elseif($standing->position === 3)
                                        <span class="badge-custom badge-secondary" style="font-size:0.85rem; background:#fcd34d; color:#92400e;"><i class="bi bi-trophy-fill"></i> 3º</span>
                                    @else
                                        <span style="font-weight:700; color:var(--color-text-secondary);">{{ $standing->position }}º</span>
                                    @endif
                                </td>
                                <td style="text-align:center; font-weight:500; color:var(--color-text-secondary);">
                                    <span style="color:var(--color-success);">{{ $standing->matches_won }}</span> -
                                    <span style="color:var(--color-danger);">{{ $standing->matches_lost }}</span> -
                                    <span>{{ $standing->matches_drawn }}</span>
                                </td>
                                <td style="text-align:right; font-weight:700; color:var(--color-text-primary);">
                                    {{ $standing->match_points }} pts
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card-custom">
            <div class="card-custom-body">
                <div class="empty-state">
                    <i class="bi bi-journal-x empty-state-icon"></i>
                    <div class="empty-state-title">Sin historial</div>
                    <div class="empty-state-desc">Este jugador aún no ha completado ningún torneo.</div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
