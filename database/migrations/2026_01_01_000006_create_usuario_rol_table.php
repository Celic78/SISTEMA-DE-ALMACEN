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
        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->foreignId('id_usuario')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('id_rol')->constrained('roles', 'id_rol')->cascadeOnDelete();
            $table->string('turno_tecnico')->nullable();
            $table->string('especialidad_tecnico')->nullable();
            $table->primary(['id_usuario', 'id_rol']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
    }
};
