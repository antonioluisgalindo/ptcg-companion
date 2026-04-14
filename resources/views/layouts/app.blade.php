<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ App\Models\Setting::get('app_name', 'PTCG Companion') }}</title>
    <meta name="description" content="PTCG Companion — Herramienta de gestión de torneos de Pokémon TCG">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')

    <style>
        :root {
            --color-primary: {{ App\Models\Setting::get('app_color', '#E3350D') }};
            --color-primary-glow: {{ App\Models\Setting::get('app_color', '#E3350D') }}40;
        }
    </style>
</head>
<body>
<div class="app-container">

    <!-- ── Sidebar ─────────────────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-logo" style="text-decoration:none;">
                <div class="sidebar-logo-icon">🏆</div>
                <div>
                    <div class="sidebar-logo-text">{{ App\Models\Setting::get('app_name', 'PTCG Companion') }}</div>
                    <div class="sidebar-logo-sub">Tournament Manager</div>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-house nav-icon"></i>
                <span>Inicio</span>
            </a>

            <!-- Tournament Section -->
            <div class="nav-section-title">Torneos</div>

            <a href="{{ route('tournaments.index') }}" class="nav-item {{ request()->routeIs('tournaments.index') ? 'active' : '' }}">
                <i class="bi bi-trophy nav-icon"></i>
                <span>Torneos</span>
            </a>

            <!-- Notifications -->
            <div class="nav-section-title">Personal</div>
            <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell nav-icon"></i>
                <span>Notificaciones</span>
                @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                @if($unread > 0)
                    <span class="nav-badge">{{ $unread }}</span>
                @endif
            </a>

        </nav>

        <!-- Admin footer -->
        <div class="sidebar-footer">
            @if(auth()->user()->hasRole('admin'))
            <div class="nav-section-title" style="padding-top:0">Administración</div>
            <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people nav-icon"></i>
                <span>Usuarios</span>
            </a>
            <a href="{{ route('roles.index') }}" class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-check nav-icon"></i>
                <span>Roles & Permisos</span>
            </a>
            <a href="{{ route('activity-logs.index') }}" class="nav-item {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text nav-icon"></i>
                <span>Registro de Actividad</span>
            </a>
            <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear nav-icon"></i>
                <span>Configuración</span>
            </a>
            @endif
        </div>
    </aside>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>

    <!-- ── Main Content ──────────────────────────────────────────────── -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="mobile-menu-btn" id="mobile-menu-toggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            <div class="header-right">
                <!-- Notification Bell -->
                <a href="{{ route('notifications.index') }}" class="notification-bell" title="Notificaciones">
                    <i class="bi bi-bell"></i>
                    @if($unread ?? 0 > 0)
                        <span class="badge">{{ $unread }}</span>
                    @endif
                </a>

                <!-- User Menu -->
                <div class="user-menu" id="user-menu-toggle">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="user-avatar">
                    <i class="bi bi-chevron-down" style="font-size:0.75rem;color:var(--color-text-muted);"></i>

                    <div class="dropdown-menu-custom" id="user-dropdown">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item-custom">
                            <i class="bi bi-person"></i> Mi Perfil
                        </a>
                        <div style="height:1px;background:var(--color-border);margin:4px 0;"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item-custom danger">
                                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="content">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert-ptcg alert-success animate-fade-in-up" style="margin-bottom:var(--spacing-lg);">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="alert-ptcg alert-danger animate-fade-in-up" style="margin-bottom:var(--spacing-lg);">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="alert-ptcg alert-danger animate-fade-in-up" style="margin-bottom:var(--spacing-lg);">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Por favor, corrige los errores:</strong>
                        <ul style="margin:4px 0 0 16px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script>
// Mobile menu
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('mobile-overlay');
document.getElementById('mobile-menu-toggle')?.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
});
overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
});

// User dropdown
document.getElementById('user-menu-toggle')?.addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('user-dropdown').classList.toggle('show');
});
document.addEventListener('click', () => {
    document.getElementById('user-dropdown')?.classList.remove('show');
});

// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert-ptcg').forEach(el => {
        el.style.transition = 'opacity 0.5s ease';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 5000);
</script>

@stack('scripts')
</body>
</html>
