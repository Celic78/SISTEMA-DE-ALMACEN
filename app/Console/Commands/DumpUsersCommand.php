<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class DumpUsersCommand extends Command
{
    protected $signature = 'users:dump {--format=json}';

    protected $description = 'Exporta todos los usuarios con sus contraseñas en texto plano';

    public function handle(): int
    {
        $defaultPassword = (string) env('SEED_DEFAULT_PASSWORD', 'Password123!');
        $coordPassword = (string) env('SEED_COORDINADOR_PASSWORD', $defaultPassword);
        $almacenDefault = (string) env('SEED_ALMACEN_PASSWORD', $defaultPassword);
        $tecnicoDefault = (string) env('SEED_TECNICO_PASSWORD', $defaultPassword);
        $maestroDefault = (string) env('SEED_MAESTRO_PASSWORD', $defaultPassword);
        $alumnoDefault = (string) env('SEED_ALUMNO_PASSWORD', $defaultPassword);

        $users = User::with('roles')->orderBy('id')->get();
        $this->info("Total de usuarios en base de datos: " . $users->count());

        $list = [];
        foreach ($users as $user) {
            $tipo = $user->email_tipo ?? 'desconocido';
            $plain = match ($tipo) {
                'coordinador' => $coordPassword,
                'almacen' => $almacenDefault,
                'tecnico' => $tecnicoDefault,
                'profesor' => $maestroDefault,
                'alumno' => $alumnoDefault,
                default => $defaultPassword,
            };

            // Check if hash matches
            $matches = Hash::check($plain, $user->password);

            $rol = $user->roles->first();
            $rolName = $rol?->nombre_rol ?? ucfirst($tipo);
            $turno = $rol?->pivot?->turno_tecnico;
            $especialidad = $rol?->pivot?->especialidad_tecnico;

            $list[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $plain,
                'password_hash_verified' => $matches,
                'tipo' => $tipo,
                'rol' => $rolName,
                'turno' => $turno,
                'especialidad' => $especialidad,
            ];
        }

        $json = json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(storage_path('app/users_dump.json'), $json);

        $this->info("Exportación guardada en: " . storage_path('app/users_dump.json'));

        return 0;
    }
}
