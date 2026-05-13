<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ClaimAccountController extends Controller
{
    public function showForm()
    {
        return view('auth.claim-account');
    }

    public function claim(Request $request)
    {
        $request->validate([
            'player_id' => ['required', 'string'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('player_id', $request->player_id)->first();

        if (!$user) {
            return back()->withErrors(['player_id' => 'No se ha encontrado ninguna cuenta importada con este Play! Pokémon ID.']);
        }

        if (!str_ends_with($user->email, '@temp-ptcg.local')) {
            return back()->withErrors(['player_id' => 'Esta cuenta ya está reclamada o es una cuenta de usuario normal.']);
        }

        $user->update([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '¡Cuenta reclamada exitosamente! Bienvenido a PTCG Companion.');
    }
}
