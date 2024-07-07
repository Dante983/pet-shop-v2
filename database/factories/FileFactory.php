<?php

namespace Database\Factories;

use App\Models\File;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->word;
        $mimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'path' => "/images/{$name}.jpg",  // Adjust this to match your actual file structure
            'size' => $this->faker->randomNumber(),  // Generates a random number
            'type' => 'image',
            'mime_type' => $this->faker->randomElement($mimeTypes),
        ];
    }
}
