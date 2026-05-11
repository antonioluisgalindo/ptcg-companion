<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    /**
     * Import data from raw text (Copy-Paste from TOM tables).
     */
    public function importFromText(Request $request, Tournament $tournament)
    {
        $request->validate([
            'raw_data'    => 'required|string',
            'import_type' => 'required|in:players,pairings',
        ]);

        $rawData = $request->input('raw_data');
        $type    = $request->input('import_type');
        $lines   = explode("\n", str_replace("\r", "", $rawData));

        if ($type === 'players') {
            [$imported, $errors] = $this->processPlayerLines($tournament, $lines);
            return back()->with('success', "Se han importado/inscrito {$imported} jugadores con éxito.");
        }

        if ($type === 'pairings') {
            [$imported, $errors, $roundNumber] = $this->processPairingLines($tournament, $lines);
            $msg = "Ronda {$roundNumber} creada con {$imported} emparejamientos.";
            if (count($errors) > 0) {
                return back()->with('warning', $msg . " Errores: " . implode(', ', array_slice($errors, 0, 3)));
            }
            return back()->with('success', $msg);
        }
    }

    /**
     * Import players from an uploaded CSV/TXT file.
     */
    public function importPlayers(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $content = file_get_contents($request->file('csv_file')->getRealPath());
        $lines   = explode("\n", str_replace("\r", "", $content));

        [$imported, $errors] = $this->processPlayerLines($tournament, $lines);

        $msg = "Se han importado/inscrito {$imported} jugadores desde el archivo.";
        if (count($errors) > 0) {
            return back()->with('warning', $msg . " Errores: " . implode(', ', array_slice($errors, 0, 3)));
        }
        return back()->with('success', $msg);
    }

    /**
     * Import pairings from an uploaded CSV/TXT file.
     */
    public function importPairings(Request $request, Tournament $tournament)
    {
        $this->authorize('update', $tournament);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $content = file_get_contents($request->file('csv_file')->getRealPath());
        $lines   = explode("\n", str_replace("\r", "", $content));

        [$imported, $errors, $roundNumber] = $this->processPairingLines($tournament, $lines);

        $msg = "Ronda {$roundNumber} creada con {$imported} emparejamientos desde el archivo.";
        if (count($errors) > 0) {
            return back()->with('warning', $msg . " Errores: " . implode(', ', array_slice($errors, 0, 3)));
        }
        return back()->with('success', $msg);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Process an array of text lines as player records.
     * Returns [imported_count, errors_array].
     */
    private function processPlayerLines(Tournament $tournament, array $lines): array
    {
        $imported = 0;
        $errors   = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Skip likely header lines
            if (preg_match('/^(nombre|name|jugador|player|#)/i', trim($line))) continue;

            // Split by tabs or multiple spaces
            $parts = preg_split('/\t+|\s{2,}/', trim($line));

            if (count($parts) < 2) continue;

            $name      = trim($parts[0]);
            $surname   = count($parts) >= 4 ? trim($parts[1]) : '';
            $player_id = count($parts) >= 4 ? trim($parts[2]) : (count($parts) >= 2 ? trim($parts[1]) : null);
            $cat_str   = count($parts) >= 4 ? trim($parts[3]) : (count($parts) >= 3 ? trim($parts[2]) : 'MA');

            // Find or create user
            $user = null;
            if ($player_id && is_numeric($player_id)) {
                $user = User::where('player_id', $player_id)->first();
            }

            if (!$user) {
                $category = match (strtoupper(trim($cat_str))) {
                    'JR', 'JUNIOR' => 'junior',
                    'SR', 'SENIOR' => 'senior',
                    default        => 'master',
                };

                $user = User::create([
                    'name'      => $name,
                    'surname'   => $surname,
                    'player_id' => $player_id,
                    'category'  => $category,
                    'email'     => strtolower(Str::slug($name . $surname)) . '_' . Str::random(4) . '@temp-ptcg.local',
                    'password'  => Hash::make(Str::random(16)),
                ]);
                $user->assignRole('jugador');
            }

            if (!$user->isRegisteredIn($tournament)) {
                TournamentRegistration::create([
                    'tournament_id' => $tournament->id,
                    'user_id'       => $user->id,
                    'status'        => 'confirmed',
                ]);
                $imported++;
            }
        }

        return [$imported, $errors];
    }

    /**
     * Process an array of text lines as pairing records, creating a new round.
     * Returns [imported_count, errors_array, round_number].
     */
    private function processPairingLines(Tournament $tournament, array $lines): array
    {
        $imported    = 0;
        $errors      = [];
        $roundNumber = $tournament->currentRoundNumber() + 1;

        $round = $tournament->rounds()->create([
            'number'        => $roundNumber,
            'status'        => 'active',
            'started_at'    => now(),
            'time_limit_at' => now()->addMinutes($tournament->match_time_minutes ?? 50),
        ]);

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Robust: find all numbers in line
            preg_match_all('/\d+/', $line, $matches);
            $numbers = $matches[0];

            if (count($numbers) < 2) continue;

            $table = $numbers[0];
            $p1_id = $numbers[1];
            $p2_id = isset($numbers[2]) ? $numbers[2] : null;

            $player1 = User::where('player_id', $p1_id)->first();
            $player2 = $p2_id ? User::where('player_id', $p2_id)->first() : null;

            if (!$player1) {
                $errors[] = "No se encontró jugador con ID {$p1_id} en mesa {$table}";
                continue;
            }

            $round->pairings()->create([
                'player1_id'   => $player1->id,
                'player2_id'   => $player2?->id,
                'table_number' => $table,
                'result'       => $p2_id ? 'pending' : 'bye',
                'result_confirmed' => $p2_id ? false : true,
            ]);
            $imported++;
        }

        return [$imported, $errors, $roundNumber];
    }
}
