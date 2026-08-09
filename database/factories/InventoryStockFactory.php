<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryStock>
 */
class InventoryStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'resource_id' => Resource::factory(),
            'quantity' => fake()->randomFloat(2, 100, 10000),
            'status' => InventoryStock::STATUS_OPTIMAL,
        ];
    }

    public function withQuantity(float $quantity): static
    {
        return $this->state(fn (): array => ['quantity' => $quantity]);
    }
}
