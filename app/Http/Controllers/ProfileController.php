<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        $standings = $user->standings()->with('tournament')->get();
        
        $stats = [
            'total_points' => $standings->sum('match_points'),
            'total_wins'   => $standings->sum('matches_won'),
            'total_losses' => $standings->sum('matches_lost'),
            'total_draws'  => $standings->sum('matches_drawn'),
            'tournaments'  => $standings->count(),
            'win_rate'     => 0,
        ];

        $totalMatches = $stats['total_wins'] + $stats['total_losses'] + $stats['total_draws'];
        if ($totalMatches > 0) {
            $stats['win_rate'] = round(($stats['total_wins'] / $totalMatches) * 100, 1);
        }

        // Get past tournament history ordered by most recent
        $history = $standings->sortByDesc(fn($s) => $s->tournament->starts_at);

        return view('profile.show', [
            'user' => $user,
            'stats' => $stats,
            'history' => $history
        ]);
    }

    public function edit()
    {
        $user = Auth::user();
        $standings = $user->standings;
        
        $stats = [
            'total_points' => $standings->sum('match_points'),
            'total_wins'   => $standings->sum('matches_won'),
            'total_losses' => $standings->sum('matches_lost'),
            'total_draws'  => $standings->sum('matches_drawn'),
            'tournaments'  => $standings->count(),
            'win_rate'     => 0,
        ];

        $totalMatches = $stats['total_wins'] + $stats['total_losses'] + $stats['total_draws'];
        if ($totalMatches > 0) {
            $stats['win_rate'] = round(($stats['total_wins'] / $totalMatches) * 100, 1);
        }

        return view('profile.edit', [
            'user' => $user,
            'stats' => $stats
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'surname'    => ['nullable', 'string', 'max:100'],
            'player_id'  => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'bio'        => ['nullable', 'string', 'max:500'],
            'avatar'     => ['nullable', 'image', 'max:2048'],
        ]);

        $user->fill($validated);

        if ($request->has('birth_date')) {
            $user->updateCategoryFromBirthDate();
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);
        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
