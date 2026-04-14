<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) return true;
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tournament $tournament): bool
    {
        return $tournament->is_public || $user->hasAnyRole(['organizador', 'juez']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['organizador', 'admin']);
    }

    public function update(User $user, Tournament $tournament): bool
    {
        return $user->hasRole('organizador') && $tournament->organizer_id === $user->id
            || $user->hasRole('juez');
    }

    public function delete(User $user, Tournament $tournament): bool
    {
        return $user->hasRole('organizador') && $tournament->organizer_id === $user->id;
    }
}
