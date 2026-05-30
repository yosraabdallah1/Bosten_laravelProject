<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // admin de test
        User::factory()->create([
            'name' => 'Admin Bosten',
            'email' => 'admin@bosten.tn',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Compte client de test
        User::factory()->create([
            'name' => 'Client Test',
            'email' => 'client@bosten.tn',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        $this->call([
            CategorySeeder::class,   // ← doit être AVANT ProductSeeder
            ProductSeeder::class,
        ]);
    }
}
