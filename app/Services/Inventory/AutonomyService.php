<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Services\LifeSupport\EnergyConsumptionService;
use App\Services\LifeSupport\FoodConsumptionService;
use App\Services\LifeSupport\OxygenConsumptionService;
use App\Services\LifeSupport\WaterManagementService;

final class AutonomyService
{
    public function __construct(
        private readonly OxygenConsumptionService $oxygen,
        private readonly WaterManagementService $water,
        private readonly FoodConsumptionService $food,
        private readonly EnergyConsumptionService $energy,
    ) {}

    /**
     * Estimated hours of autonomy for each consumable stock at a location,
     * based on the current occupants and consumption rates.
     *
     * @return array<int, array{
     *     resource_name: string,
     *     measurement_unit: string,
     *     quantity: float,
     *     status: string,
     *     hours: float|null,
     * }>
     */
    public function forLocation(Location $location): array
    {
        $location->loadMissing('stocks.resource');

        $rates = [
            $this->oxygen->resourceName() => $this->oxygen->ratePerPersonPerHour(),
            $this->water->resourceName() => $this->water->ratePerPersonPerHour(),
            $this->food->resourceName() => $this->food->ratePerPersonPerHour(),
            $this->energy->resourceName() => $this->energy->ratePerPersonPerHour(),
        ];

        return $location->stocks
            ->filter(fn (InventoryStock $stock): bool => $stock->resource->is_consumable)
            ->map(function (InventoryStock $stock) use ($rates, $location): array {
                $rate = $rates[$stock->resource->name] ?? null;
                $hours = ($rate !== null && $rate > 0 && $location->occupants > 0)
                    ? $stock->quantity / ($rate * $location->occupants)
                    : null;

                return [
                    'resource_name' => $stock->resource->name,
                    'measurement_unit' => $stock->resource->measurement_unit,
                    'quantity' => $stock->quantity,
                    'status' => $stock->status,
                    'hours' => $hours,
                ];
            })
            ->values()
            ->all();
    }
}
