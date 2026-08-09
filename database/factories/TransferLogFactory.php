<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use App\Models\Resource;
use App\Models\TransferLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransferLog>
 */
class TransferLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_location_id' => Location::factory(),
            'to_location_id' => Location::factory(),
            'resource_id' => Resource::factory(),
            'quantity' => fake()->randomFloat(2, 1, 1000),
            'user_id' => User::factory(),
        ];
    }
}
