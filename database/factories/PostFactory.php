<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomNumber = mt_rand(100000, 999999);

        return [
            'title' => fake()->title(),
            'url' => fake()->url(),
            'cover' => $this->generateImageUrls(),
            'number' => $randomNumber,
            'info' => fake()->paragraph(),
            'categorie_id' => 1,
        ];
    }

    protected function generateImageUrls()
    {
        $colors = ['ff9d00', '00ad65', '00b8c7', '008aeb', '0060ff', '6c00ff', 'fd00ff', 'ff0020', 'ff7d6e', 'ff7724', 'ee8700', '00bad8', '000000', '254abd', 'c61480', '00baff', '6a6aba', '3cbfdc', 'ff60bb'];
        $XColor = $colors[array_rand($colors)];

        return "https://ui-avatars.com/api/?background=$XColor&color=fff&name=$XColor";
    }
}
