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
}
