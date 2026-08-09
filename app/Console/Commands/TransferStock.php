<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\Resource;
use App\Services\Inventory\StockTransferService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gaia:transfer {from} {to} {resource} {quantity}')]
#[Description('Transfiere una cantidad de un recurso entre dos ubicaciones')]
class TransferStock extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(StockTransferService $transferService): int
    {
        $from = $this->findLocation($this->argument('from'));

        if ($from === null) {
            $this->error("Ubicacion de origen '{$this->argument('from')}' no encontrada.");

            return self::FAILURE;
        }

        $to = $this->findLocation($this->argument('to'));

        if ($to === null) {
            $this->error("Ubicacion de destino '{$this->argument('to')}' no encontrada.");

            return self::FAILURE;
        }

        $resource = $this->findResource($this->argument('resource'));

        if ($resource === null) {
            $this->error("Recurso '{$this->argument('resource')}' no encontrado.");

            return self::FAILURE;
        }

        try {
            $transferService->transfer($from, $to, $resource, (float) $this->argument('quantity'));
        } catch (\DomainException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Transferidos %.2f %s de %s a %s.',
            (float) $this->argument('quantity'),
            $resource->measurement_unit,
            $from->name,
            $to->name,
        ));

        return self::SUCCESS;
    }

    /**
     * Resolve a location by id or name.
     */
    private function findLocation(int|string $value): ?Location
    {
        return Location::where('id', $value)->orWhere('name', $value)->first();
    }

    /**
     * Resolve a resource by id or name.
     */
    private function findResource(int|string $value): ?Resource
    {
        return Resource::where('id', $value)->orWhere('name', $value)->first();
    }
}
