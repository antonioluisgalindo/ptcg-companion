<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntegrationController extends Controller
{
    /**
     * Import data from raw text (Copy-Paste from TOM tables).
     */
    public function importFromText(Request $request, Tournament $tournament)
    {
        $request->validate([
            'raw_data' => 'required|string',
            'import_type' => 'required|in:players,pairings'
        ]);

        $rawData = $request->input('raw_data');
        $type = $request->input('import_type');
        $lines = explode("\n", str_replace("\r", "", $rawData));
        
        $imported = 0;
        $errors = [];

        if ($type === 'players') {
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                // Split by tabs or multiple spaces
                $parts = preg_split('/\t+|\s{2,}/', trim($line));
                
                // Expected: Name, Surname, PlayerID, Category
                // Or: FullName, PlayerID, Category
                if (count($parts) < 2) continue;

                $name = $parts[0];
                $surname = count($parts) >= 4 ? $parts[1] : '';
                $player_id = count($parts) >= 4 ? $parts[2] : (count($parts) >= 2 ? $parts[1] : null);
                $cat_str = count($parts) >= 4 ? $parts[3] : (count($parts) >= 3 ? $parts[2] : 'MA');

                // Find or create user
                $user = null;
                if ($player_id && is_numeric($player_id)) {
                    $user = User::where('player_id', $player_id)->first();
                }

                if (!$user) {
                    $category = match(strtoupper(trim($cat_str))) {
                        'JR', 'JUNIOR' => 'junior',
                        'SR', 'SENIOR' => 'senior',
                        default => 'master'
                    };

                    $user = User::create([
                        'name' => $name,
                        'surname' => $surname,
                        'player_id' => $player_id,
                        'category' => $category,
                        'email' => strtolower(Str::slug($name . $surname)) . '_' . Str::random(4) . '@temp-ptcg.local',
                        'password' => Hash::make(Str::random(16)),
                    ]);
                    $user->assignRole('jugador');
                }

                if (!$user->isRegisteredIn($tournament)) {
                    TournamentRegistration::create([
                        'tournament_id' => $tournament->id,
                        'user_id' => $user->id,
                        'status' => 'confirmed',
                    ]);
                    $imported++;
                }
            }
            return back()->with('success', "Se han importado/inscrito {$imported} jugadores con éxito.");
        }

        if ($type === 'pairings') {
            // Create new round
            $roundNumber = $tournament->currentRoundNumber() + 1;
            $round = $tournament->rounds()->create([
                'number' => $roundNumber,
                'status' => 'active',
                'started_at' => now(),
                'time_limit_at' => now()->addMinutes($tournament->match_time_minutes),
            ]);

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;

                // Robust regex to find IDs inside parentheses or sequential numbers
                // Example: "1  Juan (123)  Pedro (456)" -> [1, 123, 456]
                preg_match_all('/\d+/', $line, $matches);
                $numbers = $matches[0];

                if (count($numbers) < 2) continue;

                $table = $numbers[0];
                $p1_id = $numbers[1];
                $p2_id = isset($numbers[2]) ? $numbers[2] : null;

                $player1 = User::where('player_id', $p1_id)->first();
                $player2 = $p2_id ? User::where('player_id', $p2_id)->first() : null;

                if (!$player1) {
                    $errors[] = "No se encontró ID {$p1_id} en mesa {$table}";
                    continue;
                }

                $round->pairings()->create([
                    'player1_id' => $player1->id,
                    'player2_id' => $player2?->id,
                    'table_number' => $table,
                    'result' => $p2_id ? 'pending' : 'bye',
                ]);
                $imported++;
            }
            
            $msg = "Ronda {$roundNumber} creada con {$imported} emparejamientos.";
            if (count($errors) > 0) {
                return back()->with('warning', $msg . " Errores: " . implode(', ', array_slice($errors, 0, 3)));
            }
            return back()->with('success', $msg);
        }
    }
}
