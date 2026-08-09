<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Collection;

final class TelemetryService
{
    /**
     * Global levels per tracked resource: total quantity, capacity and percentage.
     *
     * @return list<array{name: string, measurement_unit: string, quantity: float, capacity: float, percentage: float}>
     */
    public function globalLevels(): array
    {
        $capacities = (array) config('life-support.telemetry.capacities', []);

        $resources = Resource::query()
            ->whereIn('name', array_keys($capacities))
            ->withSum('stocks as total', 'quantity')
            ->get()
            ->groupBy('name');

        return collect($capacities)->map(function (int|float $capacity, string $name) use ($resources): array {
            $group = $resources->get($name, collect());

            return [
                'name' => $name,
                'measurement_unit' => (string) ($group->first()->measurement_unit ?? ''),
                'quantity' => (float) $group->sum('total'),
                'capacity' => (float) $capacity,
                'percentage' => $this->percentage((float) $group->sum('total'), (float) $capacity),
            ];
        })
            ->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * Stocks that require attention, critical ones first.
     *
     * @return Collection<int, InventoryStock>
     */
    public function criticalStocks(): Collection
    {
        return InventoryStock::query()
            ->where('status', '!=', InventoryStock::STATUS_OPTIMAL)
            ->with(['location', 'resource'])
            ->orderByRaw("CASE status WHEN 'Critico' THEN 0 WHEN 'Bajo' THEN 1 ELSE 2 END")
            ->orderBy('quantity')
            ->get();
    }

    /**
     * All locations with their stocks and resources, for tank bars and forms.
     *
     * @return Collection<int, Location>
     */
    public function locations(): Collection
    {
        return Location::query()
            ->with(['stocks.resource'])
            ->orderBy('name')
            ->get();
    }

    private function percentage(float $quantity, float $capacity): float
    {
        if ($capacity <= 0) {
            return 0.0;
        }

        return round(min(100.0, ($quantity / $capacity) * 100), 1);
    }
}
