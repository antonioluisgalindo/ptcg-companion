<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id', 'number', 'type', 'status', 'started_at', 'finished_at', 'time_limit_at',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'finished_at'   => 'datetime',
        'time_limit_at' => 'datetime',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function pairings()
    {
        return $this->hasMany(Pairing::class)->orderBy('table_number');
    }

    public function isFinished(): bool
    {
        return $this->pairings()->where('result', 'pending')->count() === 0;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending'  => ['label' => 'Pendiente', 'class' => 'badge-secondary'],
            'active'   => ['label' => 'En curso',  'class' => 'badge-success'],
            'finished' => ['label' => 'Finalizada', 'class' => 'badge-dark'],
            default    => ['label' => 'Desconocido', 'class' => 'badge-secondary'],
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'swiss' ? 'Suiza' : 'Top Cut';
    }
}
