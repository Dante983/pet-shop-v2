<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Categories;
use App\Models\File;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $productNames = ['Dog Food', 'Cat Food', 'Bird Seed', 'Fish Food', 'Dog Toy', 'Cat Toy', 'Bird Cage', 'Fish Tank', 'Dog Leash', 'Cat Litter'];

        // Fetch a random category, brand, and file
        $category = Categories::inRandomOrder()->first();
        if (!$category) {
            $category = Categories::factory()->create();
        }
        $brand = Brand::inRandomOrder()->first();
        if (!$brand) {
            $brand = Brand::factory()->create();
        }
        $file = File::inRandomOrder()->first();
        if (!$file) {
            $file = File::factory()->create();
        }
        $uuid = (string) Str::uuid();

        return [
            'uuid' => $uuid,
            'category_uuid' => $category->uuid,
            'title' => $this->faker->randomElement($productNames),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'description' => $this->faker->paragraph,
            'metadata' => json_encode([
                'brand' => $brand->uuid,
                'image' => $file->uuid,
            ]),
        ];
    }
}
