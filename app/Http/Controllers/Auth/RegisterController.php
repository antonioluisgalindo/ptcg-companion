<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'surname'   => ['nullable', 'string', 'max:100'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'player_id' => ['nullable', 'string', 'max:20', 'unique:users'],
            'password'  => ['required', 'confirmed', Password::defaults()],
        ], [
            'name.required'    => 'El nombre es obligatorio.',
            'email.required'   => 'El email es obligatorio.',
            'email.unique'     => 'Este email ya está en uso.',
            'player_id.unique' => 'Este Player ID ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'surname'   => $validated['surname'] ?? null,
            'email'     => $validated['email'],
            'player_id' => $validated['player_id'] ?? null,
            'password'  => Hash::make($validated['password']),
        ]);

        $user->assignRole('jugador');

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
