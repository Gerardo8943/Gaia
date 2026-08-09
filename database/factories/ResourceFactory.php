<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Resource as ResourceModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceModel>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'measurement_unit' => 'Litros',
            'is_consumable' => true,
            'critical_threshold' => fake()->randomFloat(2, 10, 1000),
        ];
    }

    public function nonConsumable(): static
    {
        return $this->state(fn (): array => ['is_consumable' => false]);
    }
}
