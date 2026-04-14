<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PTCG Companion') | Tournament Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: var(--spacing-md); }
        .guest-card {
            background: var(--color-bg-card);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            padding: var(--spacing-2xl);
            width: 100%;
            max-width: 420px;
            animation: fadeInUp 0.5s ease both;
        }
        .guest-logo {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        .guest-logo-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
            border-radius: var(--radius-lg);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto var(--spacing-md);
            box-shadow: 0 8px 24px var(--color-primary-glow);
        }
        .guest-title { font-size: 1.5rem; font-weight: 800; color: var(--color-text-primary); }
        .guest-subtitle { font-size: 0.875rem; color: var(--color-text-muted); margin-top: 4px; }
        .guest-divider { height: 1px; background: var(--color-border); margin: var(--spacing-lg) 0; }
    </style>
</head>
<body>
    <div class="guest-card">
        <div class="guest-logo">
            <div class="guest-logo-icon">⚡</div>
            <div class="guest-title">PTCG Companion</div>
            <div class="guest-subtitle">Tournament Manager</div>
        </div>

        @if(session('success'))
            <div class="alert-ptcg alert-success" style="margin-bottom:var(--spacing-md);">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-ptcg alert-danger" style="margin-bottom:var(--spacing-md);">
                <i class="bi bi-exclamation-circle-fill"></i>
                <ul style="margin:0;padding-left:16px;">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
