@extends('layouts.app')
@section('title', 'Notificaciones')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--spacing-xl);">
    <div>
        <h2 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);">Notificaciones</h2>
        <p style="color:var(--color-text-muted);font-size:0.875rem;margin-top:4px;">
            {{ $notifications->total() }} notificaciones
        </p>
    </div>
    <form method="POST" action="{{ route('notifications.markAllRead') }}">
        @csrf
        <button type="submit" class="btn-ptcg btn-secondary-ptcg btn-sm">
            <i class="bi bi-check2-all"></i> Marcar todas como leídas
        </button>
    </form>
</div>

<div class="card-custom">
    @forelse($notifications as $notification)
    <div class="notification-item {{ !$notification->is_read ? 'unread' : '' }}">
        <div class="notification-icon">
            <i class="bi {{ $notification->getIconForType() }}"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <p class="notification-title">{{ $notification->title }}</p>
            <p class="notification-message">{{ $notification->message }}</p>
            <p class="notification-time">{{ $notification->created_at->diffForHumans() }}</p>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:var(--spacing-xs);">
            @if($notification->link)
                <a href="{{ $notification->link }}" class="btn-ptcg btn-secondary-ptcg btn-sm">
                    Ver <i class="bi bi-arrow-right"></i>
                </a>
            @endif
            @if(!$notification->is_read)
                <form method="POST" action="{{ route('notifications.markRead', $notification) }}">
                    @csrf
                    <button type="submit" class="btn-ptcg btn-secondary-ptcg btn-sm" style="font-size:0.72rem;">
                        ✓ Leído
                    </button>
                </form>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="bi bi-bell-slash empty-state-icon"></i>
        <p class="empty-state-title">Sin notificaciones</p>
        <p class="empty-state-desc">¡Estás al día!</p>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="pagination-ptcg">
    @foreach($notifications->links()->elements[0] ?? [] as $page => $url)
        <a href="{{ $url }}" class="page-item-ptcg {{ $notifications->currentPage() == $page ? 'active' : '' }}">{{ $page }}</a>
    @endforeach
</div>
@endif
@endsection
