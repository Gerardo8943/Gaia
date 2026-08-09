<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location;
use App\Services\LifeSupport\LifeSupportOrchestrator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gaia:simulate-consumption {location} {hours}')]
#[Description('Proyecta el consumo de soporte vital en una ubicación durante un número de horas sin modificar datos')]
class SimulateConsumption extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(LifeSupportOrchestrator $orchestrator): int
    {
        $location = $this->findLocation($this->argument('location'));

        if ($location === null) {
            $this->error("Ubicacion '{$this->argument('location')}' no encontrada.");

            return self::FAILURE;
        }

        $hours = (float) $this->argument('hours');

        $rows = collect($orchestrator->project($location, $hours))
            ->map(fn (array $projection): array => [
                'Recurso' => $projection['resource_name'],
                'Actual' => $projection['quantity'],
                'Proyectado' => $projection['projected_quantity'],
                'Estado actual' => $projection['status'],
                'Estado proyectado' => $projection['projected_status'],
            ]);

        $this->info(sprintf(
            'Prevision de consumo para %s durante %.2f horas (no modifica datos):',
            $location->name,
            $hours,
        ));
        $this->table(['Recurso', 'Actual', 'Proyectado', 'Estado actual', 'Estado proyectado'], $rows);

        return self::SUCCESS;
    }

    /**
     * Resolve a location by id or name.
     */
    private function findLocation(int|string $value): ?Location
    {
        return Location::where('id', $value)->orWhere('name', $value)->first();
    }
}
