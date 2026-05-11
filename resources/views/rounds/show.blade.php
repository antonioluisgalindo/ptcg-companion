@extends('layouts.app')
@section('title', 'Ronda ' . $round->number . ' — ' . $round->tournament->name)

@section('content')
@php $user = auth()->user(); @endphp

<div style="margin-bottom:var(--spacing-lg);">
    <a href="{{ route('tournaments.show', $round->tournament) }}" style="color:var(--color-text-muted);font-size:0.875rem;text-decoration:none;">
        <i class="bi bi-arrow-left"></i> {{ $round->tournament->name }}
    </a>
</div>

<!-- Round Header -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:var(--spacing-lg);flex-wrap:wrap;gap:var(--spacing-md);">
    <div>
        <h2 style="font-size:1.5rem;font-weight:800;color:var(--color-text-primary);">
            Ronda {{ $round->number }}
            @if($round->type === 'top_cut')<span style="font-size:1rem;color:var(--color-warning);"> — Top Cut</span>@endif
        </h2>
        <div style="display:flex;gap:var(--spacing-sm);margin-top:var(--spacing-xs);align-items:center;flex-wrap:wrap;">
            <span class="badge-custom badge-{{ match($round->status) { 'active' => 'success', 'finished' => 'dark', default => 'secondary' } }}">
                {{ $round->status_badge['label'] }}
            </span>
            @if($round->started_at)
                <span style="font-size:0.8rem;color:var(--color-text-muted);">
                    <i class="bi bi-clock"></i> Inició {{ $round->started_at->format('H:i') }}
                </span>
            @endif
            @if($round->time_limit_at && $round->status === 'active')
                <span class="match-timer" data-limit="{{ $round->time_limit_at->toIso8601String() }}" style="font-size:0.85rem;color:var(--color-warning);font-weight:700;display:inline-flex;align-items:center;gap:4px;background:rgba(234,179,8,0.1);padding:4px 8px;border-radius:var(--radius-md);">
                    <i class="bi bi-hourglass-split"></i> <span class="timer-display">Calculando...</span>
                </span>
            @endif
            <span style="font-size:0.8rem;color:var(--color-text-muted);">
                {{ $round->pairings()->where('result','!=','pending')->count() }}/{{ $round->pairings->count() }} resultados
            </span>
        </div>
    </div>
    @if($round->status === 'active' && ($user->hasAnyRole(['admin','organizador','juez'])))
    <form method="POST" action="{{ route('rounds.finish', $round) }}">
        @csrf
        @if($round->isFinished())
            <button type="submit" class="btn-ptcg btn-success-ptcg">
                <i class="bi bi-flag-fill"></i> Cerrar Ronda
            </button>
        @else
            <button type="submit" class="btn-ptcg btn-secondary-ptcg"
                onclick="return confirm('Aún hay resultados pendientes. ¿Cerrar igualmente?')">
                <i class="bi bi-flag"></i> Forzar Cierre
            </button>
        @endif
    </form>
    @endif
</div>

<!-- Pairings Table -->
<div class="card-custom">
    <div class="card-custom-header">
        <h3 class="card-custom-title">Emparejamientos</h3>
        <span style="font-size:0.8rem;color:var(--color-text-muted);">{{ $round->pairings->count() }} partidas</span>
    </div>
    <div>
        <!-- Table header -->
        <div style="display:grid;grid-template-columns:80px 1fr auto 1fr 120px 100px;gap:var(--spacing-md);padding:10px var(--spacing-lg);background:var(--color-bg-tertiary);border-bottom:1px solid var(--color-border);">
            <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;">Mesa</span>
            <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;">Jugador 1</span>
            <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;text-align:center;">Games</span>
            <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;text-align:right;">Jugador 2</span>
            <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;text-align:center;">Estado</span>
            <span style="font-size:0.72rem;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;text-align:center;">Acción</span>
        </div>

        @foreach($round->pairings as $pairing)
        @php
            $isMyPairing = $pairing->involvesUser($user->id);
            $mr = $pairing->matchResult;
        @endphp
        <div style="display:grid;grid-template-columns:80px 1fr auto 1fr 120px 100px;gap:var(--spacing-md);padding:14px var(--spacing-lg);border-bottom:1px solid var(--color-border);align-items:center;{{ $isMyPairing ? 'background:rgba(227,53,13,0.06);' : '' }}">

            <!-- Mesa -->
            <div>
                @if($pairing->table_number)
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;background:var(--color-bg-tertiary);border:1px solid var(--color-border);border-radius:var(--radius-md);font-size:0.85rem;font-weight:700;color:var(--color-text-secondary);">
                        {{ $pairing->table_number }}
                    </span>
                @else
                    <span style="color:var(--color-text-muted);font-size:0.75rem;">BYE</span>
                @endif
            </div>

            <!-- Player 1 -->
            <div style="display:flex;align-items:center;gap:var(--spacing-sm);">
                <img src="{{ $pairing->player1->avatar_url }}" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                <div>
                    <span style="font-size:0.875rem;font-weight:{{ $pairing->result === 'player1_win' ? '700' : '500' }};color:{{ $pairing->result === 'player1_win' ? 'var(--color-text-primary)' : 'var(--color-text-secondary)' }};">
                        {{ $pairing->player1->full_name }}
                    </span>
                    @if($pairing->result === 'player1_win')
                        <i class="bi bi-trophy-fill" style="color:var(--color-warning);font-size:0.7rem;margin-left:4px;"></i>
                    @endif
                </div>
            </div>

            <!-- Score -->
            <div style="text-align:center;min-width:60px;">
                @if($pairing->isBye())
                    <span class="badge-custom badge-info" style="font-size:0.7rem;">BYE</span>
                @elseif($mr)
                    <span style="font-size:1rem;font-weight:700;color:var(--color-text-primary);">
                        {{ $mr->player1_wins }}-{{ $mr->player2_wins }}
                    </span>
                    @if($mr->ties > 0)
                        <span style="font-size:0.7rem;color:var(--color-text-muted);">(+{{ $mr->ties }})</span>
                    @endif
                @else
                    <span style="font-size:0.75rem;font-weight:700;color:var(--color-text-muted);">VS</span>
                @endif
            </div>

            <!-- Player 2 -->
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:var(--spacing-sm);">
                @if($pairing->player2)
                <div style="text-align:right;">
                    @if($pairing->result === 'player2_win')
                        <i class="bi bi-trophy-fill" style="color:var(--color-warning);font-size:0.7rem;margin-right:4px;"></i>
                    @endif
                    <span style="font-size:0.875rem;font-weight:{{ $pairing->result === 'player2_win' ? '700' : '500' }};color:{{ $pairing->result === 'player2_win' ? 'var(--color-text-primary)' : 'var(--color-text-secondary)' }};">
                        {{ $pairing->player2->full_name }}
                    </span>
                </div>
                <img src="{{ $pairing->player2->avatar_url }}" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                @else
                    <span style="color:var(--color-text-muted);font-size:0.8rem;">— BYE —</span>
                @endif
            </div>

            <!-- Status -->
            <div style="text-align:center;">
                @if($pairing->result === 'pending')
                    <span class="badge-custom badge-warning">Pendiente</span>
                @elseif($pairing->result === 'bye')
                    <span class="badge-custom badge-info">Auto-Bye</span>
                @else
                    <span class="badge-custom badge-success">
                        {{ $pairing->result_confirmed ? 'Confirmado' : 'Registrado' }}
                    </span>
                @endif
            </div>

            <!-- Action -->
            <div style="text-align:center;">
                @if($pairing->result === 'pending' && !$pairing->isBye())
                    @if($isMyPairing || $user->hasAnyRole(['admin','juez','organizador']))
                        <a href="{{ route('pairings.show', $pairing) }}" class="btn-ptcg btn-primary-ptcg btn-sm">
                            <i class="bi bi-clipboard-plus"></i> Resultado
                        </a>
                    @endif
                @elseif($user->hasAnyRole(['admin','juez']))
                    <a href="{{ route('pairings.show', $pairing) }}" class="btn-ptcg btn-secondary-ptcg btn-sm">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
function updateRoundTimer() {
    const timerElements = document.querySelectorAll('.match-timer');
    timerElements.forEach(el => {
        const timeLimit = el.getAttribute('data-limit');
        if (!timeLimit) return;

        const limitDate = new Date(timeLimit);
        const now = new Date();
        const diff = limitDate - now;
        const display = el.querySelector('.timer-display');

        if (diff <= 0) {
            display.textContent = "¡Tiempo Agotado!";
            el.style.color = 'var(--color-danger)';
            el.style.background = 'rgba(239,68,68,0.1)';
        } else {
            const m = Math.floor((diff / 1000 / 60) % 60);
            const s = Math.floor((diff / 1000) % 60);
            display.textContent = m + "m " + (s < 10 ? '0' : '') + s + "s restantes";
        }
    });
}
setInterval(updateRoundTimer, 1000);
updateRoundTimer();
</script>
@endpush

@endsection
