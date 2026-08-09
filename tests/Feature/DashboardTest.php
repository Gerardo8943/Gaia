<?php

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use App\Models\User;

test('authenticated users can view the dashboard', function () {
    $this->actingAs(User::factory()->create());

    Location::factory()->create();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('kpis'));
});

test('dashboard shows occupants and autonomy for a stocked location', function () {
    config(['life-support.consumption.oxygen' => 0.5]);

    $this->actingAs(User::factory()->create());

    $location = Location::factory()->withOccupants(4)->create();
    $oxygen = Resource::factory()->create([
        'name' => 'Oxigeno Liquido',
        'critical_threshold' => 100,
    ]);
    InventoryStock::factory()->withQuantity(100)->create([
        'location_id' => $location,
        'resource_id' => $oxygen,
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('kpis.occupants', 4)
            ->where('kpis.locations', 1)
            ->has('autonomy', 1)
            ->where('autonomy.0.location.name', $location->name)
            ->where('autonomy.0.stocks.0.resource_name', 'Oxigeno Liquido'));
});
