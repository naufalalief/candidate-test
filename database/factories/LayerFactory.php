<?php

namespace Database\Factories;

use App\Models\Layer;
use App\Models\Layup;
use Illuminate\Database\Eloquent\Factories\Factory;

class LayerFactory extends Factory
{
    protected $model = Layer::class;

    public function definition(): array
    {
        return [
            'layup_id' => Layup::factory(),
            'layer_order' => fake()->numberBetween(1, 10),
            'thickness' => fake()->randomFloat(2, 1, 50),
            'width' => fake()->randomFloat(2, 10, 200),
            'angle' => fake()->randomFloat(2, 0, 180),
        ];
    }
}
