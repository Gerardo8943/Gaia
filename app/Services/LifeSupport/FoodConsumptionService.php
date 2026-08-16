<?php

declare(strict_types=1);

namespace App\Services\LifeSupport;

final class FoodConsumptionService extends ResourceConsumptionService
{
    public function resourceName(): string
    {
        return 'Raciones de Comida';
    }

    public function ratePerPersonPerHour(): float
    {
        return (float) config('life-support.consumption.food', 0.12);
    }
}
