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
        Schema::create('tareas_orden', function (Blueprint $table) {
            $table->id('id_tarea');
            $table->foreignId('id_orden')->constrained('ordenes_trabajo', 'id_orden')->cascadeOnDelete();
            $table->text('descripcion_tarea');
            $table->boolean('completada')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas_orden');
    }
};
