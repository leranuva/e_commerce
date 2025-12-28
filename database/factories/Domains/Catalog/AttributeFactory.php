<?php

namespace Database\Factories\Domains\Catalog;

use App\Domains\Catalog\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Catalog\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $attributes = ['Color', 'Talla', 'Material', 'Tamaño', 'Estilo'];
        return [
            'name' => $this->faker->unique()->randomElement($attributes),
        ];
    }
}

