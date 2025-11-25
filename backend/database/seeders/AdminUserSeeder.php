<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin/Owner user
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admidmin'),
                'role' => 'owner',
                'phone_number' => '+36 30 123 4567',
                'birth_date' => '1990-01-01',
                'email_verified_at' => now()
            ]
        );

        // Owner test user
        User::firstOrCreate(
            ['email' => 'owner@test.com'],
            [
                'name' => 'Test Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone_number' => '+36 30 555 1234',
                'birth_date' => '1985-03-20',
                'email_verified_at' => now()
            ]
        );

        // Guest test user
        User::firstOrCreate(
            ['email' => 'guest@test.com'],
            [
                'name' => 'Test Guest',
                'password' => Hash::make('password'),
                'role' => 'guest',
                'phone_number' => '+36 30 987 6543',
                'birth_date' => '1995-05-15',
                'email_verified_at' => now()
            ]
        );

        $this->command->info('Test users created successfully!');
        $this->command->info('Admin: admin@admin.com / admidmin (owner role)');
        $this->command->info('Owner: owner@test.com / password (owner role)');
        $this->command->info('Guest: guest@test.com / password (guest role)');
    }
}
