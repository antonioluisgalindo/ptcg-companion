<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $this->authorize('update', Setting::class);
        return view('settings.index');
    }

    public function update(Request $request)
    {
        $this->authorize('update', Setting::class);
        $validated = $request->validate([
            'app_name'  => ['required', 'string', 'max:100'],
            'app_color' => ['required', 'string', 'max:7'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Configuración actualizada correctamente.');
    }

    public function resetDatabase()
    {
        $this->authorize('update', Setting::class);
        
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        // Since the database was reset, the current user session is no longer valid
        // We should log them out and redirect to login
        \Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Base de datos limpiada y resembrada con éxito. Por favor, inicia sesión de nuevo.');
    }
}
