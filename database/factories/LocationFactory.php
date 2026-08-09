<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city().' Module',
            'type' => Location::TYPE_HABITABLE,
            'is_pressurized' => true,
            'occupants' => 0,
        ];
    }

    public function nonPressurized(): static
    {
        return $this->state(fn (): array => ['is_pressurized' => false]);
    }

    public function withOccupants(int $occupants): static
    {
        return $this->state(fn (): array => ['occupants' => $occupants]);
    }
}
