<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\SimulateConsumptionRequest;
use App\Http\Requests\Inventory\TransferStockRequest;
use App\Models\Location;
use App\Models\Resource;
use App\Services\Inventory\StockTransferService;
use App\Services\Inventory\TelemetryService;
use App\Services\LifeSupport\LifeSupportOrchestrator;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Show the inventory HUD with global levels, critical alerts and locations.
     */
    public function index(TelemetryService $telemetry): Response
    {
        return Inertia::render('Inventory/Index', [
            'telemetry' => $telemetry->globalLevels(),
            'criticalStocks' => $telemetry->criticalStocks(),
            'locations' => $telemetry->locations(),
        ]);
    }

    /**
     * Simulate life-support consumption at the given location.
     */
    public function consume(
        SimulateConsumptionRequest $request,
        Location $location,
        LifeSupportOrchestrator $orchestrator,
    ): RedirectResponse {
        $orchestrator->simulate($location, (float) $request->validated('hours'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Consumo simulado correctamente.'),
        ]);

        return to_route('inventory.index');
    }

    /**
     * Transfer a quantity of a resource between two locations.
     */
    public function transfer(
        TransferStockRequest $request,
        StockTransferService $transferService,
    ): RedirectResponse {
        $validated = $request->validated();

        $from = Location::findOrFail((int) $validated['from_location_id']);
        $to = Location::findOrFail((int) $validated['to_location_id']);
        $resource = Resource::findOrFail((int) $validated['resource_id']);

        try {
            $transferService->transfer($from, $to, $resource, (float) $validated['quantity']);
        } catch (DomainException $exception) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);

            return to_route('inventory.index');
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Transferencia realizada correctamente.'),
        ]);

        return to_route('inventory.index');
    }
}
