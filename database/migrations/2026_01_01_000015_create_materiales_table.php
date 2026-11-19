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
        Schema::create('materiales', function (Blueprint $table) {
            $table->id('id_material');
            $table->foreignId('id_seccion')->constrained('secciones_almacen', 'id_seccion')->cascadeOnDelete();
            $table->string('nombre_material');
            $table->text('especificacion')->nullable();
            $table->string('unidad_medida');
            $table->string('codigo_qr_barras')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
