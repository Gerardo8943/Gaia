<?php

declare(strict_types=1);

namespace App\Services\LifeSupport;

use App\Models\Location;
use App\Services\Inventory\StockStatusService;

final class LifeSupportOrchestrator
{
    /** @var list<ResourceConsumptionService> */
    private array $services;

    public function __construct(
        private readonly OxygenConsumptionService $oxygen,
        private readonly WaterManagementService $water,
        private readonly StockStatusService $stockStatus,
    ) {
        $this->services = [$oxygen, $water];
    }

    /**
     * Proyecta el consumo de soporte vital en una ubicación durante un
     * número de horas, devolviendo los valores y estados resultantes sin
     * modificar la base de datos.
     *
     * @return list<array{
     *     resource_name: string,
     *     measurement_unit: string,
     *     consumed: float,
     *     quantity: float,
     *     projected_quantity: float,
     *     status: string,
     *     projected_status: string,
     * }>
     */
    public function project(Location $location, float $hours): array
    {
        $location->loadMissing('stocks.resource');

        return collect($this->services)
            ->map(function (ResourceConsumptionService $service) use ($location, $hours): ?array {
                $stock = $location->stocks
                    ->firstWhere('resource.name', $service->resourceName());

                if ($stock === null) {
                    return null;
                }

                $consumed = $service->consumed($location->occupants, $hours);
                $projected = max(0.0, $stock->quantity - $consumed);

                return [
                    'resource_name' => $stock->resource->name,
                    'measurement_unit' => $stock->resource->measurement_unit,
                    'consumed' => $consumed,
                    'quantity' => $stock->quantity,
                    'projected_quantity' => $projected,
                    'status' => $stock->status,
                    'projected_status' => $this->stockStatus->determineStatus(
                        $projected,
                        $stock->resource->critical_threshold,
                    ),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
