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
        Schema::create('evidencias_cierre', function (Blueprint $table) {
            $table->id('id_evidencia');
            $table->foreignId('id_orden')->constrained('ordenes_trabajo', 'id_orden')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('url_archivo');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidencias_cierre');
    }
};
