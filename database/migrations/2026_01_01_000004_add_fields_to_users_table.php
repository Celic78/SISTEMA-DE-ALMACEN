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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('email_tipo', ['alumno', 'profesor', 'coordinador', 'almacen', 'tecnico'])->after('email')->nullable();
            $table->foreignId('id_espacio')->nullable()->after('email_tipo')->constrained('espacios', 'id_espacio')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_espacio']);
            $table->dropColumn(['email_tipo', 'id_espacio']);
        });
    }
};
