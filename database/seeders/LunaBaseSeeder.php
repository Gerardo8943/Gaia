<?php

namespace Database\Seeders;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use Illuminate\Database\Seeder;

class LunaBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear ubicaciones (idempotente por nombre)
        $moduloMando = Location::updateOrCreate(
            ['name' => 'Modulo de Mando'],
            [
                'type' => 'Habitable',
                'is_pressurized' => true,
                'occupants' => 4,
            ],
        );

        $rover = Location::updateOrCreate(
            ['name' => 'Rover de exploracion'],
            [
                'type' => 'Vehiculo',
                'is_pressurized' => false,
                'occupants' => 1,
            ],
        );

        $exterior = Location::updateOrCreate(
            ['name' => 'Almacen de Superficie'],
            [
                'type' => 'Exterior',
                'is_pressurized' => false,
                'occupants' => 0,
            ],
        );

        // Crear recursos (idempotente por nombre)
        $oxigeno = Resource::updateOrCreate(
            ['name' => 'Oxigeno Liquido'],
            [
                'measurement_unit' => 'Litros',
                'is_consumable' => true,
                'critical_threshold' => 1000.00,
            ],
        );

        $agua = Resource::updateOrCreate(
            ['name' => 'Agua almacenada'],
            [
                'measurement_unit' => 'Litros',
                'is_consumable' => true,
                'critical_threshold' => 500.00,
            ],
        );

        $comida = Resource::updateOrCreate(
            ['name' => 'Raciones de Comida'],
            [
                'measurement_unit' => 'Paquetes',
                'is_consumable' => true,
                'critical_threshold' => 200.00,
            ],
        );

        $energia = Resource::updateOrCreate(
            ['name' => 'Energia de Baterias'],
            [
                'measurement_unit' => 'kWh',
                'is_consumable' => true,
                'critical_threshold' => 300.00,
            ],
        );

        $traje = Resource::updateOrCreate(
            ['name' => 'Traje Espacial Eva'],
            [
                'measurement_unit' => 'Unidades',
                'is_consumable' => false,
                'critical_threshold' => 5.00,
            ],
        );

        // Asignar recursos a ubicaciones (idempotente por ubicación + recurso)
        InventoryStock::updateOrCreate(
            ['location_id' => $moduloMando->id, 'resource_id' => $oxigeno->id],
            ['quantity' => 5000, 'status' => 'Optimo'],
        );

        InventoryStock::updateOrCreate(
            ['location_id' => $moduloMando->id, 'resource_id' => $agua->id],
            ['quantity' => 2000, 'status' => 'Optimo'],
        );

        InventoryStock::updateOrCreate(
            ['location_id' => $moduloMando->id, 'resource_id' => $comida->id],
            ['quantity' => 800, 'status' => 'Optimo'],
        );

        InventoryStock::updateOrCreate(
            ['location_id' => $moduloMando->id, 'resource_id' => $energia->id],
            ['quantity' => 1500, 'status' => 'Optimo'],
        );

        InventoryStock::updateOrCreate(
            ['location_id' => $rover->id, 'resource_id' => $oxigeno->id],
            ['quantity' => 200, 'status' => 'Optimo'],
        );

        InventoryStock::updateOrCreate(
            ['location_id' => $rover->id, 'resource_id' => $comida->id],
            ['quantity' => 50, 'status' => 'Optimo'],
        );

        InventoryStock::updateOrCreate(
            ['location_id' => $exterior->id, 'resource_id' => $traje->id],
            ['quantity' => 10, 'status' => 'Optimo'],
        );
    }
}
