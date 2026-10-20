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
        Schema::create('reportes_finales', function (Blueprint $table) {
            $table->id('id_reporte');
            $table->foreignId('id_orden')->constrained('ordenes_trabajo', 'id_orden')->cascadeOnDelete();
            $table->text('resumen_ejecutivo');
            $table->dateTime('fecha_generacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_finales');
    }
};
