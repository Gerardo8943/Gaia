<?php

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use App\Models\User;

test('authenticated users can view the inventory page', function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->actingAs(User::factory()->create());

    Location::factory()->create();

    $this->get(route('inventory.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/Index')
            ->has('locations', 1));
});

test('consumption degrades oxygen based on occupants and hours', function () {
    config(['life-support.consumption.oxygen' => 0.5]);

    $this->actingAs(User::factory()->create());

    $location = Location::factory()->withOccupants(4)->create();
    $oxygen = Resource::factory()->create([
        'name' => 'Oxigeno Liquido',
        'critical_threshold' => 100,
    ]);
    $stock = InventoryStock::factory()->withQuantity(100)->create([
        'location_id' => $location,
        'resource_id' => $oxygen,
    ]);

    $this->post(route('inventory.consume', $location), ['hours' => 2]);

    expect($stock->fresh()->quantity)->toBe(96.0);
});

test('stock quantity never drops below zero', function () {
    config(['life-support.consumption.oxygen' => 1.0]);

    $this->actingAs(User::factory()->create());

    $location = Location::factory()->withOccupants(10)->create();
    $oxygen = Resource::factory()->create([
        'name' => 'Oxigeno Liquido',
        'critical_threshold' => 1,
    ]);
    InventoryStock::factory()->withQuantity(5)->create([
        'location_id' => $location,
        'resource_id' => $oxygen,
    ]);

    $this->post(route('inventory.consume', $location), ['hours' => 48]);

    expect(InventoryStock::first()->quantity)->toBe(0.0);
});

test('stock status becomes critical when below the threshold', function () {
    $this->actingAs(User::factory()->create());

    $location = Location::factory()->withOccupants(1)->create();
    $oxygen = Resource::factory()->create([
        'name' => 'Oxigeno Liquido',
        'critical_threshold' => 100,
    ]);
    InventoryStock::factory()->withQuantity(100)->create([
        'location_id' => $location,
        'resource_id' => $oxygen,
    ]);

    $this->post(route('inventory.consume', $location), ['hours' => 1]);

    expect(InventoryStock::first()->status)->toBe(InventoryStock::STATUS_CRITICAL);
});

test('consumption requires a valid number of hours', function () {
    $this->actingAs(User::factory()->create());

    $location = Location::factory()->create();

    $this->post(route('inventory.consume', $location), ['hours' => 'abc'])
        ->assertSessionHasErrors('hours');
});
