<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Compte admin unique — identifiants lus depuis l'environnement
     * (ADMIN_EMAIL / ADMIN_PASSWORD), jamais codés en dur.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL');
        $password = (string) env('ADMIN_PASSWORD');

        if ($email === '' || $password === '') {
            throw new \RuntimeException(
                'ADMIN_EMAIL et ADMIN_PASSWORD doivent être définis dans .env pour seeder le compte administrateur.'
            );
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Hermann FOMO',
                'password' => Hash::make($password),
            ]
        );
    }
}
