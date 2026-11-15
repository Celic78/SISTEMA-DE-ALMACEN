<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles requeridos
        $rolCoordinador = Rol::firstOrCreate(['nombre_rol' => 'Coordinador']);
        $rolAdmin = Rol::firstOrCreate(['nombre_rol' => 'Administrador']);
        $rolAlmacen = Rol::firstOrCreate(['nombre_rol' => 'Almacen']);
        $rolTecnico = Rol::firstOrCreate(['nombre_rol' => 'Tecnico']);
        $rolProfesor = Rol::firstOrCreate(['nombre_rol' => 'Profesor']);
        $rolAlumno = Rol::firstOrCreate(['nombre_rol' => 'Alumno']);

        // Caché de contraseñas hasheadas para optimizar rendimiento de inserción
        $hashCache = [];
        $hashPassword = function (string $plain) use (&$hashCache): string {
            return $hashCache[$plain] ??= Hash::make($plain);
        };

        // Respaldo global por defecto
        $defaultPassword = (string) env('SEED_DEFAULT_PASSWORD', 'Password123!');

        // Limpiar usuario de prueba residual si existe
        User::where('email', 'test@example.com')->delete();

        // ------------------------------------------------------------------
        // 1. COORDINADOR (1 usuario)
        // ------------------------------------------------------------------
        $coordEmail = (string) env('SEED_COORDINADOR_EMAIL', 'coordinacion@univ.edu.mx');
        $coordName = (string) env('SEED_COORDINADOR_NAME', 'Lic. Roberto Garza (Coordinación General)');
        $coordPassword = (string) env('SEED_COORDINADOR_PASSWORD', $defaultPassword);

        $coordinador = User::updateOrCreate(
            ['email' => $coordEmail],
            [
                'name' => $coordName,
                'email_tipo' => 'coordinador',
                'password' => $hashPassword($coordPassword),
                'email_verified_at' => now(),
            ]
        );
        $coordinador->roles()->syncWithoutDetaching([
            $rolCoordinador->id_rol,
            $rolAdmin->id_rol,
        ]);

        // ------------------------------------------------------------------
        // 2. PERSONAL DE ALMACÉN (3 usuarios)
        // ------------------------------------------------------------------
        $almacenDefaultPassword = (string) env('SEED_ALMACEN_PASSWORD', $defaultPassword);

        $almacenistas = [
            [
                'name' => (string) env('SEED_ALMACEN_1_NAME', 'Encargado General de Almacén'),
                'email' => (string) env('SEED_ALMACEN_1_EMAIL', 'almacen@univ.edu.mx'),
                'password' => (string) env('SEED_ALMACEN_1_PASSWORD', $almacenDefaultPassword),
            ],
            [
                'name' => (string) env('SEED_ALMACEN_2_NAME', 'Auxiliar de Almacén - Recepción'),
                'email' => (string) env('SEED_ALMACEN_2_EMAIL', 'almacen2@almacen.univ.edu.mx'),
                'password' => (string) env('SEED_ALMACEN_2_PASSWORD', $almacenDefaultPassword),
            ],
            [
                'name' => (string) env('SEED_ALMACEN_3_NAME', 'Auxiliar de Almacén - Despacho'),
                'email' => (string) env('SEED_ALMACEN_3_EMAIL', 'almacen3@almacen.univ.edu.mx'),
                'password' => (string) env('SEED_ALMACEN_3_PASSWORD', $almacenDefaultPassword),
            ],
        ];

        foreach ($almacenistas as $datosAlmacen) {
            $userAlmacen = User::updateOrCreate(
                ['email' => $datosAlmacen['email']],
                [
                    'name' => $datosAlmacen['name'],
                    'email_tipo' => 'almacen',
                    'password' => $hashPassword($datosAlmacen['password']),
                    'email_verified_at' => now(),
                ]
            );
            $userAlmacen->roles()->syncWithoutDetaching([$rolAlmacen->id_rol]);
        }

        // ------------------------------------------------------------------
        // 3. CUADRILLA TÉCNICA (20 usuarios)
        // ------------------------------------------------------------------
        $tecnicoDefaultPassword = (string) env('SEED_TECNICO_PASSWORD', $defaultPassword);
        $tecnicoDomain = (string) env('SEED_TECNICO_DOMAIN', 'tecnicos.univ.edu.mx');
        $tecnicoPrefix = (string) env('SEED_TECNICO_EMAIL_PREFIX', 'tecnico');

        $especialidades = [
            'Electricidad',
            'Plomería',
            'Cerrajería',
            'Climatización',
            'Pintura',
            'Albañilería',
            'Carpintería',
            'Mantenimiento General',
            'Redes y Telecomunicaciones',
            'Herrería',
        ];

        for ($i = 1; $i <= 20; $i++) {
            $turno = ($i <= 10) ? 'Matutino' : 'Vespertino';
            $especialidadIndex = ($i - 1) % count($especialidades);
            $especialidad = $especialidades[$especialidadIndex];

            $email = (string) env("SEED_TECNICO_{$i}_EMAIL", "{$tecnicoPrefix}{$i}@{$tecnicoDomain}");
            $name = (string) env("SEED_TECNICO_{$i}_NAME", "Técnico {$i} - {$especialidad} ({$turno})");
            $password = (string) env("SEED_TECNICO_{$i}_PASSWORD", $tecnicoDefaultPassword);

            $tecnico = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email_tipo' => 'tecnico',
                    'password' => $hashPassword($password),
                    'email_verified_at' => now(),
                ]
            );

            $tecnico->roles()->syncWithoutDetaching([
                $rolTecnico->id_rol => [
                    'turno_tecnico' => $turno,
                    'especialidad_tecnico' => $especialidad,
                ],
            ]);
        }

        // ------------------------------------------------------------------
        // 4. MAESTROS / PROFESORES (30 usuarios)
        // ------------------------------------------------------------------
        $maestroDefaultPassword = (string) env('SEED_MAESTRO_PASSWORD', $defaultPassword);
        $maestroDomain = (string) env('SEED_MAESTRO_DOMAIN', 'profesores.univ.edu.mx');
        $maestroPrefix = (string) env('SEED_MAESTRO_EMAIL_PREFIX', 'profesor');

        for ($i = 1; $i <= 30; $i++) {
            $email = (string) env("SEED_MAESTRO_{$i}_EMAIL", "{$maestroPrefix}{$i}@{$maestroDomain}");
            $name = (string) env("SEED_MAESTRO_{$i}_NAME", "Profesor {$i}");
            $password = (string) env("SEED_MAESTRO_{$i}_PASSWORD", $maestroDefaultPassword);

            $profesor = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email_tipo' => 'profesor',
                    'password' => $hashPassword($password),
                    'email_verified_at' => now(),
                ]
            );

            $profesor->roles()->syncWithoutDetaching([$rolProfesor->id_rol]);
        }

        // ------------------------------------------------------------------
        // 5. ALUMNOS (200 usuarios)
        // ------------------------------------------------------------------
        $alumnoDefaultPassword = (string) env('SEED_ALUMNO_PASSWORD', $defaultPassword);
        $alumnoDomain = (string) env('SEED_ALUMNO_DOMAIN', 'alumnos.univ.edu.mx');
        $alumnoPrefix = (string) env('SEED_ALUMNO_EMAIL_PREFIX', 'alumno');

        for ($i = 1; $i <= 200; $i++) {
            $email = (string) env("SEED_ALUMNO_{$i}_EMAIL", "{$alumnoPrefix}{$i}@{$alumnoDomain}");
            $name = (string) env("SEED_ALUMNO_{$i}_NAME", "Alumno {$i}");
            $password = (string) env("SEED_ALUMNO_{$i}_PASSWORD", $alumnoDefaultPassword);

            $alumno = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'email_tipo' => 'alumno',
                    'password' => $hashPassword($password),
                    'email_verified_at' => now(),
                ]
            );

            $alumno->roles()->syncWithoutDetaching([$rolAlumno->id_rol]);
        }
    }
}
