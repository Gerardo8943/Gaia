<?php

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use App\Models\TransferLog;
use App\Models\User;

test('transfer through the web records a transfer log with the acting user', function () {
    $user = User::factory()->create();
    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create(['name' => 'Oxigeno Liquido']);
    InventoryStock::factory()->withQuantity(100)->create([
        'location_id' => $from,
        'resource_id' => $resource,
    ]);

    $this->actingAs($user)
        ->post(route('inventory.transfer'), [
            'from_location_id' => $from->id,
            'to_location_id' => $to->id,
            'resource_id' => $resource->id,
            'quantity' => 40,
        ])
        ->assertRedirect(route('inventory.index'));

    $log = TransferLog::first();
    expect($log)->not->toBeNull()
        ->and($log->from_location_id)->toBe($from->id)
        ->and($log->to_location_id)->toBe($to->id)
        ->and($log->quantity)->toBe(40.0)
        ->and($log->user_id)->toBe($user->id);
});

test('transfer with insufficient stock records no log', function () {
    $this->actingAs(User::factory()->create());

    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create(['name' => 'Oxigeno Liquido']);
    InventoryStock::factory()->withQuantity(10)->create([
        'location_id' => $from,
        'resource_id' => $resource,
    ]);

    $this->post(route('inventory.transfer'), [
        'from_location_id' => $from->id,
        'to_location_id' => $to->id,
        'resource_id' => $resource->id,
        'quantity' => 50,
    ])->assertRedirect(route('inventory.index'));

    expect(TransferLog::count())->toBe(0);
});

test('history page shows the latest transfer logs', function () {
    $user = User::factory()->create();
    $from = Location::factory()->create();
    $to = Location::factory()->create();
    $resource = Resource::factory()->create(['name' => 'Oxigeno Liquido']);
    $log = TransferLog::factory()->create([
        'from_location_id' => $from,
        'to_location_id' => $to,
        'resource_id' => $resource,
        'quantity' => 25,
        'user_id' => $user,
    ]);

    $this->actingAs($user)
        ->get(route('inventory.history'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/History')
            ->has('logs.data', 1)
            ->where('logs.data.0.id', $log->id)
            ->where('logs.data.0.quantity', 25));
});

test('history page is empty when there are no transfers', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('inventory.history'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Inventory/History')
            ->where('logs.total', 0));
});
