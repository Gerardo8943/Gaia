<?php

declare(strict_types=1);

namespace App\Services\LifeSupport;

final class EnergyConsumptionService extends ResourceConsumptionService
{
    public function resourceName(): string
    {
        return 'Energia de Baterias';
    }

    public function ratePerPersonPerHour(): float
    {
        return (float) config('life-support.consumption.energy', 0.50);
    }
}
