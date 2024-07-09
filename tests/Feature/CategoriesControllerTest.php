<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_categories_list()
    {
        Categories::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_create_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'title' => 'Dog Food',
            'slug' => 'dog-food'
        ];

        $response = $this->postJson('/api/v1/categories/create', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['uuid', 'title', 'slug']);

        $this->assertDatabaseHas('categories', [
            'title' => 'Dog Food',
            'slug' => 'dog-food',
        ]);
    }

    public function test_show_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $category = Categories::factory()->create();

        $response = $this->getJson('/api/v1/categories/' . $category->uuid);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['uuid', 'title', 'slug']);
    }

    public function test_update_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $category = Categories::factory()->create();

        $data = [
            'title' => 'Updated Dog Food',
            'slug' => 'updated-dog-food'
        ];

        $response = $this->putJson('/api/v1/categories/' . $category->uuid, $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['uuid', 'title', 'slug']);

        $this->assertDatabaseHas('categories', [
            'uuid' => $category->uuid,
            'title' => 'Updated Dog Food',
            'slug' => 'updated-dog-food',
        ]);
    }

    public function test_delete_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $category = Categories::factory()->create();

        $response = $this->deleteJson('/api/v1/categories/' . $category->uuid);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', ['uuid' => $category->uuid]);
    }
}
