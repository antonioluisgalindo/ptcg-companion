<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'user_id', 'match_points', 'matches_played',
        'matches_won', 'matches_lost', 'matches_drawn',
        'games_won', 'games_lost', 'games_played',
        'opponent_win_pct', 'opp_opp_win_pct', 'game_win_pct',
        'position', 'byes_received',
    ];

    protected $casts = [
        'opponent_win_pct' => 'float',
        'opp_opp_win_pct'  => 'float',
        'game_win_pct'     => 'float',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getWinPercentageAttribute(): float
    {
        if ($this->matches_played === 0) return 0.0;
        return round($this->matches_won / $this->matches_played * 100, 1);
    }

    public function getGameWinPercentageDisplayAttribute(): float
    {
        return round($this->game_win_pct * 100, 1);
    }
}
