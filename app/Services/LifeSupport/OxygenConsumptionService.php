<?php

declare(strict_types=1);

namespace App\Services\LifeSupport;

final class OxygenConsumptionService extends ResourceConsumptionService
{
    public function resourceName(): string
    {
        return 'Oxigeno Liquido';
    }

    public function ratePerPersonPerHour(): float
    {
        return (float) config('life-support.consumption.oxygen');
    }
}
