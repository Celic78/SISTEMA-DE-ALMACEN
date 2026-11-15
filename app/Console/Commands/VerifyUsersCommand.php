<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class VerifyUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el recuento, roles y autenticación de los usuarios del sistema';

    /**
     * Execute the console command.
     */
    public function handle(AuthService $authService): int
    {
        $this->info('=== VERIFICACIÓN DE USUARIOS EN BASE DE DATOS ===');

        $coordCount = User::where('email_tipo', 'coordinador')->count();
        $almacenCount = User::where('email_tipo', 'almacen')->count();
        $tecnicoCount = User::where('email_tipo', 'tecnico')->count();
        $profesorCount = User::where('email_tipo', 'profesor')->count();
        $alumnoCount = User::where('email_tipo', 'alumno')->count();
        $total = User::count();

        $this->table(
            ['Rol / Tipo', 'Esperado', 'Encontrado en BD', 'Estado'],
            [
                ['Coordinador', 1, $coordCount, $coordCount === 1 ? 'OK' : 'ERROR'],
                ['Alumnos', 200, $alumnoCount, $alumnoCount === 200 ? 'OK' : 'ERROR'],
                ['Maestros (Profesores)', 30, $profesorCount, $profesorCount === 30 ? 'OK' : 'ERROR'],
                ['Cuadrilla Técnica', 20, $tecnicoCount, $tecnicoCount === 20 ? 'OK' : 'ERROR'],
                ['Personal de Almacén', 3, $almacenCount, $almacenCount === 3 ? 'OK' : 'ERROR'],
                ['TOTAL GENERAL', 254, $total, $total >= 254 ? 'OK' : 'ERROR'],
            ]
        );

        // Verificación de turnos en cuadrilla técnica
        $tecnicosMatutinos = 0;
        $tecnicosVespertinos = 0;
        foreach (User::where('email_tipo', 'tecnico')->with('roles')->get() as $tec) {
            $rol = $tec->roles->firstWhere('nombre_rol', 'Tecnico');
            if ($rol && $rol->pivot->turno_tecnico === 'Matutino') {
                $tecnicosMatutinos++;
            }
            if ($rol && $rol->pivot->turno_tecnico === 'Vespertino') {
                $tecnicosVespertinos++;
            }
        }

        $this->info("Cuadrilla Técnica - Turno Matutino: {$tecnicosMatutinos}, Turno Vespertino: {$tecnicosVespertinos}");

        // Verificación de credenciales con AuthService
        $this->newLine();
        $this->info('=== PRUEBAS DE AUTENTICACIÓN INSTITUCIONAL CON AUTHSERVICE ===');

        $testCases = [
            [
                'rol' => 'Coordinador',
                'email' => (string) env('SEED_COORDINADOR_EMAIL', 'coordinacion@univ.edu.mx'),
                'password' => (string) env('SEED_COORDINADOR_PASSWORD', 'Coordinador123!'),
            ],
            [
                'rol' => 'Almacén',
                'email' => (string) env('SEED_ALMACEN_1_EMAIL', 'almacen@univ.edu.mx'),
                'password' => (string) env('SEED_ALMACEN_1_PASSWORD', 'Almacen123!'),
            ],
            [
                'rol' => 'Técnico',
                'email' => 'tecnico1@tecnicos.univ.edu.mx',
                'password' => (string) env('SEED_TECNICO_PASSWORD', 'Tecnico123!'),
            ],
            [
                'rol' => 'Profesor',
                'email' => 'profesor1@profesores.univ.edu.mx',
                'password' => (string) env('SEED_MAESTRO_PASSWORD', 'Profesor123!'),
            ],
            [
                'rol' => 'Alumno',
                'email' => 'alumno1@alumnos.univ.edu.mx',
                'password' => (string) env('SEED_ALUMNO_PASSWORD', 'Alumno123!'),
            ],
        ];

        foreach ($testCases as $test) {
            try {
                $resultado = $authService->autenticar($test['email'], $test['password'], $test['rol']);
                $this->line("<info>[PASS]</info> Rol: {$test['rol']} | Email: {$test['email']} -> Redirige a: {$resultado['redirect_url']}");
            } catch (\Throwable $e) {
                $this->error("[FAIL] Rol: {$test['rol']} | Email: {$test['email']} -> Error: {$e->getMessage()}");

                return 1;
            }
        }

        $this->newLine();
        $this->info('¡Todos los usuarios y credenciales configurados y validados con éxito!');

        return 0;
    }
}
