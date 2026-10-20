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
        Schema::create('existencias', function (Blueprint $table) {
            $table->id('id_existencia');
            $table->foreignId('id_material')->constrained('materiales', 'id_material')->cascadeOnDelete();
            $table->decimal('cantidad_disponible', 10, 2)->default(0.00);
            $table->decimal('stock_minimo', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('existencias');
    }
};
