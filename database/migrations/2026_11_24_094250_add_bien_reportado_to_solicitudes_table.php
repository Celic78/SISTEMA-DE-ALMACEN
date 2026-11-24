<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table): void {
            $table->foreignId('id_bien_espacio')->nullable()->constrained('bienes_espacio')->nullOnDelete();
            $table->string('bien_reportado')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('id_bien_espacio');
            $table->dropColumn('bien_reportado');
        });
    }
};
