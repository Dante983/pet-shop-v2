<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PromotionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Promotion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $promotionTitles = ['Summer Sale', 'Winter Special', 'Holiday Discounts', 'Clearance Sale', 'Weekend Special'];
        $promotionTitle = $this->faker->randomElement($promotionTitles);
        return [
            'uuid' => Str::uuid(),
            'title' => $promotionTitle,
            'content' => $this->faker->text(200),
            'metadata' => ['valid_from' => $this->faker->date('Y-m-d'), 'valid_to' => $this->faker->date('Y-m-d')],
        ];
    }
}
