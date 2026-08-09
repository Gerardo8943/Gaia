<?php

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use App\Models\User;
use App\Services\Inventory\StockTransferService;
use DomainException;

test('authenticated users can transfer stock between locations', function () {
    $this->actingAs(User::factory()->create());

    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create(['critical_threshold' => 10]);
    InventoryStock::factory()->withQuantity(100)->create(['location_id' => $from, 'resource_id' => $resource]);

    $this->post(route('inventory.transfer'), [
        'from_location_id' => $from->id,
        'to_location_id' => $to->id,
        'resource_id' => $resource->id,
        'quantity' => 30,
    ])->assertRedirect(route('inventory.index'));

    expect($from->stocks()->where('resource_id', $resource->id)->first()->quantity)->toBe(70.0);
    expect($to->stocks()->where('resource_id', $resource->id)->first()->quantity)->toBe(30.0);
});

test('transfer creates the destination stock when it does not exist', function () {
    $this->actingAs(User::factory()->create());

    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create();
    InventoryStock::factory()->withQuantity(50)->create(['location_id' => $from, 'resource_id' => $resource]);

    expect($to->stocks()->count())->toBe(0);

    $this->post(route('inventory.transfer'), [
        'from_location_id' => $from->id,
        'to_location_id' => $to->id,
        'resource_id' => $resource->id,
        'quantity' => 20,
    ]);

    $destination = $to->stocks()->where('resource_id', $resource->id)->first();

    expect($destination)->not->toBeNull()
        ->and($destination->quantity)->toBe(20.0);
});

test('transfer rejects insufficient stock with an error toast', function () {
    $this->actingAs(User::factory()->create());

    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create();
    InventoryStock::factory()->withQuantity(5)->create(['location_id' => $from, 'resource_id' => $resource]);

    $this->post(route('inventory.transfer'), [
        'from_location_id' => $from->id,
        'to_location_id' => $to->id,
        'resource_id' => $resource->id,
        'quantity' => 50,
    ])
        ->assertRedirect(route('inventory.index'))
        ->assertSessionHas('inertia.flash_data.toast.type', 'error');

    expect($from->stocks()->where('resource_id', $resource->id)->first()->quantity)->toBe(5.0);
});

test('transfer recalculates the status of the affected stocks', function () {
    $this->actingAs(User::factory()->create());

    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create(['critical_threshold' => 100]);
    InventoryStock::factory()->withQuantity(100)->create(['location_id' => $from, 'resource_id' => $resource]);

    $this->post(route('inventory.transfer'), [
        'from_location_id' => $from->id,
        'to_location_id' => $to->id,
        'resource_id' => $resource->id,
        'quantity' => 99,
    ]);

    expect($from->stocks()->where('resource_id', $resource->id)->first()->status)->toBe(InventoryStock::STATUS_CRITICAL);
});

test('transfer service rejects a non-positive quantity', function () {
    $service = app(StockTransferService::class);
    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create();
    InventoryStock::factory()->withQuantity(10)->create(['location_id' => $from, 'resource_id' => $resource]);

    $service->transfer($from, $to, $resource, -5);
})->throws(DomainException::class);

test('transfer service rejects moving stock to the same location', function () {
    $service = app(StockTransferService::class);
    $from = Location::factory()->create();
    $resource = Resource::factory()->create();
    InventoryStock::factory()->withQuantity(10)->create(['location_id' => $from, 'resource_id' => $resource]);

    $service->transfer($from, $from, $resource, 5);
})->throws(DomainException::class);
