<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            $table->decimal('monto_extra', 10, 2)->default(0);
            $table->foreignId('id_tecnico_apoyo')->nullable()->constrained('users', 'id')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('id_tecnico_apoyo');
            $table->dropColumn('monto_extra');
        });
    }
};
