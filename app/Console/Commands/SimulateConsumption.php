<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location;
use App\Services\LifeSupport\LifeSupportOrchestrator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gaia:simulate-consumption {location} {hours}')]
#[Description('Simula el consumo de soporte vital en una ubicación durante un número de horas')]
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

        $before = $location->stocks()
            ->with('resource')
            ->get()
            ->mapWithKeys(fn ($stock) => [$stock->resource->name => $stock->quantity]);

        $orchestrator->simulate($location, $hours);

        $rows = $location->stocks()
            ->with('resource')
            ->get()
            ->map(fn ($stock) => [
                'Recurso' => $stock->resource->name,
                'Antes' => $before[$stock->resource->name] ?? '-',
                'Despues' => $stock->quantity,
                'Estado' => $stock->status,
            ]);

        $this->info(sprintf('Consumo simulado para %s durante %.2f horas:', $location->name, $hours));
        $this->table(['Recurso', 'Antes', 'Despues', 'Estado'], $rows);

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
