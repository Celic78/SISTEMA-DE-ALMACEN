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
        Schema::create('historial_estados', function (Blueprint $table) {
            $table->id('id_historial');
            $table->foreignId('id_orden')->constrained('ordenes_trabajo', 'id_orden')->cascadeOnDelete();
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->foreignId('id_usuario_cambio')->constrained('users', 'id')->cascadeOnDelete();
            $table->dateTime('fecha_cambio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estados');
    }
};
