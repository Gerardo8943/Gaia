<?php

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use App\Models\User;

test('inventory page exposes global telemetry levels and critical stocks', function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->actingAs(User::factory()->create());

    $location = Location::factory()->create();
    $oxygen = Resource::factory()->create(['name' => 'Oxigeno Liquido', 'critical_threshold' => 1000]);
    $water = Resource::factory()->create(['name' => 'Agua almacenada', 'critical_threshold' => 500]);
    InventoryStock::factory()->withQuantity(2500)->create(['location_id' => $location, 'resource_id' => $oxygen]);
    InventoryStock::factory()->withQuantity(1000)->create(['location_id' => $location, 'resource_id' => $water]);

    $this->get(route('inventory.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/Index')
            ->has('telemetry', 2)
            ->where('telemetry.0.name', 'Agua almacenada')
            ->where('telemetry.0.percentage', 20)
            ->where('telemetry.1.name', 'Oxigeno Liquido')
            ->where('telemetry.1.percentage', 25)
            ->has('criticalStocks', 0)
            ->has('locations', 1));
});

test('critical stocks list only stocks below optimal status', function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->actingAs(User::factory()->create());

    $location = Location::factory()->create();
    $oxygen = Resource::factory()->create(['name' => 'Oxigeno Liquido', 'critical_threshold' => 1000]);
    InventoryStock::factory()
        ->withQuantity(500)
        ->withStatus(InventoryStock::STATUS_CRITICAL)
        ->create(['location_id' => $location, 'resource_id' => $oxygen]);

    $this->get(route('inventory.index'))
        ->assertInertia(fn ($page) => $page
            ->has('criticalStocks', 1)
            ->where('criticalStocks.0.status', InventoryStock::STATUS_CRITICAL)
            ->where('criticalStocks.0.location.name', $location->name));
});
