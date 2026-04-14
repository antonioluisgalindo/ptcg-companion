@extends('layouts.guest')
@section('title', 'Iniciar Sesión')

@section('content')
<form method="POST" action="{{ route('login.post') }}">
    @csrf
    <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control-ptcg"
            value="{{ old('email') }}" placeholder="tu@email.com" required autofocus>
        @error('email')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Contraseña</label>
        <input type="password" id="password" name="password" class="form-control-ptcg"
            placeholder="••••••••" required>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-lg);">
        <label class="form-check-ptcg">
            <input type="checkbox" name="remember"> Recordarme
        </label>
    </div>

    <button type="submit" class="btn-ptcg btn-primary-ptcg" style="width:100%;justify-content:center;">
        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
    </button>
</form>

<div class="guest-divider"></div>
<p style="text-align:center;font-size:0.875rem;color:var(--color-text-muted);">
    ¿No tienes cuenta?
    <a href="{{ route('register') }}" style="color:var(--color-primary-light);font-weight:600;">Regístrate</a>
</p>
@endsection
