<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes_operativos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('remitente')->constrained('users')->cascadeOnDelete();
            $table->foreignId('destinatario')->constrained('users')->cascadeOnDelete();
            $table->text('mensaje');
            $table->dateTime('enviado');
            $table->index(['remitente', 'destinatario']);
        });

        Schema::create('avisos_mantenimiento', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo', 160);
            $table->dateTime('fecha')->index();
            $table->foreignId('id_coordinador')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignId('id_categoria')->nullable()->constrained('categorias_solicitud', 'id_categoria')->nullOnDelete();
            $table->string('ubicacion')->nullable();
            $table->unsignedSmallInteger('intervalo_dias')->nullable();
            $table->dateTime('ultima_generacion')->nullable();
            $table->foreignId('id_orden_generada')->nullable()->constrained('ordenes_trabajo', 'id_orden')->nullOnDelete();
            $table->unique(['titulo', 'fecha']);
        });

        Schema::table('solicitudes', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_espacio')->nullable()->change();
            $table->string('ubicacion_libre')->nullable();
            $table->string('huella_reporte', 64)->nullable();
        });

        Schema::table('existencias', function (Blueprint $table): void {
            $table->decimal('cantidad_reservada', 12, 2)->default(0);
        });

        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            $table->unsignedSmallInteger('horas_estimadas_sla')->nullable();
            $table->dateTime('fecha_asignacion')->nullable();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('estado_registro', 12)->default('aprobado')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_operativos');
        Schema::dropIfExists('avisos_mantenimiento');

        Schema::table('solicitudes', function (Blueprint $table): void {
            $table->dropColumn(['ubicacion_libre', 'huella_reporte']);
        });

        Schema::table('existencias', function (Blueprint $table): void {
            $table->dropColumn('cantidad_reservada');
        });

        Schema::table('ordenes_trabajo', function (Blueprint $table): void {
            $table->dropColumn(['horas_estimadas_sla', 'fecha_asignacion']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['estado_registro']);
            $table->dropColumn('estado_registro');
        });
    }
};
