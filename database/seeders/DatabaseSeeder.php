<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Categories;
use App\Models\File;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
                'password' => Hash::make('admin'),
                'is_admin' => true,
                'address' => 'Admin Address',
                'phone_number' => '1234567890',
            ]
        );

        Categories::factory()->count(5)->create();
        Brand::factory()->count(5)->create();
        Post::factory()->count(10)->create();
        Promotion::factory()->count(3)->create();
        File::factory()->count(10)->create();
        Product::factory()->count(50)->create();
    }
}
