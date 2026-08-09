<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\SimulateConsumptionRequest;
use App\Models\Location;
use App\Services\LifeSupport\LifeSupportOrchestrator;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Show the inventory with its stock levels and statuses.
     */
    public function index(): Response
    {
        return Inertia::render('Inventory/Index', [
            'locations' => Location::query()
                ->with(['stocks.resource'])
                ->orderBy('name')
                ->get(),
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
}
