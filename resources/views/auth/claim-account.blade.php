<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamar Cuenta | PTCG Companion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--color-bg-primary); padding: var(--spacing-lg);">
    
    <div style="width: 100%; max-width: 480px;">
        <div style="text-align: center; margin-bottom: var(--spacing-xl);">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; background:var(--color-primary); color:white; font-size:2rem; margin-bottom:var(--spacing-md); box-shadow:0 10px 25px -5px rgba(227, 53, 13, 0.4);">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 800; color: var(--color-text-primary); margin-bottom: 8px;">Reclamar Cuenta</h1>
            <p style="color: var(--color-text-secondary); line-height: 1.5;">Si fuiste importado a un torneo desde TOM, ingresa tu Play! Pokémon ID para activar tu cuenta y acceder a tus estadísticas.</p>
        </div>

        <div class="card-custom" style="padding: var(--spacing-xl);">
            @if ($errors->any())
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: var(--radius-md); padding: var(--spacing-md); margin-bottom: var(--spacing-lg);">
                    <ul style="color: var(--color-danger); margin: 0; padding-left: 20px; font-size: 0.875rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('claim.submit') }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="player_id">Play! Pokémon ID *</label>
                    <div style="position: relative;">
                        <i class="bi bi-123" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                        <input id="player_id" type="text" name="player_id" value="{{ old('player_id') }}" required autofocus class="form-control-ptcg" style="padding-left: 40px;" placeholder="Ej: 1234567">
                    </div>
                    <small style="color: var(--color-text-muted); font-size: 0.75rem; margin-top: 4px; display: block;">El ID numérico con el que juegas en los torneos oficiales.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Tu Correo Electrónico *</label>
                    <div style="position: relative;">
                        <i class="bi bi-envelope" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-control-ptcg" style="padding-left: 40px;" placeholder="tu@email.com">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Nueva Contraseña *</label>
                    <div style="position: relative;">
                        <i class="bi bi-lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                        <input id="password" type="password" name="password" required autocomplete="new-password" class="form-control-ptcg" style="padding-left: 40px;" placeholder="••••••••">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: var(--spacing-xl);">
                    <label class="form-label" for="password_confirmation">Confirmar Contraseña *</label>
                    <div style="position: relative;">
                        <i class="bi bi-lock-fill" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" required class="form-control-ptcg" style="padding-left: 40px;" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-ptcg btn-primary-ptcg" style="width: 100%; padding: 12px; font-size: 1rem;">
                    Reclamar mi Cuenta <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>

        <div style="text-align: center; margin-top: var(--spacing-xl);">
            <a href="{{ route('login') }}" style="color: var(--color-text-muted); text-decoration: none; font-size: 0.875rem; transition: color 0.2s;" class="hover-primary">
                <i class="bi bi-arrow-left"></i> Volver al Login
            </a>
        </div>
    </div>
</body>
</html>
