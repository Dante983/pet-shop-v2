<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a hardcoded admin account
        User::updateOrCreate(
            ['email' => 'admin@buckhill.co.uk'],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@buckhill.co.uk',
                'password' => Hash::make('admin'), // Replace with a secure password
                'is_admin' => true,
                'address' => 'Admin Address',
                'phone_number' => '1234567890',
            ]
        );
    }
}
