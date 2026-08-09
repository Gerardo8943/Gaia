<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryStock;

final class StockStatusService
{
    public function determineStatus(float $quantity, float $criticalThreshold): string
    {
        if ($quantity <= $criticalThreshold * (float) config('life-support.status.critical')) {
            return InventoryStock::STATUS_CRITICAL;
        }

        if ($quantity <= $criticalThreshold * (float) config('life-support.status.low')) {
            return InventoryStock::STATUS_LOW;
        }

        return InventoryStock::STATUS_OPTIMAL;
    }
}
