<?php

namespace Database\Factories;

use App\Models\Categories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoriesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Categories::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $categoryNames = ['Dogs', 'Cats', 'Birds', 'Fish', 'Reptiles', 'Small Pets'];
        $categoryName = $this->faker->randomElement($categoryNames);
        return [
            'uuid' => Str::uuid(),
            'title' => $categoryName,
            'slug' => Str::slug($categoryName) . '-' . Str::random(5),
        ];
    }
}
