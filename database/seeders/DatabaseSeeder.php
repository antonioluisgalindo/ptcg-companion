<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ──────────────────────────────────────────────────────
        $permissions = [
            'tournaments-view',   'tournaments-create', 'tournaments-edit',
            'tournaments-delete', 'tournaments-manage',
            'rounds-start',       'rounds-finish',
            'results-report',     'results-edit',
            'standings-view',
            'users-view',         'users-edit',
            'roles-view',
            'activity-logs-view',
            'settings-view',      'settings-edit',
            'notifications-view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Roles ────────────────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $organizador = Role::firstOrCreate(['name' => 'organizador', 'guard_name' => 'web']);
        $organizador->syncPermissions([
            'tournaments-view', 'tournaments-create', 'tournaments-edit',
            'tournaments-delete', 'tournaments-manage',
            'rounds-start', 'rounds-finish',
            'results-report', 'results-edit',
            'standings-view',
            'notifications-view',
        ]);

        $juez = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'web']);
        $juez->syncPermissions([
            'tournaments-view',
            'rounds-finish',
            'results-report', 'results-edit',
            'standings-view',
            'notifications-view',
        ]);

        $jugador = Role::firstOrCreate(['name' => 'jugador', 'guard_name' => 'web']);
        $jugador->syncPermissions([
            'tournaments-view',
            'results-report',
            'standings-view',
            'notifications-view',
        ]);

        $espectador = Role::firstOrCreate(['name' => 'espectador', 'guard_name' => 'web']);
        $espectador->syncPermissions([
            'tournaments-view',
            'standings-view',
        ]);

        // ── Default Admin User ────────────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@ptcg.local'],
            [
                'name'      => 'Administrador',
                'surname'   => 'PTCG',
                'password'  => Hash::make('admin123'),
                'is_active' => true,
            ]
        );
        $adminUser->assignRole('admin');

        // ── Demo Users ────────────────────────────────────────────────────────
        $to = User::firstOrCreate(
            ['email' => 'to@ptcg.local'],
            [
                'name'      => 'Organizador',
                'surname'   => 'Demo',
                'player_id' => 'TO-0001',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $to->assignRole('organizador');

        $judge = User::firstOrCreate(
            ['email' => 'juez@ptcg.local'],
            [
                'name'      => 'Juez',
                'surname'   => 'Demo',
                'player_id' => 'JG-0001',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $judge->assignRole('juez');

        $player1 = User::firstOrCreate(
            ['email' => 'player1@ptcg.local'],
            [
                'name'      => 'Ash',
                'surname'   => 'Ketchum',
                'player_id' => 'PL-0001',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $player1->assignRole('jugador');

        $player2 = User::firstOrCreate(
            ['email' => 'player2@ptcg.local'],
            [
                'name'      => 'Misty',
                'surname'   => 'Cerulean',
                'player_id' => 'PL-0002',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $player2->assignRole('jugador');

        // ── Default Settings ──────────────────────────────────────────────────
        Setting::set('app_name', 'PTCG Companion');
        Setting::set('app_color', '#E3350D');

        // ── Locations (Provinces & Localities) ────────────────────────────────
        $this->call([
            LocationSeeder::class,
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('   Admin: admin@ptcg.local / admin123');
        $this->command->info('   TO:    to@ptcg.local / password');
        $this->command->info('   Judge: juez@ptcg.local / password');
        $this->command->info('   Player1: player1@ptcg.local / password');
    }
}
