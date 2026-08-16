<?php

declare(strict_types=1);

namespace App\Services\LifeSupport;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Services\Inventory\StockStatusService;

final class LifeSupportOrchestrator
{
    /** @var list<ResourceConsumptionService> */
    private array $services;

    public function __construct(
        private readonly OxygenConsumptionService $oxygen,
        private readonly WaterManagementService $water,
        private readonly FoodConsumptionService $food,
        private readonly EnergyConsumptionService $energy,
        private readonly StockStatusService $stockStatus,
    ) {
        $this->services = [$oxygen, $water, $food, $energy];
    }

    /**
     * Proyecta el consumo de soporte vital en una ubicación durante un
     * número de horas, devolviendo los valores y estados resultantes sin
     * modificar la base de datos.
     *
     * @return array<int, array{
     *     resource_name: string,
     *     measurement_unit: string,
     *     consumed: float,
     *     quantity: float,
     *     projected_quantity: float,
     *     status: string,
     *     projected_status: string,
     *     projected_hours_left: float|null,
     * }>
     */
    public function project(Location $location, float $hours): array
    {
        $location->loadMissing('stocks.resource');

        $registeredServices = collect($this->services)
            ->keyBy(fn (ResourceConsumptionService $service): string => $service->resourceName());

        return $location->stocks
            ->filter(fn (InventoryStock $stock): bool => $stock->resource->is_consumable)
            ->map(function (InventoryStock $stock) use ($registeredServices, $location, $hours): ?array {
                $service = $registeredServices->get($stock->resource->name);

                $rate = $service?->ratePerPersonPerHour() ?? 0.0;
                $consumed = $rate * $location->occupants * $hours;
                $projected = max(0.0, (float) $stock->quantity - $consumed);

                $hourlyRate = $rate * $location->occupants;
                $projectedHoursLeft = ($hourlyRate > 0)
                    ? ($projected / $hourlyRate)
                    : null;

                return [
                    'resource_name' => $stock->resource->name,
                    'measurement_unit' => $stock->resource->measurement_unit,
                    'consumed' => round($consumed, 2),
                    'quantity' => (float) $stock->quantity,
                    'projected_quantity' => round($projected, 2),
                    'status' => $stock->status,
                    'projected_status' => $this->stockStatus->determineStatus(
                        $projected,
                        (float) $stock->resource->critical_threshold,
                    ),
                    'projected_hours_left' => $projectedHoursLeft !== null ? round($projectedHoursLeft, 1) : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
