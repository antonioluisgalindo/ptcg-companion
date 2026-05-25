@extends('layouts.app')
@section('title', 'Mi Perfil')

@section('content')
<!-- Profile Header / Hero Section -->
<div class="hero-banner animate-fade-in-up" style="display:flex; align-items:center; gap:var(--spacing-xl); padding: var(--spacing-xl); flex-wrap:wrap;">
    <div style="position:relative; cursor:pointer;" onclick="document.getElementById('avatar-input').click();">
        <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" id="avatar-preview"
             style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:4px solid rgba(255,255,255,0.1); box-shadow: 0 0 20px var(--color-primary-glow);">
        <div class="camera-icon-badge">
             <i class="bi bi-camera-fill"></i>
        </div>
    </div>
    <div>
        <h2 style="font-size:1.75rem; font-weight:800; color:var(--color-text-primary); margin-bottom:4px;">{{ $user->full_name }}</h2>
        <p style="color:var(--color-text-secondary); margin-bottom:var(--spacing-sm);">{{ $user->email }}</p>
        <div style="display:flex; align-items:center; gap:var(--spacing-sm);">
            @foreach($user->roles as $role)
                <span class="badge-custom badge-primary" style="font-size:0.7rem;">{{ ucfirst($role->name) }}</span>
            @endforeach
            @if($user->player_id)
                <span class="badge-custom badge-info" style="font-family:monospace;">{{ $user->player_id }}</span>
            @endif
        </div>
    </div>
</div>

<div class="profile-grid">
    <!-- Main Column (Left) -->
    <div class="profile-main-col">
        <!-- Edit Profile Info -->
        <div class="card-custom" style="margin-bottom:var(--spacing-lg);">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-person-fill" style="margin-right:6px; color:var(--color-primary-light);"></i> Información de Usuario</h3>
            </div>
            <div class="card-custom-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="grid-2-col-responsive">
                        <div class="form-group">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="name" class="form-control-ptcg" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="surname" class="form-control-ptcg" value="{{ old('surname', $user->surname) }}">
                        </div>
                    </div>
                    <div class="grid-2-col-responsive">
                        <div class="form-group">
                            <label class="form-label">Player ID oficial (Pokémon TCG)</label>
                            <input type="text" name="player_id" class="form-control-ptcg" value="{{ old('player_id', $user->player_id) }}" placeholder="1234567" style="font-family:monospace;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <div style="display:flex; gap:var(--spacing-xs); align-items:center;">
                                <input type="date" name="birth_date" class="form-control-ptcg" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                                <span class="badge-custom badge-info" title="{{ $user->category_label }}">
                                    {{ $user->category_sigla }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Biografía / Notas</label>
                        <textarea name="bio" class="form-control-ptcg" rows="3" placeholder="Información opcional sobre ti...">{{ old('bio', $user->bio) }}</textarea>
                    </div>
                    <div class="form-group" style="display:none;">
                        <input type="file" name="avatar" id="avatar-input" class="form-control-ptcg" accept="image/*" onchange="previewAvatar(this)">
                    </div>
                    <div style="margin-top:var(--spacing-md);">
                        <button type="submit" class="btn-ptcg btn-primary-ptcg">
                            <i class="bi bi-save"></i> Actualizar Perfil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History of Registrations -->
        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-journal-text" style="margin-right:6px; color:var(--color-info);"></i> Historial de Torneos</h3>
                <span class="badge-custom badge-secondary">{{ auth()->user()->registrations->count() }} inscripciones</span>
            </div>
            <div class="card-custom-body p-0 table-responsive">
                @php $registrations = auth()->user()->registrations()->with('tournament')->latest()->get(); @endphp
                @if($registrations->count() > 0)
                    <table class="table-ptcg">
                        <thead><tr><th>Evento</th><th>Estado</th><th>Mazo</th><th></th></tr></thead>
                        <tbody>
                            @foreach($registrations as $reg)
                            <tr>
                                <td>
                                    <strong style="color:var(--color-text-primary);">{{ $reg->tournament->name }}</strong><br>
                                    <span style="font-size:0.7rem; color:var(--color-text-muted);">{{ $reg->tournament->format_label }}</span>
                                </td>
                                <td>
                                    <span class="badge-custom badge-{{ match($reg->status) { 'confirmed' => 'success', 'pending' => 'warning', 'dropped' => 'dark', default => 'danger' } }}">
                                        {{ $reg->status_badge['label'] }}
                                    </span>
                                </td>
                                <td style="font-size:0.8rem; color:var(--color-text-secondary);">{{ $reg->deck_name ?? '—' }}</td>
                                <td><a href="{{ route('tournaments.show', $reg->tournament) }}" class="btn-ptcg btn-secondary-ptcg btn-sm">Ver</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="bi bi-controller empty-state-icon"></i>
                        <p class="empty-state-title">Sin historial</p>
                        <p class="empty-state-desc">Todavía no has participado en ningún evento.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Column (Right) -->
    <div class="profile-side-col" style="margin-top: var(--spacing-lg);">
        <!-- Stats Card -->
        <div class="card-custom" style="margin-bottom:var(--spacing-lg); border-color:rgba(245,158,11,0.25);">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-trophy" style="margin-right:6px; color:var(--color-warning);"></i> Resumen de Carrera</h3>
            </div>
            <div class="card-custom-body">
                <div class="stats-mini-grid">
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Victorias</span>
                        <span class="stat-mini-value" style="color:var(--color-success);">{{ $stats['total_wins'] }}</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Derrotas</span>
                        <span class="stat-mini-value" style="color:var(--color-danger);">{{ $stats['total_losses'] }}</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Puntos</span>
                        <span class="stat-mini-value">{{ $stats['total_points'] }}</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Win Rate</span>
                        <span class="stat-mini-value">{{ $stats['win_rate'] }}%</span>
                    </div>
                </div>
                <div style="margin-top:var(--spacing-md); padding-top:var(--spacing-md); border-top:1px solid var(--color-border); text-align:center;">
                    <span style="font-size:0.75rem; color:var(--color-text-muted);">{{ $stats['tournaments'] }} torneos jugados</span>
                </div>
            </div>
        </div>

        <!-- Security / Password Card -->
        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-shield-lock" style="margin-right:6px; color:var(--color-danger);"></i> Seguridad</h3>
            </div>
            <div class="card-custom-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label class="form-label" style="font-size:0.7rem;">Contraseña actual</label>
                        <input type="password" name="current_password" class="form-control-ptcg" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size:0.7rem;">Nueva contraseña</label>
                        <input type="password" name="password" class="form-control-ptcg" required>
                    </div>
                    <div class="form-group" style="margin-bottom:var(--spacing-lg);">
                        <label class="form-label" style="font-size:0.7rem;">Repetir contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control-ptcg" required>
                    </div>
                    <button type="submit" class="btn-ptcg btn-secondary-ptcg" style="width:100%;">
                        Actualizar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.camera-icon-badge {
    position:absolute; bottom:0; right:0; background:var(--color-primary); color:white; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid var(--color-bg-main);
    transition: transform var(--transition-fast), background var(--transition-fast);
}
.hero-banner div[onclick]:hover .camera-icon-badge {
    transform: scale(1.1);
    background: var(--color-primary-light);
}
.hero-banner div[onclick]:hover img {
    border-color: var(--color-primary-light);
}
</style>


<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
