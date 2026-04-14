<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $query = User::with('roles');
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        $users = $query->latest()->paginate(20);
        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        $roles = Role::all();

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

        return view('users.edit', compact('user', 'roles', 'stats'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'surname'   => ['nullable', 'string', 'max:100'],
            'email'     => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'player_id' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'is_active' => ['boolean'],
            'role'      => ['nullable', 'exists:roles,name'],
        ]);

        $user->update($validated);

        if ($request->role && Auth::user()->hasRole('admin')) {
            $user->syncRoles([$request->role]);
        }

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        abort_if($user->id === Auth::id(), 403, 'No puedes eliminarte a ti mismo.');
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }
}
