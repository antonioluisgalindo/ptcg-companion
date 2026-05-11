<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PairingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StandingController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::get('/login',    [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login',   [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',  [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register',[RegisterController::class, 'register'])->name('register.post');

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Locations API
    Route::get('/locations/provinces', [App\Http\Controllers\Api\LocationController::class, 'provinces'])->name('locations.provinces');
    Route::get('/locations/provinces/{province}/localities', [App\Http\Controllers\Api\LocationController::class, 'localities'])->name('locations.localities');


    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile',           [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',           [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',  [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Notifications
    Route::get('/notifications',                        [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read',         [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{notification}/read',   [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::delete('/notifications/{notification}',      [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Tournaments
    Route::resource('tournaments', TournamentController::class);
    Route::get('/join/{access_code}',                        [TournamentController::class, 'joinByQR'])->name('tournaments.joinByQR');
    Route::post('/tournaments/join-by-code',                 [TournamentController::class, 'joinByCode'])->name('tournaments.joinByCode');
    Route::post('/tournaments/{tournament}/status',          [TournamentController::class, 'updateStatus'])->name('tournaments.status');
    Route::post('/tournaments/{tournament}/register',        [TournamentController::class, 'register'])->name('tournaments.register');
    Route::post('/registrations/{registration}/confirm',     [TournamentController::class, 'confirmRegistration'])->name('registrations.confirm');
    Route::post('/registrations/{registration}/drop',        [TournamentController::class, 'dropPlayer'])->name('registrations.drop');
    Route::delete('/registrations/{registration}/cancel',    [TournamentController::class, 'cancelRegistration'])->name('registrations.cancel');

    // Rounds
    Route::get('/rounds/{round}',                       [RoundController::class, 'show'])->name('rounds.show');
    Route::post('/tournaments/{tournament}/start-round', [RoundController::class, 'startRound'])->name('rounds.start');
    Route::post('/rounds/{round}/finish',               [RoundController::class, 'finishRound'])->name('rounds.finish');

    // Pairings
    Route::get('/pairings/{pairing}',              [PairingController::class, 'show'])->name('pairings.show');
    Route::post('/pairings/{pairing}/result',      [PairingController::class, 'reportResult'])->name('pairings.result');
    Route::put('/pairings/{pairing}/result',       [PairingController::class, 'editResult'])->name('pairings.editResult');

    // ── Integration (TOM) ────────────────────────────────────────────────────────
    Route::post('/tournaments/{tournament}/import-players',   [\App\Http\Controllers\IntegrationController::class, 'importPlayers'])->name('integration.import.players');
    Route::post('/tournaments/{tournament}/import-pairings',  [\App\Http\Controllers\IntegrationController::class, 'importPairings'])->name('integration.import.pairings');
    Route::post('/tournaments/{tournament}/import-text',      [\App\Http\Controllers\IntegrationController::class, 'importFromText'])->name('integration.import.text');

    // ── Admin/TO only ─────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Users
        Route::get('/users',              [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit',  [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}',       [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',    [UserController::class, 'destroy'])->name('users.destroy');

        // Roles
        Route::get('/roles', function () {
            $roles = \Spatie\Permission\Models\Role::withCount('users')->get();
            $permissions = \Spatie\Permission\Models\Permission::all();
            return view('roles.index', compact('roles', 'permissions'));
        })->name('roles.index');

        // Settings
        Route::get('/settings',   [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings',  [SettingController::class, 'update'])->name('settings.update');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});
