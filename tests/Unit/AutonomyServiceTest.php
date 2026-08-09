<?php

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use App\Services\Inventory\AutonomyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('autonomy is quantity divided by the per person per hour rate', function () {
    config(['life-support.consumption.oxygen' => 0.5]);

    $location = Location::factory()->withOccupants(4)->create();
    $oxygen = Resource::factory()->create(['name' => 'Oxigeno Liquido']);
    InventoryStock::factory()->withQuantity(100)->create([
        'location_id' => $location,
        'resource_id' => $oxygen,
    ]);

    $stocks = app(AutonomyService::class)->forLocation($location);

    expect($stocks)->toHaveCount(1);
    expect($stocks[0]['hours'])->toBe(50.0);
});

test('autonomy is null when the location has no occupants', function () {
    $location = Location::factory()->withOccupants(0)->create();
    $oxygen = Resource::factory()->create(['name' => 'Oxigeno Liquido']);
    InventoryStock::factory()->withQuantity(100)->create([
        'location_id' => $location,
        'resource_id' => $oxygen,
    ]);

    $stocks = app(AutonomyService::class)->forLocation($location);

    expect($stocks[0]['hours'])->toBeNull();
});

test('autonomy ignores non consumable resources', function () {
    $location = Location::factory()->withOccupants(2)->create();
    $suit = Resource::factory()->nonConsumable()->create(['name' => 'Traje Espacial Eva']);
    InventoryStock::factory()->withQuantity(10)->create([
        'location_id' => $location,
        'resource_id' => $suit,
    ]);

    $stocks = app(AutonomyService::class)->forLocation($location);

    expect($stocks)->toBeEmpty();
});
