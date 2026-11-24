<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            $table->string('nombre_tecnico_apoyo', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            $table->dropColumn('nombre_tecnico_apoyo');
        });
    }
};
