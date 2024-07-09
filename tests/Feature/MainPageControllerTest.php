<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainPageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_blogs()
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/main/blog');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_show_blog()
    {
        $post = Post::factory()->create();

        $response = $this->getJson('/api/v1/main/blog/' . $post->uuid);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['uuid', 'title', 'content', 'created_at', 'updated_at']);
    }

    public function test_list_promotions()
    {
        Promotion::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/main/promotions');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3);
    }
}
