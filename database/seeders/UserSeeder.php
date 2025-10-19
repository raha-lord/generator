<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Balance::create([
            'user_id' => $admin->id,
            'credits' => 10000,
            'reserved_credits' => 0,
        ]);

        // Create regular test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Balance::create([
            'user_id' => $user->id,
            'credits' => 1000,
            'reserved_credits' => 0,
        ]);

        // Create 5 additional random users
        for ($i = 1; $i <= 5; $i++) {
            $randomUser = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            Balance::create([
                'user_id' => $randomUser->id,
                'credits' => 1000,
                'reserved_credits' => 0,
            ]);
        }

        $this->command->info('Created 7 users with balances (1 admin, 6 regular users)');
    }
}
