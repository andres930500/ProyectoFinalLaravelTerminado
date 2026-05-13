<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@reservacancha.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
            ]
        );
    }
}
