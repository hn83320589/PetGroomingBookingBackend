<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        /*$user = User::factory()->create([
            'name' => 'admin',
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);*/
        $user = User::find(1);
        $user->assignRole('管理員');
    }
}
