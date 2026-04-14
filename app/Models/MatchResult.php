<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'pairing_id', 'reported_by', 'match_result',
        'player1_wins', 'player2_wins', 'ties', 'notes', 'is_judge_entry',
    ];

    protected $casts = [
        'is_judge_entry' => 'boolean',
    ];

    public function pairing()
    {
        return $this->belongsTo(Pairing::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getGamesPlayedAttribute(): int
    {
        return $this->player1_wins + $this->player2_wins + $this->ties;
    }
}
