@extends('layouts.app')
@section('title', 'Configuración')

@section('content')
<div style="max-width:500px;">
    <h2 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);margin-bottom:var(--spacing-xl);">Configuración</h2>
    <div class="card-custom">
        <div class="card-custom-header"><h3 class="card-custom-title">Aplicación</h3></div>
        <div class="card-custom-body">
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nombre de la App</label>
                    <input type="text" name="app_name" class="form-control-ptcg" value="{{ App\Models\Setting::get('app_name', 'PTCG Companion') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Color principal</label>
                    <div style="display:flex;gap:var(--spacing-sm);align-items:center;">
                        <input type="color" name="app_color" value="{{ App\Models\Setting::get('app_color', '#E3350D') }}" style="width:48px;height:40px;border-radius:var(--radius-sm);border:1px solid var(--color-border);background:none;cursor:pointer;">
                        <input type="text" class="form-control-ptcg" style="flex:1;" value="{{ App\Models\Setting::get('app_color', '#E3350D') }}" readonly>
                    </div>
                </div>
                <button type="submit" class="btn-ptcg btn-primary-ptcg">
                    <i class="bi bi-check-circle"></i> Guardar Configuración
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
