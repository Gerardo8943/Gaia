<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del recurso, por ejemplo: Oxigeno, Filtro CO2, etc
            $table->string('measurement_unit'); // Unidad de medida, por ejemplo: Litros, Kilogramos, etc
            $table->boolean('is_consumable')->default(true); // Indica si el recurso es consumible o no
            $table->decimal('critical_threshold', 8, 2); // Alarma critica
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
