<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bienes_espacio', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_espacio')->constrained('espacios', 'id_espacio')->restrictOnDelete();
            $table->string('codigo_inventario', 60)->unique();
            $table->string('nombre', 120);
            $table->string('tipo', 80);
            $table->string('ubicacion_interna', 150);
            $table->string('marca', 80)->nullable();
            $table->string('modelo', 80)->nullable();
            $table->string('numero_serie', 80)->nullable();
            $table->string('estado', 30);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bienes_espacio');
    }
};
