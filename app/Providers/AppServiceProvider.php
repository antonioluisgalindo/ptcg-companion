<?php

namespace App\Providers;

use App\Models\Tournament;
use App\Models\User;
use App\Policies\TournamentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register Tournament Policy
        Gate::policy(Tournament::class, TournamentPolicy::class);

        // Admin can do anything
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) return true;
        });

        // User management gates
        Gate::define('viewAny', function ($user, $model) {
            if ($model === User::class) return $user->hasRole('admin');
            return false;
        });

        Gate::define('update', function ($user, $modelOrClass) {
            if ($modelOrClass instanceof User) {
                return $user->hasRole('admin') || $user->id === $modelOrClass->id;
            }
            if ($modelOrClass instanceof \App\Models\Setting) {
                return $user->hasRole('admin');
            }
            return false;
        });

        Gate::define('delete', function ($user, $model) {
            if ($model instanceof User) return $user->hasRole('admin');
            return false;
        });

        // Activity log
        Gate::define('viewAny-activity', function ($user) {
            return $user->hasRole('admin');
        });
    }
}
