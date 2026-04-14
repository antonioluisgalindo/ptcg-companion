@extends('layouts.app')
@section('title', 'Editar Torneo: ' . $tournament->name)

@section('content')
<div style="margin-bottom:var(--spacing-lg);">
    <a href="{{ route('tournaments.show', $tournament) }}" style="color:var(--color-text-muted);font-size:0.875rem;text-decoration:none;">
        <i class="bi bi-arrow-left"></i> {{ $tournament->name }}
    </a>
</div>

<div style="max-width:720px;">
    <h2 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);margin-bottom:var(--spacing-xl);">Editar Torneo</h2>

    <form method="POST" action="{{ route('tournaments.update', $tournament) }}">
        @csrf @method('PUT')

        <div class="card-custom" style="margin-bottom:var(--spacing-lg);">
            <div class="card-custom-header"><h3 class="card-custom-title">Información General</h3></div>
            <div class="card-custom-body">
                <div class="form-group">
                    <label class="form-label">Nombre del Torneo *</label>
                    <input type="text" name="name" class="form-control-ptcg" value="{{ old('name', $tournament->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control-ptcg" rows="3">{{ old('description', $tournament->description) }}</textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label">Formato de Barajas *</label>
                        <select name="format" class="form-control-ptcg" required>
                            <option value="standard" {{ old('format', $tournament->format) === 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="expanded" {{ old('format', $tournament->format) === 'expanded' ? 'selected' : '' }}>Expanded</option>
                            <option value="unlimited" {{ old('format', $tournament->format) === 'unlimited' ? 'selected' : '' }}>Unlimited</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Formato de Partida *</label>
                        <select name="match_format" class="form-control-ptcg" required>
                            <option value="bo1" {{ old('match_format', $tournament->match_format) === 'bo1' ? 'selected' : '' }}>Mejor de 1 (Bo1)</option>
                            <option value="bo3" {{ old('match_format', $tournament->match_format) === 'bo3' ? 'selected' : '' }}>Mejor de 3 (Bo3)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Máx. Jugadores *</label>
                        <input type="number" name="max_players" class="form-control-ptcg" value="{{ old('max_players', $tournament->max_players) }}" min="4" required>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label">Provincia</label>
                        <select name="province_id" id="province_id" class="form-control-ptcg" onchange="loadLocalities(this.value)">
                            <option value="">Selecciona Provincia</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ old('province_id', $tournament->province_id) == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Localidad</label>
                        <select name="locality_id" id="locality_id" class="form-control-ptcg">
                            <option value="">Selecciona Localidad</option>
                            @foreach($localities as $locality)
                                <option value="{{ $locality->id }}" {{ old('locality_id', $tournament->locality_id) == $locality->id ? 'selected' : '' }}>{{ $locality->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group" style="display:none;">
                        <input type="hidden" name="city" id="city_hidden" value="{{ old('city', $tournament->city) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lugar</label>
                        <input type="text" name="venue" class="form-control-ptcg" value="{{ old('venue', $tournament->venue) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de inicio</label>
                    <input type="datetime-local" name="starts_at" class="form-control-ptcg"
                        value="{{ old('starts_at', $tournament->starts_at?->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
        </div>

        <div class="card-custom" style="margin-bottom:var(--spacing-lg);">
            <div class="card-custom-header"><h3 class="card-custom-title">Configuración de Rondas</h3></div>
            <div class="card-custom-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label">Rondas Swiss</label>
                        <input type="number" name="swiss_rounds" class="form-control-ptcg" value="{{ old('swiss_rounds', $tournament->swiss_rounds) }}" min="3" max="15" placeholder="Auto">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tiempo/Ronda (min) *</label>
                        <input type="number" name="match_time_minutes" class="form-control-ptcg" value="{{ old('match_time_minutes', $tournament->match_time_minutes) }}" min="15" max="90" required>
                    </div>
                </div>
                <div style="padding:var(--spacing-md);background:var(--color-bg-secondary);border-radius:var(--radius-md);border:1px solid var(--color-border);">
                    <label class="form-check-ptcg" style="margin-bottom:var(--spacing-sm);">
                        <input type="checkbox" name="top_cut_enabled" value="1"
                            {{ old('top_cut_enabled', $tournament->top_cut_enabled) ? 'checked' : '' }} id="top_cut_cb"
                            onchange="document.getElementById('top_cut_size_group').style.display=this.checked?'block':'none'">
                        <span style="font-weight:600;color:var(--color-text-primary);">Activar Top Cut</span>
                    </label>
                    <div id="top_cut_size_group" style="display:{{ old('top_cut_enabled', $tournament->top_cut_enabled) ? 'block' : 'none' }};">
                        <select name="top_cut_size" class="form-control-ptcg" style="max-width:200px;">
                            @foreach(['4','8','16','32'] as $s)
                                <option value="{{ $s }}" {{ old('top_cut_size', $tournament->top_cut_size) == $s ? 'selected' : '' }}>Top {{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:var(--spacing-md);">
            <button type="submit" class="btn-ptcg btn-primary-ptcg">
                <i class="bi bi-check-circle"></i> Guardar Cambios
            </button>
            <a href="{{ route('tournaments.show', $tournament) }}" class="btn-ptcg btn-secondary-ptcg">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
async function loadLocalities(provinceId, selectedLocalityId = null) {
    const localitySelect = document.getElementById('locality_id');
    const cityHidden = document.getElementById('city_hidden');
    
    // Clear current options (except if we are just initializing with a value)
    if (!selectedLocalityId) {
        localitySelect.innerHTML = '<option value="">Cargando...</option>';
    }
    
    if (!provinceId) {
        localitySelect.innerHTML = '<option value="">Selecciona Provincia primero</option>';
        return;
    }

    try {
        const response = await fetch(`/locations/provinces/${provinceId}/localities`);
        const localities = await response.json();
        
        localitySelect.innerHTML = '<option value="">Selecciona Localidad</option>';
        localities.forEach(loc => {
            const option = document.createElement('option');
            option.value = loc.id;
            option.textContent = loc.name;
            if (selectedLocalityId && loc.id == selectedLocalityId) {
                option.selected = true;
            }
            localitySelect.appendChild(option);
        });

        // Update hidden city field when locality changes
        localitySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            cityHidden.value = selectedOption.value ? selectedOption.textContent : '';
        });

    } catch (error) {
        console.error('Error loading localities:', error);
        if (!selectedLocalityId) {
            localitySelect.innerHTML = '<option value="">Error al cargar localidades</option>';
        }
    }
}

// Handle initialization or old input
@if(old('province_id'))
    loadLocalities({{ old('province_id') }}, {{ old('locality_id', 'null') }});
@endif
</script>
@endpush
