@extends('layouts.app')
@section('title', 'Registro de Actividad')

@section('content')
<h2 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);margin-bottom:var(--spacing-xl);">Registro de Actividad</h2>

<div class="card-custom">
    <div class="card-custom-body p-0">
        @forelse($activities as $activity)
        <div class="activity-item" style="padding:var(--spacing-md) var(--spacing-lg);">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($activity->causer->name ?? 'Sistema') }}&background=E3350D&color=fff&bold=true"
                 alt="" class="activity-avatar">
            <div style="flex:1;">
                <p class="activity-text">
                    <strong>{{ $activity->causer->full_name ?? 'Sistema' }}</strong>
                    {{ $activity->description }}
                    @if($activity->subject_type)
                        <span style="color:var(--color-text-muted);font-size:0.75rem;">· {{ class_basename($activity->subject_type) }}</span>
                    @endif
                </p>
                <p class="activity-time">{{ $activity->created_at->format('d/m/Y H:i:s') }} · {{ $activity->created_at->diffForHumans() }}</p>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="bi bi-journal-x empty-state-icon"></i>
            <p class="empty-state-title">Sin actividad registrada</p>
            <p class="empty-state-desc">Aún no hay acciones grabadas en el registro.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
