<?php

namespace Database\Seeders;

use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Resource;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LunaBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    // Crear ubicaciones
    $moduloMando = Location::create([
        'name' => 'Modulo de Mando',
        'type' => 'Habitable',
        'is_pressurized' => true,
    ]);

    $rover = Location::create([
        'name' => 'Rover de exploracion',
        'type' => 'Vehiculo',
        'is_pressurized' => false,
    ]);

    $exterior = Location::create([
        'name' => 'Almacen de Superficie',
        'type' => 'Exterior',
        'is_pressurized' => false,
    ]);

    //Crear recursos
    $oxigeno = Resource::create([
        'name' => 'Oxigeno Liquido',
        'measurement_unit' => 'Litros',
        'is_consumable' => true,
        'critical_threshold' => 1000.00,
    ]);

    $agua = Resource::create([
        'name' => 'Agua almacenada',
        'measurement_unit' => 'Litros',
        'is_consumable' => true,
        'critical_threshold' => 500.00,
    ]);

    $traje = Resource::create([
        'name' => 'Traje Espacial Eva',
        'measurement_unit' => 'Unidades',
        'is_consumable' => false,
        'critical_threshold' => 5.00,
    ]);

    //Asignar recursos a ubicaciones
    InventoryStock::create([
        'location_id' => $moduloMando->id,
        'resource_id' => $oxigeno->id,
        'quantity' => 5000,
        'status'=>'Optimo',
    ]);

    InventoryStock::create([
        'location_id' => $moduloMando->id,
        'resource_id' => $agua->id,
        'quantity' => 2000,
        'status'=>'Optimo',
    ]);
    
    InventoryStock::create([
    'location_id' => $rover->id,
    'resource_id' => $oxigeno->id,
    'quantity' => 200,
    'status'=>'Optimo',
    ]);

    InventoryStock::create([
        'location_id' => $exterior->id,
        'resource_id' => $traje->id,
        'quantity' => 10,
        'status'=>'Optimo',
    ]);
        
    }
}
