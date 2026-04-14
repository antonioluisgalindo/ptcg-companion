@extends('layouts.app')
@section('title', 'Registrar Resultado')

@section('content')
@php
    $user = auth()->user();
    $isJudge = $user->hasAnyRole(['admin','juez']);
    $tournament = $pairing->round->tournament;
@endphp

<div style="margin-bottom:var(--spacing-lg);">
    <a href="{{ route('rounds.show', $pairing->round) }}" style="color:var(--color-text-muted);font-size:0.875rem;text-decoration:none;">
        <i class="bi bi-arrow-left"></i> Ronda {{ $pairing->round->number }}
    </a>
</div>

<div style="max-width:600px;margin:0 auto;">

    <!-- Match Header -->
    <div class="card-custom" style="margin-bottom:var(--spacing-lg);border-color:rgba(227,53,13,0.25);">
        <div class="card-custom-body">
            <p style="font-size:0.75rem;color:var(--color-text-muted);margin-bottom:var(--spacing-md);">
                <i class="bi bi-trophy"></i> {{ $tournament->name }} — Ronda {{ $pairing->round->number }}
                @if($pairing->table_number) — Mesa {{ $pairing->table_number }} @endif
            </p>

            <div style="display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:var(--spacing-lg);">
                <!-- Player 1 -->
                <div style="text-align:center;">
                    <img src="{{ $pairing->player1->avatar_url }}" alt="" style="width:56px;height:56px;border-radius:50%;margin-bottom:var(--spacing-sm);border:2px solid var(--color-border);">
                    <p style="font-weight:700;color:var(--color-text-primary);">{{ $pairing->player1->full_name }}</p>
                    @if($pairing->player1->player_id)
                        <p style="font-size:0.72rem;color:var(--color-text-muted);font-family:monospace;">{{ $pairing->player1->player_id }}</p>
                    @endif
                </div>

                <div style="text-align:center;">
                    <div style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;background:var(--color-bg-tertiary);border:1px solid var(--color-border);border-radius:50%;font-weight:800;color:var(--color-text-muted);font-size:0.9rem;">
                        VS
                    </div>
                </div>

                <!-- Player 2 -->
                <div style="text-align:center;">
                    <img src="{{ $pairing->player2->avatar_url }}" alt="" style="width:56px;height:56px;border-radius:50%;margin-bottom:var(--spacing-sm);border:2px solid var(--color-border);">
                    <p style="font-weight:700;color:var(--color-text-primary);">{{ $pairing->player2->full_name }}</p>
                    @if($pairing->player2->player_id)
                        <p style="font-size:0.72rem;color:var(--color-text-muted);font-family:monospace;">{{ $pairing->player2->player_id }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Current Result (if any) -->
    @if($pairing->matchResult)
    @php $mr = $pairing->matchResult; @endphp
    <div class="alert-ptcg alert-info" style="margin-bottom:var(--spacing-lg);">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>Resultado actual:</strong>
            {{ $pairing->player1->name }} {{ $mr->player1_wins }}–{{ $mr->player2_wins }} {{ $pairing->player2->name }}
            @if($mr->ties > 0) (+{{ $mr->ties }} empate@if($mr->ties>1)s@endif) @endif
            <br>
            <span style="font-size:0.8rem;">Registrado por {{ $mr->reportedBy->full_name }}
            @if($mr->is_judge_entry) <span class="badge-custom badge-warning" style="font-size:0.65rem;">Juez</span> @endif
            </span>
        </div>
    </div>
    @endif

    <!-- Result Form -->
    <div class="card-custom">
        <div class="card-custom-header">
            <h3 class="card-custom-title">
                @if($isJudge && $pairing->matchResult)
                    <i class="bi bi-pencil-square" style="color:var(--color-warning);margin-right:6px;"></i> Editar Resultado (Juez)
                @else
                    <i class="bi bi-clipboard-plus" style="color:var(--color-primary-light);margin-right:6px;"></i> Registrar Resultado
                @endif
            </h3>
        </div>
        <div class="card-custom-body">
            <form method="POST" action="{{ $isJudge && $pairing->matchResult ? route('pairings.editResult', $pairing) : route('pairings.result', $pairing) }}"
                id="result-form">
                @csrf
                @if($isJudge && $pairing->matchResult)
                    @method('PUT')
                @endif

                <!-- Games Section -->
                <p style="font-size:0.875rem;color:var(--color-text-secondary);margin-bottom:var(--spacing-lg);">
                    Introduce los resultados por cada game (Best of 3). El resultado del match se calcula automáticamente.
                </p>

                <!-- Visual game selector -->
                <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:var(--spacing-lg);margin-bottom:var(--spacing-xl);">
                    <!-- Player 1 wins -->
                    <div>
                        <label class="form-label" style="text-align:center;display:block;">Games ganados por</label>
                        <p style="text-align:center;font-weight:700;color:var(--color-text-primary);margin-bottom:var(--spacing-sm);">{{ $pairing->player1->name }}</p>
                        <div style="display:flex;justify-content:center;gap:var(--spacing-sm);">
                            @for($i=0;$i<=2;$i++)
                            <label style="cursor:pointer;">
                                <input type="radio" name="player1_wins" value="{{ $i }}" {{ (old('player1_wins', $pairing->matchResult?->player1_wins ?? '') == $i) ? 'checked' : '' }} required
                                    style="display:none;" class="game-radio" onchange="updatePreview()">
                                <div class="game-btn {{ (old('player1_wins', $pairing->matchResult?->player1_wins ?? '') == $i) ? 'selected' : '' }}"
                                    style="width:48px;height:48px;border-radius:var(--radius-md);border:2px solid var(--color-border);background:var(--color-bg-tertiary);display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:700;color:var(--color-text-secondary);transition:all 0.15s;">
                                    {{ $i }}
                                </div>
                            </label>
                            @endfor
                        </div>
                    </div>

                    <!-- Ties -->
                    <div style="text-align:center;">
                        <label class="form-label">Empates</label>
                        <select name="ties" class="form-control-ptcg" style="width:70px;text-align:center;">
                            <option value="0" {{ old('ties', $pairing->matchResult?->ties ?? 0) == 0 ? 'selected' : '' }}>0</option>
                            <option value="1" {{ old('ties', $pairing->matchResult?->ties ?? 0) == 1 ? 'selected' : '' }}>1</option>
                        </select>
                    </div>

                    <!-- Player 2 wins -->
                    <div>
                        <label class="form-label" style="text-align:center;display:block;">Games ganados por</label>
                        <p style="text-align:center;font-weight:700;color:var(--color-text-primary);margin-bottom:var(--spacing-sm);">{{ $pairing->player2->name }}</p>
                        <div style="display:flex;justify-content:center;gap:var(--spacing-sm);">
                            @for($i=0;$i<=2;$i++)
                            <label style="cursor:pointer;">
                                <input type="radio" name="player2_wins" value="{{ $i }}" {{ (old('player2_wins', $pairing->matchResult?->player2_wins ?? '') == $i) ? 'checked' : '' }} required
                                    style="display:none;" class="game-radio" onchange="updatePreview()">
                                <div class="game-btn {{ (old('player2_wins', $pairing->matchResult?->player2_wins ?? '') == $i) ? 'selected' : '' }}"
                                    style="width:48px;height:48px;border-radius:var(--radius-md);border:2px solid var(--color-border);background:var(--color-bg-tertiary);display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:700;color:var(--color-text-secondary);transition:all 0.15s;">
                                    {{ $i }}
                                </div>
                            </label>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Result Preview -->
                <div id="result-preview" style="display:none;text-align:center;padding:var(--spacing-md);background:var(--color-bg-secondary);border-radius:var(--radius-md);border:1px solid var(--color-border);margin-bottom:var(--spacing-lg);">
                    <p style="font-size:0.8rem;color:var(--color-text-muted);margin-bottom:4px;">Resultado del Match</p>
                    <p id="result-text" style="font-size:1.2rem;font-weight:700;color:var(--color-text-primary);"></p>
                </div>

                @if($isJudge)
                <div class="form-group">
                    <label class="form-label">Notas del Juez (opcional)</label>
                    <textarea name="notes" class="form-control-ptcg" rows="3"
                        placeholder="Notas sobre incidencias, disputas, etc.">{{ old('notes', $pairing->matchResult?->notes) }}</textarea>
                </div>
                @else
                    <input type="hidden" name="notes" value="">
                @endif

                <button type="submit" class="btn-ptcg btn-primary-ptcg" style="width:100%;justify-content:center;">
                    <i class="bi bi-check-circle"></i>
                    {{ $isJudge && $pairing->matchResult ? 'Actualizar Resultado' : 'Confirmar Resultado' }}
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.game-btn.selected {
    border-color: var(--color-primary) !important;
    background: rgba(227,53,13,0.15) !important;
    color: var(--color-primary-light) !important;
}
</style>

<script>
// Sync radio clicks with visual buttons
document.querySelectorAll('label').forEach(label => {
    const input = label.querySelector('input[type="radio"]');
    const btn = label.querySelector('.game-btn');
    if (!input || !btn) return;

    label.addEventListener('click', () => {
        // Deselect siblings in same group
        const name = input.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            r.parentElement.querySelector('.game-btn')?.classList.remove('selected');
        });
        btn.classList.add('selected');
    });
});

function updatePreview() {
    const p1 = document.querySelector('input[name="player1_wins"]:checked')?.value;
    const p2 = document.querySelector('input[name="player2_wins"]:checked')?.value;
    if (p1 === undefined || p2 === undefined) return;

    const preview = document.getElementById('result-preview');
    const text = document.getElementById('result-text');
    const p1name = '{{ $pairing->player1->name }}';
    const p2name = '{{ $pairing->player2->name }}';

    preview.style.display = 'block';
    if (parseInt(p1) > parseInt(p2)) {
        text.textContent = `🏆 ${p1name} gana el match`;
        text.style.color = 'var(--color-success)';
    } else if (parseInt(p2) > parseInt(p1)) {
        text.textContent = `🏆 ${p2name} gana el match`;
        text.style.color = 'var(--color-success)';
    } else {
        text.textContent = 'Empate en el match';
        text.style.color = 'var(--color-text-muted)';
    }
}

// Init on load
updatePreview();
</script>
@endsection
