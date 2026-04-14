@extends('layouts.guest')
@section('title', 'Registrarse')

@section('content')
<form method="POST" action="{{ route('register.post') }}">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-md);">
        <div class="form-group">
            <label class="form-label" for="name">Nombre *</label>
            <input type="text" id="name" name="name" class="form-control-ptcg"
                value="{{ old('name') }}" placeholder="Ash" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="surname">Apellido</label>
            <input type="text" id="surname" name="surname" class="form-control-ptcg"
                value="{{ old('surname') }}" placeholder="Ketchum">
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="email">Email *</label>
        <input type="email" id="email" name="email" class="form-control-ptcg"
            value="{{ old('email') }}" placeholder="ash@pokemon.com" required>
    </div>

    <div class="form-group">
        <label class="form-label" for="player_id">Player ID oficial
            <span style="font-weight:400;color:var(--color-text-muted)">(opcional)</span>
        </label>
        <input type="text" id="player_id" name="player_id" class="form-control-ptcg"
            value="{{ old('player_id') }}" placeholder="PL-00001"
            style="font-family:monospace;">
        <p style="font-size:0.75rem;color:var(--color-text-muted);margin-top:4px;">
            Tu ID de jugador oficial de Pokémon TCG
        </p>
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Contraseña *</label>
        <input type="password" id="password" name="password" class="form-control-ptcg"
            placeholder="Mínimo 8 caracteres" required>
    </div>

    <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirmar contraseña *</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control-ptcg"
            placeholder="Repite la contraseña" required>
    </div>

    <button type="submit" class="btn-ptcg btn-primary-ptcg" style="width:100%;justify-content:center;margin-top:var(--spacing-sm);">
        <i class="bi bi-person-plus"></i> Crear Cuenta
    </button>
</form>

<div class="guest-divider"></div>
<p style="text-align:center;font-size:0.875rem;color:var(--color-text-muted);">
    ¿Ya tienes cuenta?
    <a href="{{ route('login') }}" style="color:var(--color-primary-light);font-weight:600;">Inicia sesión</a>
</p>
@endsection
