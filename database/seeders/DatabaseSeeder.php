<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin par défaut
        User::firstOrCreate(
            ['email' => 'admin@sibtel.tn'],
            [
                'name'     => 'Administrateur',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // Superviseur de test
        User::firstOrCreate(
            ['email' => 'superviseur@sibtel.tn'],
            [
                'name'     => 'SUPERVISEUR test',
                'password' => Hash::make('password'),
                'role'     => 'superviseur',
            ]
        );

        // Opérateur de test
        User::firstOrCreate(
            ['email' => 'operateur@sibtel.tn'],
            [
                'name'     => 'operateur',
                'password' => Hash::make('password'),
                'role'     => 'operateur',
            ]
        );

        $this->command->info('✓ Utilisateurs créés avec succès');
        $this->command->info('  admin@sibtel.tn / password');
        $this->command->info('  superviseur@sibtel.tn / password');
        $this->command->info('  operateur@sibtel.tn / password');
    }
}