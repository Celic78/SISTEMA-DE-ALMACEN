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
        Schema::create('ordenes_trabajo', function (Blueprint $table) {
            $table->id('id_orden');
            $table->foreignId('id_solicitud')->unique()->constrained('solicitudes', 'id_solicitud')->cascadeOnDelete();
            $table->foreignId('id_tecnico')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->decimal('monto_estimado', 10, 2)->default(0.00);
            $table->boolean('requiere_aprobacion')->default(false);
            $table->string('estado')->default('abierta');
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_trabajo');
    }
};
