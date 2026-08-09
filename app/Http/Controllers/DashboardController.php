<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\Inventory\AutonomyService;
use App\Services\Inventory\TelemetryService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the mission command overview: KPIs, global levels, autonomy and alerts.
     */
    public function index(TelemetryService $telemetry, AutonomyService $autonomy): Response
    {
        $locations = Location::with('stocks.resource')->orderBy('name')->get();

        $globalLevels = $telemetry->globalLevels();
        $criticalStocks = $telemetry->criticalStocks();

        $perLocation = $locations
            ->map(fn (Location $location): array => [
                'location' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'type' => $location->type,
                    'occupants' => $location->occupants,
                ],
                'stocks' => $autonomy->forLocation($location),
            ])
            ->values();

        $minAutonomy = $perLocation
            ->flatMap(fn (array $entry): array => $entry['stocks'])
            ->pluck('hours')
            ->filter()
            ->min();

        return Inertia::render('Dashboard', [
            'kpis' => [
                'occupants' => $locations->sum('occupants'),
                'locations' => $locations->count(),
                'critical_stocks' => $criticalStocks->count(),
                'oxygen_percentage' => $this->percentageFor($globalLevels, 'Oxigeno Liquido'),
                'water_percentage' => $this->percentageFor($globalLevels, 'Agua almacenada'),
                'min_autonomy_hours' => $minAutonomy,
            ],
            'telemetry' => $globalLevels,
            'criticalStocks' => $criticalStocks,
            'autonomy' => $perLocation,
        ]);
    }

    /**
     * Find the global percentage for a named resource.
     */
    private function percentageFor(array $globalLevels, string $name): float
    {
        foreach ($globalLevels as $level) {
            if ($level['name'] === $name) {
                return (float) $level['percentage'];
            }
        }

        return 0.0;
    }
}
