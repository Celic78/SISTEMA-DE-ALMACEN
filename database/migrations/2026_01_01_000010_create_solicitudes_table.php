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
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id('id_solicitud');
            $table->foreignId('id_usuario')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('id_espacio')->constrained('espacios', 'id_espacio')->cascadeOnDelete();
            $table->foreignId('id_categoria')->constrained('categorias_solicitud', 'id_categoria')->cascadeOnDelete();
            $table->foreignId('id_prioridad')->nullable()->constrained('matrices_prioridad', 'id_prioridad')->nullOnDelete();
            $table->foreignId('id_solicitud_padre')->nullable()->constrained('solicitudes', 'id_solicitud')->nullOnDelete();
            $table->text('descripcion_falla');
            $table->dateTime('fecha_hallazgo');
            $table->string('estado')->default('pendiente');
            $table->string('evidencia_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
