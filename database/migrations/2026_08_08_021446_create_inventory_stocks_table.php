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
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete(); // Relacion con la tabla locations
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete(); // Relacion con la tabla resources
            $table->decimal('quantity', 10, 2)->default(0); // Cantidad de recurso en stock
            $table->string('status')->default('Optimo'); // Estado del stock, puede ser: Optimo, Bajo, Critico
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
