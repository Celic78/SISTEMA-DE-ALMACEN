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
        Schema::create('aprobaciones', function (Blueprint $table) {
            $table->id('id_aprobacion');
            $table->foreignId('id_orden')->constrained('ordenes_trabajo', 'id_orden')->cascadeOnDelete();
            $table->foreignId('id_coordinador')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('estado');
            $table->text('motivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aprobaciones');
    }
};
