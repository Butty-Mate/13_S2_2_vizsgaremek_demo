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
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admidmin'),
            'role' => 'owner',
            'phone_number' => '+36 30 123 4567',
            'birth_date' => '1990-01-01',
            'email_verified_at' => now()
        ]);

        // Guest test user
        User::create([
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
            'password' => Hash::make('password'),
            'role' => 'guest',
            'phone_number' => '+36 30 987 6543',
            'birth_date' => '1995-05-15',
            'email_verified_at' => now()
        ]);

        $this->command->info('Test users created successfully!');
        $this->command->info('Admin: admin@admin.com / admidmin (owner role)');
        $this->command->info('Guest: guest@test.com / password (guest role)');
    }
}
