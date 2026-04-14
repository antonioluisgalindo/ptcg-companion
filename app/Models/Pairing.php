<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pairing extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_id', 'player1_id', 'player2_id', 'table_number', 'result', 'result_confirmed',
    ];

    protected $casts = [
        'result_confirmed' => 'boolean',
    ];

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function matchResult()
    {
        return $this->hasOne(MatchResult::class);
    }

    public function isBye(): bool
    {
        return is_null($this->player2_id);
    }

    public function getWinner(): ?User
    {
        return match ($this->result) {
            'player1_win' => $this->player1,
            'player2_win' => $this->player2,
            'bye'         => $this->player1,
            default       => null,
        };
    }

    public function getLoser(): ?User
    {
        return match ($this->result) {
            'player1_win' => $this->player2,
            'player2_win' => $this->player1,
            default       => null,
        };
    }

    public function involvesUser(int $userId): bool
    {
        return $this->player1_id === $userId || $this->player2_id === $userId;
    }

    public function getOpponentOf(int $userId): ?User
    {
        if ($this->player1_id === $userId) return $this->player2;
        if ($this->player2_id === $userId) return $this->player1;
        return null;
    }

    public function getResultForPlayer(int $userId): string
    {
        if ($this->result === 'pending') return 'pending';
        if ($this->result === 'bye') return 'bye';
        if ($this->result === 'draw') return 'draw';
        $isWinner = ($this->result === 'player1_win' && $this->player1_id === $userId)
                 || ($this->result === 'player2_win' && $this->player2_id === $userId);
        return $isWinner ? 'win' : 'loss';
    }

    public function getResultBadgeAttribute(): array
    {
        return match ($this->result) {
            'pending'     => ['label' => 'Pendiente',   'class' => 'badge-warning'],
            'player1_win' => ['label' => $this->player1?->full_name . ' gana', 'class' => 'badge-success'],
            'player2_win' => ['label' => $this->player2?->full_name . ' gana', 'class' => 'badge-success'],
            'draw'        => ['label' => 'Empate',      'class' => 'badge-secondary'],
            'bye'         => ['label' => 'Bye',         'class' => 'badge-info'],
            default       => ['label' => 'Desconocido', 'class' => 'badge-dark'],
        };
    }
}
