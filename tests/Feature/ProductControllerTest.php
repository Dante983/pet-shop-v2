<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_products_list()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_create_product()
    {
        $user = User::factory()->create();
        $category = Categories::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'category_uuid' => $category->uuid,
            'title' => 'Dog Food',
            'price' => 14.99,
            'description' => 'Odio rerum ipsum similique aliquid iste.',
            'metadata' => json_encode(['brand' => 'b2635a08-6447-4025-a0c8-e9c06189d378', 'image' => '5e2c1baf-cbf3-4d1d-8aed-c95e03ee00a5']),
        ];

        $response = $this->postJson('/api/v1/products/create', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['uuid', 'category_uuid', 'title', 'price', 'description', 'metadata']);

        $this->assertDatabaseHas('products', [
            'title' => 'Dog Food',
            'price' => 14.99,
        ]);
    }

    public function test_show_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $product = Product::factory()->create();

        $response = $this->getJson('/api/v1/products/' . $product->uuid);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['uuid', 'category_uuid', 'title', 'price', 'description', 'metadata']);
    }

    public function test_update_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $product = Product::factory()->create();

        $data = [
            'title' => 'Updated Dog Food',
            'price' => 19.99,
            'description' => 'Updated description.',
            'metadata' => json_encode(['brand' => 'b2635a08-6447-4025-a0c8-e9c06189d378', 'image' => '5e2c1baf-cbf3-4d1d-8aed-c95e03ee00a5']),
        ];

        $response = $this->putJson('/api/v1/products/' . $product->uuid, $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['uuid', 'category_uuid', 'title', 'price', 'description', 'metadata']);

        $this->assertDatabaseHas('products', [
            'uuid' => $product->uuid,
            'title' => 'Updated Dog Food',
            'price' => 19.99,
        ]);
    }

    public function test_delete_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $product = Product::factory()->create();

        $response = $this->deleteJson('/api/v1/products/' . $product->uuid);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('products', ['uuid' => $product->uuid]);
    }
}
