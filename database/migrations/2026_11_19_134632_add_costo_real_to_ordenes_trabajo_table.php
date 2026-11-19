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
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->decimal('costo_real', 10, 2)->nullable();
            $table->string('justificacion_costo_real', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->dropColumn(['costo_real', 'justificacion_costo_real']);
        });
    }
};
