<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_material', function (Blueprint $table): void {
            $table->string('clase_salida', 20)->nullable();
            $table->text('motivo')->nullable();
            $table->string('prestado_a', 150)->nullable();
            $table->dateTime('devuelto_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_material', function (Blueprint $table): void {
            $table->dropColumn(['clase_salida', 'motivo', 'prestado_a', 'devuelto_en']);
        });
    }
};
