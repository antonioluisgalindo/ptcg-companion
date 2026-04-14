@extends('layouts.app')
@section('title', 'Editar Entrenador')

@section('content')
<!-- Hero Header Section -->
<div class="hero-banner animate-fade-in-up" style="display:flex; align-items:center; gap:var(--spacing-xl); padding: var(--spacing-xl); flex-wrap:wrap;">
    <div style="position:relative;">
        <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" 
             style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:4px solid rgba(255,255,255,0.1); box-shadow: 0 0 20px var(--color-primary-glow);">
    </div>
    <div>
        <div style="display:flex; align-items:center; gap:var(--spacing-md); margin-bottom:4px;">
            <h2 style="font-size:1.75rem; font-weight:800; color:var(--color-text-primary);">{{ $user->full_name }}</h2>
            <span class="badge-custom {{ $user->is_active ? 'badge-success' : 'badge-danger' }}" style="font-size:0.7rem;">
                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
        <p style="color:var(--color-text-secondary); margin-bottom:var(--spacing-sm);">{{ $user->email }}</p>
        <div style="display:flex; align-items:center; gap:var(--spacing-sm);">
            @foreach($user->roles as $role)
                <span class="badge-custom badge-primary" style="font-size:0.7rem;">{{ ucfirst($role->name) }}</span>
            @endforeach
            @if($user->player_id)
                <span class="badge-custom badge-info" style="font-family:monospace;">{{ $user->player_id }}</span>
            @endif
            @if($user->birth_date)
                <span class="badge-custom badge-secondary" style="font-size:0.7rem;">{{ $user->category_sigla }}</span>
            @endif
        </div>
    </div>
</div>

<div style="margin-top:var(--spacing-lg); margin-bottom:var(--spacing-xl);">
    <a href="{{ route('users.index') }}" style="color:var(--color-text-muted); font-size:0.875rem; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
        <i class="bi bi-arrow-left"></i> Volver a usuarios
    </a>
</div>

<div class="profile-grid">
    <!-- Main Column (Left) -->
    <div class="profile-main-col">
        <!-- Edit Form Card -->
        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-shield-lock-fill" style="margin-right:6px; color:var(--color-primary-light);"></i> Gestión de Cuenta</h3>
            </div>
            <div class="card-custom-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf @method('PUT')
                    
                    <div class="grid-2-col-responsive" style="margin-bottom:var(--spacing-md);">
                        <div class="form-group">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="name" class="form-control-ptcg" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="surname" class="form-control-ptcg" value="{{ old('surname', $user->surname) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email de la cuenta *</label>
                        <input type="email" name="email" class="form-control-ptcg" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="grid-2-col-responsive" style="margin-bottom:var(--spacing-md);">
                        <div class="form-group">
                            <label class="form-label">Player ID oficial</label>
                            <input type="text" name="player_id" class="form-control-ptcg" value="{{ old('player_id', $user->player_id) }}" placeholder="1234567" style="font-family:monospace;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Asignar Rol Principal</label>
                            <select name="role" class="form-control-ptcg">
                                <option value="">— Mantener actual —</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="background:rgba(255,255,255,0.03); padding:var(--spacing-md); border-radius:var(--radius-md); border:1px solid var(--color-border); margin-bottom:var(--spacing-xl);">
                        <label class="form-check-ptcg">
                            <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                            <span style="font-weight:600; color:var(--color-text-primary);">Permitir acceso al sistema (Usuario Activo)</span>
                        </label>
                        <p style="font-size:0.75rem; color:var(--color-text-muted); margin-top:4px; margin-left:28px;">
                            Si se desmarca, el usuario no podrá iniciar sesión pero sus datos y registros se mantendrán.
                        </p>
                    </div>

                    <div style="display:flex; gap:var(--spacing-md);">
                        <button type="submit" class="btn-ptcg btn-primary-ptcg">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('users.index') }}" class="btn-ptcg btn-secondary-ptcg">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Column (Right) -->
    <div class="profile-side-col">
        <!-- Stats Card (Career Summary) -->
        <div class="card-custom" style="margin-bottom:var(--spacing-lg); border-color:rgba(245,158,11,0.25);">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-trophy" style="margin-right:6px; color:var(--color-warning);"></i> Carrera del Entrenador</h3>
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
                        <span class="stat-mini-value" style="color:var(--color-text-primary);">{{ $stats['total_points'] }}</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="stat-mini-label">Win Rate</span>
                        <span class="stat-mini-value">{{ $stats['win_rate'] }}%</span>
                    </div>
                </div>
                <div style="margin-top:var(--spacing-md); padding-top:var(--spacing-md); border-top:1px solid var(--color-border); text-align:center;">
                    <span style="font-size:0.75rem; color:var(--color-text-muted);">Participación en {{ $stats['tournaments'] }} torneos</span>
                </div>
            </div>
        </div>

        <!-- Meta Info Card -->
        <div class="card-custom">
            <div class="card-custom-header">
                <h3 class="card-custom-title"><i class="bi bi-info-circle" style="margin-right:6px; color:var(--color-info);"></i> Detalles de Registro</h3>
            </div>
            <div class="card-custom-body">
                <div style="display:flex; flex-direction:column; gap:var(--spacing-sm);">
                    <div>
                        <p style="font-size:0.65rem; color:var(--color-text-muted); text-transform:uppercase;">Registrado el</p>
                        <p style="font-size:0.875rem; color:var(--color-text-primary);">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p style="font-size:0.65rem; color:var(--color-text-muted); text-transform:uppercase;">Última actualización</p>
                        <p style="font-size:0.875rem; color:var(--color-text-primary);">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p style="font-size:0.65rem; color:var(--color-text-muted); text-transform:uppercase;">Categoría de edad</p>
                        <p style="font-size:0.875rem; color:var(--color-text-primary);">{{ $user->category_label }} ({{ $user->category_sigla }})</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
