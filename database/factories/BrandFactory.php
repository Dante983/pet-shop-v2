<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Brand::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $brandNames = ['PetLife', 'AnimalCare', 'FurryFriends', 'Pawsome', 'WhiskerWorld'];
        $brandName = $this->faker->randomElement($brandNames);
        return [
            'uuid' => Str::uuid(),
            'title' => $brandName,
            'slug' => Str::slug($brandName) . '-' . Str::random(5),
        ];
    }
}
