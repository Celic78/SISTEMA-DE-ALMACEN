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
        Schema::create('bitacora_sistema', function (Blueprint $table) {
            $table->id('id_bitacora');
            $table->foreignId('id_usuario')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->string('accion');
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_sistema');
    }
};
