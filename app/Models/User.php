<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $fillable = [
        'name',
        'surname',
        'birth_date',
        'category',
        'email',
        'password',
        'player_id',
        'avatar',
        'bio',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date'        => 'date',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /**
     * Determine the age category based on birth year for the 2024-2025 season.
     * Junior: Born 2013+
     * Senior: Born 2009-2012
     * Masters: Born 2008 or earlier
     */
    public function updateCategoryFromBirthDate(): void
    {
        if (!$this->birth_date) return;

        $year = $this->birth_date->year;

        if ($year >= 2013) {
            $this->category = 'junior';
        } elseif ($year >= 2009) {
            $this->category = 'senior';
        } else {
            $this->category = 'master';
        }
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'junior' => 'Junior',
            'senior' => 'Senior',
            'master' => 'Master',
            default  => 'Master',
        };
    }

    public function getCategorySiglaAttribute(): string
    {
        return match ($this->category) {
            'junior' => 'JR',
            'senior' => 'SR',
            'master' => 'MA',
            default  => 'MA',
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    // Relations
    public function organizedTournaments()
    {
        return $this->hasMany(Tournament::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function standings()
    {
        return $this->hasMany(Standing::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    // Helpers
    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->surname}");
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=E3350D&color=fff&bold=true';
    }

    public function isRegisteredIn(Tournament $tournament): bool
    {
        return $this->registrations()->where('tournament_id', $tournament->id)->exists();
    }

    public function getRegistrationIn(Tournament $tournament): ?TournamentRegistration
    {
        return $this->registrations()->where('tournament_id', $tournament->id)->first();
    }
}
