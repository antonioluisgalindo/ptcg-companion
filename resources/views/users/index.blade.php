@extends('layouts.app')
@section('title', 'Usuarios')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-xl);">
    <h2 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);">Usuarios</h2>
    <form method="GET" style="display:flex;gap:var(--spacing-sm);">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control-ptcg" placeholder="Buscar usuario..." style="width:250px;">
        <button type="submit" class="btn-ptcg btn-secondary-ptcg"><i class="bi bi-search"></i></button>
    </form>
</div>

<div class="card-custom animate-fade-in-up">
    <div class="card-custom-body p-0 table-responsive">
        <table class="table-ptcg">
            <thead>
                <tr>
                    <th style="padding-left:var(--spacing-lg);">Entrenador</th>
                    <th>Email</th>
                    <th>Player ID</th>
                    <th>Fecha Nac. / Cat.</th>
                    <th>Rol Principal</th>
                    <th>Estado</th>
                    <th style="text-align:center;">Torneos</th>
                    <th style="text-align:right;padding-right:var(--spacing-lg);">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td style="padding-left:var(--spacing-lg);">
                        <div style="display:flex;align-items:center;gap:var(--spacing-md);">
                            <img src="{{ $u->avatar_url }}" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.05);">
                            <div>
                                <span style="display:block;font-weight:700;color:var(--color-text-primary);font-size:0.9rem;">{{ $u->full_name }}</span>
                                <span style="font-size:0.7rem;color:var(--color-text-muted);">Miembro desde {{ $u->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </td>
                    <td><span style="font-size:0.8rem;color:var(--color-text-secondary);">{{ $u->email }}</span></td>
                    <td>
                        @if($u->player_id)
                            <code style="font-family:monospace;font-size:0.85rem;color:var(--color-info);background:rgba(59,130,246,0.1);padding:2px 6px;border-radius:4px;">{{ $u->player_id }}</code>
                        @else
                            <span style="color:var(--color-text-muted);font-size:0.8rem;">No asignado</span>
                        @endif
                    </td>
                    <td>
                        @if($u->birth_date)
                            <span style="font-size:0.8rem;color:var(--color-text-primary);">{{ $u->birth_date->format('d/m/Y') }}</span>
                            <span class="badge-custom badge-info" style="font-size:0.6rem;margin-left:4px;">{{ $u->category_sigla }}</span>
                        @else
                            <span style="color:var(--color-text-muted);font-size:0.8rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @php $mainRole = $u->roles->first(); @endphp
                        @if($mainRole)
                            <span class="badge-custom badge-primary" style="font-size:0.75rem;padding:4px 10px;">
                                {{ ucfirst($mainRole->name) }}
                            </span>
                        @else
                            <span style="color:var(--color-text-muted);">Sin rol</span>
                        @endif
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px;font-size:0.8rem;font-weight:600;color:{{ $u->is_active ? 'var(--color-success)' : 'var(--color-danger)' }};">
                            <span style="width:8px;height:8px;border-radius:50%;background:currentColor;"></span>
                            {{ $u->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-size:0.9rem;font-weight:700;color:var(--color-text-primary);">{{ $u->registrations()->count() }}</span>
                    </td>
                    <td style="text-align:right;padding-right:var(--spacing-lg);">
                        <div style="display:flex;gap:var(--spacing-xs);justify-content:flex-end;">
                            <a href="{{ route('users.edit', $u) }}" class="btn-ptcg btn-secondary-ptcg btn-sm" title="Editar Usuario">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $u) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-ptcg btn-danger-ptcg btn-sm"
                                    onclick="return confirm('¿Eliminar a {{ $u->name }}? Esta acción no se puede deshacer.')" title="Eliminar Usuario">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state" style="padding:var(--spacing-2xl);">
                            <i class="bi bi-people empty-state-icon"></i>
                            <p class="empty-state-title">No hay usuarios</p>
                            <p class="empty-state-desc">No se encontraron entrenadores registrados.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
