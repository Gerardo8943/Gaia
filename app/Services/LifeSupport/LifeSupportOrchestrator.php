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
     * Simula el consumo de soporte vital en una ubicación durante un
     * número de horas, degradando el stock y recalculando su estado.
     */
    public function simulate(Location $location, float $hours): void
    {
        $location->loadMissing('stocks.resource');

        foreach ($this->services as $service) {
            $stock = $location->stocks
                ->firstWhere('resource.name', $service->resourceName());

            if ($stock === null) {
                continue;
            }

            $stock->quantity = max(0.0, $stock->quantity - $service->consumed($location->occupants, $hours));
            $stock->status = $this->stockStatus->determineStatus($stock->quantity, $stock->resource->critical_threshold);
            $stock->save();
        }
    }
}
