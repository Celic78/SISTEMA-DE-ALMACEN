<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mensajes_operativos', function (Blueprint $table): void {
            $table->dateTime('leido')->nullable();
            $table->index(['destinatario', 'leido', 'remitente'], 'mensajes_operativos_pendientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mensajes_operativos', function (Blueprint $table): void {
            $table->dropIndex('mensajes_operativos_pendientes');
            $table->dropColumn('leido');
        });
    }
};
