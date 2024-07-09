<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_admin_user()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'address' => '123 Main St',
            'phone_number' => '1234567890',
            'avatar' => UploadedFile::fake()->image('avatar.jpg')
        ];

        $response = $this->postJson('/api/v1/admin/create', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['data' => ['user', 'token'], 'message']);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
        ]);
    }

    public function test_login_admin_user()
    {
        $password = Hash::make('password123');
        $user = User::factory()->create(['email' => 'admin@example.com', 'password' => $password, 'is_admin' => true]);

        $data = ['email' => 'admin@example.com', 'password' => 'password123'];

        $response = $this->postJson('/api/v1/admin/login', $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
    }

    public function test_logout_admin_user()
    {
        // Create and authenticate an admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'api');

        // Send POST request to logout
        $response = $this->getJson('/api/v1/admin/logout');

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_user_listing()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'api');

        User::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/admin/user-listing');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_edit_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'api');

        $user = User::factory()->create();
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'address' => '456 Elm St',
            'phone_number' => '9876543210',
            'avatar' => UploadedFile::fake()->image('new_avatar.jpg')
        ];

        $response = $this->putJson("/api/v1/admin/user-edit/{$user->uuid}", $data);

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'User updated successfully']);

        $this->assertDatabaseHas('users', ['email' => 'jane.doe@example.com']);
    }

    public function test_delete_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'api');

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/user-delete/{$user->uuid}");

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['uuid' => $user->uuid]);
    }
}
