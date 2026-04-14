<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'data', 'icon', 'link', 'is_read', 'read_at',
    ];

    protected $casts = [
        'data'     => 'array',
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function getIconForType(): string
    {
        return match ($this->type) {
            'pairing_ready'    => 'bi-controller',
            'result_reported'  => 'bi-clipboard-check',
            'round_finished'   => 'bi-flag-fill',
            'tournament_update' => 'bi-trophy',
            'registration_confirmed' => 'bi-check-circle',
            default => 'bi-bell',
        };
    }
}
