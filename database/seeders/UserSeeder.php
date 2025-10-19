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
        $password = Hash::make('password');

        // Clear existing test data
        $this->command->info('Creating users...');

        try {
            // Create admin user
            $admin = new User();
            $admin->name = 'Admin User';
            $admin->email = 'admin@example.com';
            $admin->password = $password;
            $admin->role = 'admin';
            $admin->is_active = true;
            $admin->email_verified_at = now();
            $admin->save();
            $this->command->info('Created admin user');

            Balance::create([
                'user_id' => $admin->id,
                'credits' => 10000,
                'reserved_credits' => 0,
            ]);

            // Create regular test user
            $user = new User();
            $user->name = 'Test User';
            $user->email = 'user@example.com';
            $user->password = $password;
            $user->role = 'user';
            $user->is_active = true;
            $user->email_verified_at = now();
            $user->save();
            $this->command->info('Created test user');

            Balance::create([
                'user_id' => $user->id,
                'credits' => 1000,
                'reserved_credits' => 0,
            ]);

            // Create 5 additional random users
            for ($i = 1; $i <= 5; $i++) {
                $randomUser = new User();
                $randomUser->name = "User {$i}";
                $randomUser->email = "user{$i}@example.com";
                $randomUser->password = $password;
                $randomUser->role = 'user';
                $randomUser->is_active = true;
                $randomUser->email_verified_at = now();
                $randomUser->save();

                Balance::create([
                    'user_id' => $randomUser->id,
                    'credits' => 1000,
                    'reserved_credits' => 0,
                ]);

                $this->command->info("Created user {$i}");
            }

            $this->command->info('Successfully created 7 users with balances (1 admin, 6 regular users)');
        } catch (\Exception $e) {
            $this->command->error('Failed to seed: ' . $e->getMessage());
            throw $e;
        }
    }
}
