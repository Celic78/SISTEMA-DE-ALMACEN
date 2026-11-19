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
        Schema::create('movimientos_material', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_material')->constrained('materiales', 'id_material')->cascadeOnDelete();
            $table->foreignId('id_orden')->nullable()->constrained('ordenes_trabajo', 'id_orden')->nullOnDelete();
            $table->foreignId('id_usuario_almacen')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('tipo_movimiento');
            $table->decimal('cantidad', 10, 2);
            $table->dateTime('fecha_movimiento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_material');
    }
};
