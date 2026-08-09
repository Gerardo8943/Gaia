<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use App\Models\TransferLog;
use DomainException;
use Illuminate\Support\Facades\DB;

final class StockTransferService
{
    public function __construct(
        private readonly StockStatusService $stockStatus,
    ) {}

    /**
     * Move a quantity of a resource from one location to another,
     * recalculating the status of both affected stocks.
     *
     * @throws DomainException when the quantity is invalid or the source lacks stock
     */
    public function transfer(Location $from, Location $to, Resource $resource, float $quantity): void
    {
        if ($quantity <= 0) {
            throw new DomainException('La cantidad a transferir debe ser mayor que cero.');
        }

        if ($from->is($to)) {
            throw new DomainException('El origen y el destino deben ser ubicaciones distintas.');
        }

        DB::transaction(function () use ($from, $to, $resource, $quantity) {
            $source = $from->stocks()
                ->where('resource_id', $resource->id)
                ->lockForUpdate()
                ->first();

            if ($source === null || $source->quantity < $quantity) {
                throw new DomainException(sprintf("Stock insuficiente de '%s' en %s.", $resource->name, $from->name));
            }

            $destination = $to->stocks()->firstOrCreate(
                ['resource_id' => $resource->id],
                ['quantity' => 0, 'status' => InventoryStock::STATUS_OPTIMAL],
            );

            $source->quantity -= $quantity;
            $source->status = $this->stockStatus->determineStatus($source->quantity, $resource->critical_threshold);
            $source->save();

            $destination->quantity += $quantity;
            $destination->status = $this->stockStatus->determineStatus($destination->quantity, $resource->critical_threshold);
            $destination->save();

            TransferLog::create([
                'from_location_id' => $from->id,
                'to_location_id' => $to->id,
                'resource_id' => $resource->id,
                'quantity' => $quantity,
                'user_id' => auth()->id(),
            ]);
        });
    }
}
