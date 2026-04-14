@extends('layouts.app')
@section('title', 'Crear Torneo')

@section('content')
<div style="margin-bottom:var(--spacing-lg);">
    <a href="{{ route('tournaments.index') }}" style="color:var(--color-text-muted);font-size:0.875rem;text-decoration:none;">
        <i class="bi bi-arrow-left"></i> Torneos
    </a>
</div>

<div style="max-width:720px;">
    <h2 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);margin-bottom:var(--spacing-xl);">Crear Nuevo Torneo</h2>

    <form method="POST" action="{{ route('tournaments.store') }}">
        @csrf

        <div class="card-custom" style="margin-bottom:var(--spacing-lg);">
            <div class="card-custom-header"><h3 class="card-custom-title">Información General</h3></div>
            <div class="card-custom-body">
                <div class="form-group">
                    <label class="form-label" for="name">Nombre del Torneo *</label>
                    <input type="text" id="name" name="name" class="form-control-ptcg" value="{{ old('name') }}" required placeholder="Ej: City Championship — Primavera 2026">
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Descripción</label>
                    <textarea id="description" name="description" class="form-control-ptcg" rows="3" placeholder="Información adicional sobre el torneo...">{{ old('description') }}</textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label" for="format">Formato de Barajas *</label>
                        <select id="format" name="format" class="form-control-ptcg" required>
                            <option value="standard" {{ old('format') === 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="expanded" {{ old('format') === 'expanded' ? 'selected' : '' }}>Expanded</option>
                            <option value="unlimited" {{ old('format') === 'unlimited' ? 'selected' : '' }}>Unlimited</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="match_format">Formato de Partida *</label>
                        <select id="match_format" name="match_format" class="form-control-ptcg" required>
                            <option value="bo1" {{ old('match_format') === 'bo1' ? 'selected' : '' }}>Mejor de 1 (Bo1)</option>
                            <option value="bo3" {{ old('match_format') === 'bo3' ? 'selected' : '' }}>Mejor de 3 (Bo3)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="max_players">Máx. Jugadores *</label>
                        <input type="number" id="max_players" name="max_players" class="form-control-ptcg" value="{{ old('max_players', 32) }}" min="4" max="512" required>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label" for="province_id">Provincia</label>
                        <select id="province_id" name="province_id" class="form-control-ptcg" onchange="loadLocalities(this.value)">
                            <option value="">Selecciona Provincia</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="locality_id">Localidad</label>
                        <select id="locality_id" name="locality_id" class="form-control-ptcg">
                            <option value="">Selecciona Localidad</option>
                            {{-- Localities will be loaded here via JS --}}
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group" style="display:none;">
                        <input type="hidden" name="city" id="city_hidden" value="{{ old('city') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="venue">Lugar / Sede</label>
                        <input type="text" id="venue" name="venue" class="form-control-ptcg" value="{{ old('venue') }}" placeholder="Ej: Centro Comercial X">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="starts_at">Fecha de inicio</label>
                    <input type="datetime-local" id="starts_at" name="starts_at" class="form-control-ptcg" value="{{ old('starts_at') }}">
                </div>
            </div>
        </div>

        <div class="card-custom" style="margin-bottom:var(--spacing-lg);">
            <div class="card-custom-header"><h3 class="card-custom-title">Configuración de Rondas</h3></div>
            <div class="card-custom-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label" for="swiss_rounds">Rondas Swiss</label>
                        <input type="number" id="swiss_rounds" name="swiss_rounds" class="form-control-ptcg" value="{{ old('swiss_rounds') }}" min="3" max="15" placeholder="Auto">
                        <p style="font-size:0.75rem;color:var(--color-text-muted);margin-top:4px;">Dejar vacío para calcular automáticamente</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="match_time_minutes">Tiempo por Ronda (min) *</label>
                        <input type="number" id="match_time_minutes" name="match_time_minutes" class="form-control-ptcg" value="{{ old('match_time_minutes', 50) }}" min="15" max="90" required>
                    </div>
                </div>

                <div style="padding:var(--spacing-md);background:var(--color-bg-secondary);border-radius:var(--radius-md);border:1px solid var(--color-border);margin-bottom:var(--spacing-md);">
                    <label class="form-check-ptcg" style="margin-bottom:var(--spacing-sm);">
                        <input type="checkbox" name="top_cut_enabled" value="1" {{ old('top_cut_enabled') ? 'checked' : '' }} id="top_cut_cb" onchange="document.getElementById('top_cut_size_group').style.display=this.checked?'block':'none'">
                        <span style="font-weight:600;color:var(--color-text-primary);">Activar Top Cut (Eliminatorias)</span>
                    </label>
                    <div id="top_cut_size_group" style="display:{{ old('top_cut_enabled') ? 'block' : 'none' }};margin-top:var(--spacing-sm);">
                        <label class="form-label">Tamaño del Top Cut</label>
                        <select name="top_cut_size" class="form-control-ptcg" style="max-width:200px;">
                            <option value="4" {{ old('top_cut_size') === '4' ? 'selected' : '' }}>Top 4</option>
                            <option value="8" {{ old('top_cut_size', '8') === '8' ? 'selected' : '' }}>Top 8</option>
                            <option value="16" {{ old('top_cut_size') === '16' ? 'selected' : '' }}>Top 16</option>
                            <option value="32" {{ old('top_cut_size') === '32' ? 'selected' : '' }}>Top 32</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-custom" style="margin-bottom:var(--spacing-lg);">
            <div class="card-custom-header"><h3 class="card-custom-title">Inscripciones</h3></div>
            <div class="card-custom-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label" for="registration_opens_at">Abre inscripciones</label>
                        <input type="datetime-local" id="registration_opens_at" name="registration_opens_at" class="form-control-ptcg" value="{{ old('registration_opens_at') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="registration_closes_at">Cierra inscripciones</label>
                        <input type="datetime-local" id="registration_closes_at" name="registration_closes_at" class="form-control-ptcg" value="{{ old('registration_closes_at') }}">
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:var(--spacing-sm);">
                    <label class="form-check-ptcg">
                        <input type="checkbox" name="is_public" value="1" {{ old('is_public', '1') == '1' ? 'checked' : '' }}>
                        <span>Torneo público (visible para todos)</span>
                    </label>
                    <label class="form-check-ptcg">
                        <input type="checkbox" name="require_deck_list" value="1" {{ old('require_deck_list') ? 'checked' : '' }}>
                        <span>Requerir lista de mazo al inscribirse</span>
                    </label>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:var(--spacing-md);">
            <button type="submit" class="btn-ptcg btn-primary-ptcg">
                <i class="bi bi-check-circle"></i> Crear Torneo
            </button>
            <a href="{{ route('tournaments.index') }}" class="btn-ptcg btn-secondary-ptcg">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
async function loadLocalities(provinceId, selectedLocalityId = null) {
    const localitySelect = document.getElementById('locality_id');
    const cityHidden = document.getElementById('city_hidden');
    
    // Clear current options
    localitySelect.innerHTML = '<option value="">Cargando...</option>';
    
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
        localitySelect.innerHTML = '<option value="">Error al cargar localidades</option>';
    }
}

// Handle old input for locality if validation fails
@if(old('province_id'))
    loadLocalities({{ old('province_id') }}, {{ old('locality_id', 'null') }});
@endif
</script>
@endpush
