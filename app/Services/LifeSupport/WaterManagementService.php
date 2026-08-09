<?php

declare(strict_types=1);

namespace App\Services\LifeSupport;

final class WaterManagementService extends ResourceConsumptionService
{
    public function resourceName(): string
    {
        return 'Agua almacenada';
    }

    public function ratePerPersonPerHour(): float
    {
        return (float) config('life-support.consumption.water');
    }
}
