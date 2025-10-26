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
        $this->command->info('Creating users...');

        try {
            $this->createUserIfNotExists([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'credits' => 10000,
            ], $password);

            $this->createUserIfNotExists([
                'name' => 'Test User',
                'email' => 'user@example.com',
                'role' => 'user',
                'credits' => 1000,
            ], $password);

            for ($i = 1; $i <= 5; $i++) {
                $this->createUserIfNotExists([
                    'name' => "User {$i}",
                    'email' => "user{$i}@example.com",
                    'role' => 'user',
                    'credits' => 1000,
                ], $password);
            }

            $this->command->info('✅ Successfully created users with balances');
        } catch (\Exception $e) {
            $this->command->error('❌ Failed to seed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create user and balance if not exists.
     */
    private function createUserIfNotExists(array $data, string $password): void
    {
        $user = User::where('email', $data['email'])->first();

        if ($user) {
            $this->command->warn("Skipped: user with email {$data['email']} already exists");
            return;
        }

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $password;
        $user->role = $data['role'];
        $user->is_active = true;
        $user->email_verified_at = now();
        $user->save();

        Balance::create([
            'user_id' => $user->id,
            'credits' => $data['credits'],
            'reserved_credits' => 0,
        ]);

        $this->command->info("Created: {$data['name']} ({$data['email']})");
    }
}
