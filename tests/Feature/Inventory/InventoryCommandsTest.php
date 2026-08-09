<?php

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;

test('simulate consumption command degrades stock by occupants and hours', function () {
    config(['life-support.consumption.oxygen' => 0.5]);

    $location = Location::factory()->withOccupants(4)->create();
    $oxygen = Resource::factory()->create(['name' => 'Oxigeno Liquido', 'critical_threshold' => 100]);
    InventoryStock::factory()->withQuantity(100)->create(['location_id' => $location, 'resource_id' => $oxygen]);

    $this->artisan('gaia:simulate-consumption', ['location' => $location->id, 'hours' => 2])
        ->assertExitCode(0);

    expect(InventoryStock::first()->quantity)->toBe(96.0);
});

test('simulate consumption command fails when the location is unknown', function () {
    $this->artisan('gaia:simulate-consumption', ['location' => 'No existe', 'hours' => 1])
        ->assertExitCode(1);
});

test('transfer command moves stock between locations', function () {
    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create(['name' => 'Oxigeno Liquido']);
    InventoryStock::factory()->withQuantity(100)->create(['location_id' => $from, 'resource_id' => $resource]);

    $this->artisan('gaia:transfer', [
        'from' => $from->name,
        'to' => $to->name,
        'resource' => $resource->name,
        'quantity' => 40,
    ])->assertExitCode(0);

    expect($from->stocks()->first()->quantity)->toBe(60.0);
    expect($to->stocks()->first()->quantity)->toBe(40.0);
});

test('transfer command fails when there is not enough stock', function () {
    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create(['name' => 'Oxigeno Liquido']);
    InventoryStock::factory()->withQuantity(10)->create(['location_id' => $from, 'resource_id' => $resource]);

    $this->artisan('gaia:transfer', [
        'from' => $from->name,
        'to' => $to->name,
        'resource' => $resource->name,
        'quantity' => 50,
    ])->assertExitCode(1);
});
