<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration()
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

        $response = $this->postJson('/api/v1/user/create', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['data' => ['user', 'token'], 'message']);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
        ]);
    }

    public function test_user_login()
    {
        $password = Hash::make('password123');
        $user = User::factory()->create(['email' => 'john.doe@example.com', 'password' => $password]);

        $data = ['email' => 'john.doe@example.com', 'password' => 'password123'];

        $response = $this->postJson('/api/v1/user/login', $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['user', 'token', 'avatar_url'], 'message']);

        $this->assertDatabaseHas('users', ['email' => 'john.doe@example.com']);
    }

    public function test_user_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/v1/user');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['uuid', 'first_name', 'last_name', 'email', 'avatar']);
    }

    public function test_user_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/v1/user/logout');

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_user_update()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'address' => '456 Elm St',
            'phone_number' => '9876543210',
            'avatar' => UploadedFile::fake()->image('new_avatar.jpg')
        ];

        $response = $this->putJson('/api/v1/user/edit', $data);

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'User account updated successfully']);

        $this->assertDatabaseHas('users', ['email' => 'jane.doe@example.com']);
    }

    public function test_user_deletion()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $response = $this->deleteJson('/api/v1/user');

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'User account deleted successfully']);

        $this->assertDatabaseMissing('users', ['uuid' => $user->uuid]);
    }

    public function test_forgot_password()
    {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $data = ['email' => 'john.doe@example.com'];

        $response = $this->postJson('/api/v1/user/forgot-password', $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['recovery_link', 'message']);
    }

    public function test_reset_password()
    {
        $user = User::factory()->create(['email' => 'john.doe@example.com']);

        $data = [
            'token' => 'fake-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->postJson('/api/v1/user/reset-password-token', $data);

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Password reset successfully']);
    }
}
