<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_brands_list()
    {
        Brand::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/brands');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_create_brand()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'title' => 'Brand Title',
            'slug' => 'brand-title'
        ];

        $response = $this->postJson('/api/v1/brands/create', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['uuid', 'title', 'slug']);

        $this->assertDatabaseHas('brands', [
            'title' => 'Brand Title',
            'slug' => 'brand-title',
        ]);
    }

    public function test_show_brand()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $brand = Brand::factory()->create();

        $response = $this->getJson('/api/v1/brands/' . $brand->uuid);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['uuid', 'title', 'slug']);
    }

    public function test_update_brand()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $brand = Brand::factory()->create();

        $data = [
            'title' => 'Updated Brand Title',
            'slug' => 'updated-brand-title'
        ];

        $response = $this->putJson('/api/v1/brands/' . $brand->uuid, $data);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['uuid', 'title', 'slug']);

        $this->assertDatabaseHas('brands', [
            'uuid' => $brand->uuid,
            'title' => 'Updated Brand Title',
            'slug' => 'updated-brand-title',
        ]);
    }

    public function test_delete_brand()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $brand = Brand::factory()->create();

        $response = $this->deleteJson('/api/v1/brands/' . $brand->uuid);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('brands', ['uuid' => $brand->uuid]);
    }
}
