<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'user_id', 'deck_name', 'deck_list', 'status', 'seed', 'dropped_at',
    ];

    protected $casts = [
        'dropped_at' => 'datetime',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending'       => ['label' => 'Pendiente',       'class' => 'badge-warning'],
            'confirmed'     => ['label' => 'Confirmado',      'class' => 'badge-success'],
            'dropped'       => ['label' => 'Abandonó',        'class' => 'badge-dark'],
            'disqualified'  => ['label' => 'Descalificado',   'class' => 'badge-danger'],
            default         => ['label' => 'Desconocido',     'class' => 'badge-secondary'],
        };
    }
}
