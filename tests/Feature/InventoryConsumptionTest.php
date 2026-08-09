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

test('preview projects consumption without modifying the stock', function () {
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

    $this->get(route('inventory.preview', $location).'?hours=2')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/Index')
            ->where('projection.hours', 2)
            ->where('projection.stocks.0.projected_quantity', 96));

    expect($stock->fresh()->quantity)->toBe(100.0);
});

test('preview never projects below zero', function () {
    config(['life-support.consumption.oxygen' => 1.0]);

    $this->actingAs(User::factory()->create());

    $location = Location::factory()->withOccupants(10)->create();
    $oxygen = Resource::factory()->create([
        'name' => 'Oxigeno Liquido',
        'critical_threshold' => 1,
    ]);
    $stock = InventoryStock::factory()->withQuantity(5)->create([
        'location_id' => $location,
        'resource_id' => $oxygen,
    ]);

    $this->get(route('inventory.preview', $location).'?hours=48')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('projection.stocks.0.projected_quantity', 0));

    expect($stock->fresh()->quantity)->toBe(5.0);
});

test('preview shows a critical projected status without persisting it', function () {
    $this->actingAs(User::factory()->create());

    $location = Location::factory()->withOccupants(1)->create();
    $oxygen = Resource::factory()->create([
        'name' => 'Oxigeno Liquido',
        'critical_threshold' => 100,
    ]);
    $stock = InventoryStock::factory()->withQuantity(100)->create([
        'location_id' => $location,
        'resource_id' => $oxygen,
    ]);

    $this->get(route('inventory.preview', $location).'?hours=1')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('projection.stocks.0.projected_status', InventoryStock::STATUS_CRITICAL));

    expect($stock->fresh()->status)->toBe(InventoryStock::STATUS_OPTIMAL);
});

test('preview requires a valid number of hours', function () {
    $this->actingAs(User::factory()->create());

    $location = Location::factory()->create();

    $this->get(route('inventory.preview', $location).'?hours=abc')
        ->assertSessionHasErrors('hours');
});
