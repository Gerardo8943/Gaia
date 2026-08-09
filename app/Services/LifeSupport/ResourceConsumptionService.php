<?php

declare(strict_types=1);

namespace App\Services\LifeSupport;

abstract class ResourceConsumptionService
{
    /**
     * Nombre exacto del recurso que consume (columna resources.name).
     */
    abstract public function resourceName(): string;

    /**
     * Unidades consumidas por persona por hora.
     */
    abstract public function ratePerPersonPerHour(): float;

    /**
     * Unidades consumidas por los ocupantes durante las horas dadas.
     */
    public function consumed(int $occupants, float $hours): float
    {
        return $this->ratePerPersonPerHour() * $occupants * $hours;
    }
}
