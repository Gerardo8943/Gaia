<?php

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use App\Models\TransferLog;

test('simulate consumption command prints a projection without modifying stock', function () {
    config(['life-support.consumption.oxygen' => 0.5]);

    $location = Location::factory()->withOccupants(4)->create();
    $oxygen = Resource::factory()->create(['name' => 'Oxigeno Liquido', 'critical_threshold' => 100]);
    $stock = InventoryStock::factory()->withQuantity(100)->create(['location_id' => $location, 'resource_id' => $oxygen]);

    $this->artisan('gaia:simulate-consumption', ['location' => $location->id, 'hours' => 2])
        ->expectsOutputToContain('96')
        ->assertExitCode(0);

    expect($stock->fresh()->quantity)->toBe(100.0);
});

test('simulate consumption command fails when the location is unknown', function () {
    $this->artisan('gaia:simulate-consumption', ['location' => 'No existe', 'hours' => 1])
        ->assertExitCode(1);
});

test('transfer command moves stock between locations and logs the transfer', function () {
    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create(['name' => 'Oxigeno Liquido']);
    $stock = InventoryStock::factory()->withQuantity(100)->create(['location_id' => $from, 'resource_id' => $resource]);

    $this->artisan('gaia:transfer', [
        'from' => $from->name,
        'to' => $to->name,
        'resource' => $resource->name,
        'quantity' => 40,
    ])->assertExitCode(0);

    expect($from->stocks()->first()->quantity)->toBe(60.0);
    expect($to->stocks()->first()->quantity)->toBe(40.0);

    $log = TransferLog::first();
    expect($log)->not->toBeNull()
        ->and($log->from_location_id)->toBe($from->id)
        ->and($log->to_location_id)->toBe($to->id)
        ->and($log->resource_id)->toBe($resource->id)
        ->and($log->quantity)->toBe(40.0)
        ->and($log->user_id)->toBeNull();
});

test('transfer command fails when there is not enough stock and logs nothing', function () {
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

    expect(TransferLog::count())->toBe(0);
    expect($from->stocks()->first()->quantity)->toBe(10.0);
});
