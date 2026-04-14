<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Tournament extends Model
{
    use HasFactory, LogsActivity;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tournament) {
            if (!$tournament->access_code) {
                $tournament->access_code = self::generateUniqueAccessCode();
            }
        });
    }

    public static function generateUniqueAccessCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            // Format like XXX-XXX
            $code = substr($code, 0, 3) . '-' . substr($code, 3, 3);
        } while (self::where('access_code', $code)->exists());

        return $code;
    }

    protected $fillable = [
        'starts_at', 'venue', 'city', 'province_id', 'locality_id', 'is_public', 'require_deck_list', 'match_format',
    ];

    protected $casts = [
        'top_cut_enabled'          => 'boolean',
        'is_public'                => 'boolean',
        'require_deck_list'        => 'boolean',
        'registration_opens_at'    => 'datetime',
        'registration_closes_at'   => 'datetime',
        'starts_at'                => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    // Relations
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function province(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function locality(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }

    public function registrations()
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function confirmedRegistrations()
    {
        return $this->registrations()->where('status', 'confirmed');
    }

    public function rounds()
    {
        return $this->hasMany(Round::class)->orderBy('number');
    }

    public function standings()
    {
        return $this->hasMany(Standing::class)->orderBy('position');
    }

    public function activeRound()
    {
        return $this->rounds()->where('status', 'active')->first();
    }

    public function currentRoundNumber(): int
    {
        return $this->rounds()->max('number') ?? 0;
    }

    // Helpers
    public function getSwissRoundsCountAttribute(): int
    {
        if ($this->swiss_rounds) return $this->swiss_rounds;
        $players = $this->confirmedRegistrations()->count();
        if ($players <= 4)  return 3;
        if ($players <= 8)  return 3;
        if ($players <= 16) return 4;
        if ($players <= 32) return 5;
        if ($players <= 64) return 6;
        if ($players <= 128) return 7;
        return 8;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft'        => ['label' => 'Borrador',       'class' => 'badge-secondary'],
            'registration' => ['label' => 'Inscripciones',  'class' => 'badge-info'],
            'ongoing'      => ['label' => 'En curso',       'class' => 'badge-success'],
            'finished'     => ['label' => 'Finalizado',     'class' => 'badge-dark'],
            'cancelled'    => ['label' => 'Cancelado',      'class' => 'badge-danger'],
            default        => ['label' => 'Desconocido',    'class' => 'badge-secondary'],
        };
    }

    public function getFormatLabelAttribute(): string
    {
        return match ($this->format) {
            'standard' => 'Standard',
            'expanded' => 'Expanded',
            'unlimited' => 'Unlimited',
            default => $this->format,
        };
    }

    public function getMatchFormatLabelAttribute(): string
    {
        return match ($this->match_format) {
            'bo1' => 'Mejor de 1 (Bo1)',
            'bo3' => 'Mejor de 3 (Bo3)',
            default => strtoupper($this->match_format),
        };
    }

    public function isOpenForRegistration(): bool
    {
        return $this->status === 'registration'
            && $this->confirmedRegistrations()->count() < $this->max_players;
    }
}
