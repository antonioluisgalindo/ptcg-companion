@extends('layouts.app')
@section('title', 'Torneos')

@section('content')
<!-- Page Header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-xl);">
    <div>
        <h2 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);">Torneos</h2>
        <p style="color:var(--color-text-muted);font-size:0.875rem;margin-top:4px;">Explora y únete a los torneos disponibles</p>
    </div>
    @if(auth()->user()->hasAnyRole(['admin','organizador']))
    <a href="{{ route('tournaments.create') }}" class="btn-ptcg btn-primary-ptcg">
        <i class="bi bi-plus-lg"></i> Crear Torneo
    </a>
    @endif
</div>

<!-- Filters -->
<div class="card-custom" style="margin-bottom:var(--spacing-lg);">
    <div class="card-custom-body" style="padding:var(--spacing-md);">
        <form method="GET" style="display:flex;gap:var(--spacing-md);flex-wrap:wrap;align-items:flex-end;">
            <div style="flex:2;min-width:200px;">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control-ptcg" placeholder="Nombre del torneo...">
            </div>
            <div style="flex:1;min-width:150px;">
                <label class="form-label">Provincia</label>
                <select name="province_id" id="province_id" class="form-control-ptcg" onchange="loadLocalities(this.value)">
                    <option value="">Todas</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:150px;">
                <label class="form-label">Localidad</label>
                <select name="locality_id" id="locality_id" class="form-control-ptcg">
                    <option value="">Todas</option>
                    @foreach($localities as $locality)
                        <option value="{{ $locality->id }}" {{ request('locality_id') == $locality->id ? 'selected' : '' }}>{{ $locality->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:120px;">
                <label class="form-label">Vista</label>
                <select name="view" class="form-control-ptcg">
                    <option value="">Todos</option>
                    @if(auth()->user()->hasAnyRole(['admin', 'organizador']))
                        <option value="my_tournaments" {{ request('view') === 'my_tournaments' ? 'selected' : '' }}>Creados por mí</option>
                    @endif
                    @if(auth()->user()->hasAnyRole(['jugador']))
                        <option value="my_registrations" {{ request('view') === 'my_registrations' ? 'selected' : '' }}>Mis Inscripciones</option>
                    @endif
                </select>
            </div>
            <div style="flex:1;min-width:120px;">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control-ptcg">
                    <option value="">Todos</option>
                    <option value="registration" {{ request('status') === 'registration' ? 'selected' : '' }}>Inscripciones</option>
                    <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>En Curso</option>
                    <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }}>Finalizados</option>
                </select>
            </div>
            <div style="flex:1;min-width:120px;">
                <label class="form-label">Formato</label>
                <select name="format" class="form-control-ptcg">
                    <option value="">Todos</option>
                    <option value="standard" {{ request('format') === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="expanded" {{ request('format') === 'expanded' ? 'selected' : '' }}>Expanded</option>
                    <option value="unlimited" {{ request('format') === 'unlimited' ? 'selected' : '' }}>Unlimited</option>
                </select>
            </div>
            <div style="display:flex;gap:var(--spacing-sm);">
                <button type="submit" class="btn-ptcg btn-secondary-ptcg"><i class="bi bi-search"></i></button>
                @if(request()->hasAny(['search', 'province_id', 'locality_id', 'view', 'status', 'format']))
                    <a href="{{ route('tournaments.index') }}" class="btn-ptcg btn-secondary-ptcg" title="Limpiar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tournaments Grid -->
@if($tournaments->count() > 0)
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:var(--spacing-lg);">
    @foreach($tournaments as $tournament)
    @php $badge = $tournament->status_badge; @endphp
    <a href="{{ route('tournaments.show', $tournament) }}" class="tournament-card">
        <div class="tournament-card-header">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--spacing-sm);">
                <div class="tournament-name">{{ $tournament->name }}</div>
                <span class="badge-custom badge-{{ match($tournament->status) {
                    'registration' => 'info', 'ongoing' => 'success',
                    'finished' => 'dark', 'cancelled' => 'danger', default => 'secondary'
                } }}">{{ $badge['label'] }}</span>
            </div>
            @if($tournament->description)
                <p style="font-size:0.8rem;color:var(--color-text-muted);margin-top:var(--spacing-xs);line-height:1.5;">
                    {{ Str::limit($tournament->description, 100) }}
                </p>
            @endif
        </div>
        <div class="tournament-card-body">
            <div class="tournament-meta">
                <span class="tournament-meta-item">
                    <i class="bi bi-layers"></i> {{ $tournament->format_label }}
                </span>
                @if($tournament->locality_id || $tournament->province_id || $tournament->city)
                <span class="tournament-meta-item">
                    <i class="bi bi-geo-alt"></i> 
                    @if($tournament->locality_id)
                        {{ $tournament->locality->name }}, {{ $tournament->province->name }}
                    @else
                        {{ $tournament->city }}
                    @endif
                </span>
                @endif
                @if($tournament->starts_at)
                <span class="tournament-meta-item">
                    <i class="bi bi-calendar"></i> {{ $tournament->starts_at->format('d/m/Y') }}
                </span>
                @endif
                <span class="tournament-meta-item">
                    <i class="bi bi-clock"></i> {{ $tournament->match_time_minutes }}min/ronda
                </span>
            </div>
        </div>
        <div class="tournament-card-footer">
            <span style="font-size:0.8rem;color:var(--color-text-muted);">
                <i class="bi bi-people"></i>
                {{ $tournament->confirmed_registrations_count }}/{{ $tournament->max_players }} jugadores
            </span>
            <div style="display:flex;gap:var(--spacing-xs);">
                @if($tournament->top_cut_enabled)
                    <span class="badge-custom badge-warning" style="font-size:0.65rem;">
                        Top {{ $tournament->top_cut_size }}
                    </span>
                @endif
                <span class="badge-custom badge-secondary" style="font-size:0.65rem;">
                    {{ $tournament->swiss_rounds_count }} rondas
                </span>
            </div>
        </div>
    </a>
    @endforeach
</div>

<!-- Pagination -->
<div class="pagination-ptcg">
    {{ $tournaments->links('components.pagination') }}
</div>
@else
<div class="empty-state" style="background:transparent;">
    <i class="bi bi-trophy empty-state-icon"></i>
    <p class="empty-state-title">No hay torneos disponibles</p>
    <p class="empty-state-desc">
        @if(auth()->user()->hasAnyRole(['admin','organizador']))
            <a href="{{ route('tournaments.create') }}" style="color:var(--color-primary-light);">Crea el primer torneo</a>
        @else
            Vuelve pronto para ver los próximos torneos.
        @endif
    </p>
</div>
@endif
@endsection

@push('scripts')
<script>
async function loadLocalities(provinceId, selectedLocalityId = null) {
    const localitySelect = document.getElementById('locality_id');
    
    // Clear current options
    localitySelect.innerHTML = '<option value="">Todas</option>';
    
    if (!provinceId) return;

    localitySelect.innerHTML = '<option value="">Cargando...</option>';

    try {
        const response = await fetch(`/locations/provinces/${provinceId}/localities`);
        const localities = await response.json();
        
        localitySelect.innerHTML = '<option value="">Todas</option>';
        localities.forEach(loc => {
            const option = document.createElement('option');
            option.value = loc.id;
            option.textContent = loc.name;
            if (selectedLocalityId && loc.id == selectedLocalityId) {
                option.selected = true;
            }
            localitySelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading localities:', error);
        localitySelect.innerHTML = '<option value="">Error</option>';
    }
}
</script>
@endpush
