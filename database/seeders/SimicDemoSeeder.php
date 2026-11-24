<?php

namespace Database\Seeders;

use App\Models\CategoriaSolicitud;
use App\Models\Edificio;
use App\Models\Espacio;
use App\Models\Material;
use App\Models\MatrizPrioridad;
use App\Models\Plantel;
use App\Models\Rol;
use App\Models\SeccionAlmacen;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\CoordinacionService;
use App\Services\SolicitudService;
use Illuminate\Database\Seeder;

class SimicDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Los datos de demostración solo se habilitan en local o testing.');
        }
        $plantel = Plantel::firstOrCreate(['nombre' => 'Campus de demostración SIMIC'], ['direccion' => 'Plantel de ejemplo para revisión local']);
        $building = Edificio::firstOrCreate(['id_plantel' => $plantel->id_plantel, 'nombre' => 'Edificio 1 · Docencia Norte']);
        $space = Espacio::firstOrCreate(['id_edificio' => $building->id_edificio, 'nombre_espacio' => 'Aula 204'], ['tipo_espacio' => 'Aula', 'piso' => 'Segundo nivel']);
        Espacio::firstOrCreate(['id_edificio' => $building->id_edificio, 'nombre_espacio' => 'Sanitarios PB'], ['tipo_espacio' => 'Sanitario', 'piso' => 'Planta baja']);
        $category = CategoriaSolicitud::firstOrCreate(['nombre_categoria' => 'Electricidad e iluminación']);
        CategoriaSolicitud::firstOrCreate(['nombre_categoria' => 'Plomería e hidráulica']);
        CategoriaSolicitud::firstOrCreate(['nombre_categoria' => 'Mobiliario y acabados']);
        foreach (['Media' => 24, 'Alta' => 8, 'Crítica' => 2] as $level => $hours) {
            MatrizPrioridad::firstOrCreate(['nivel' => $level], ['tiempo_respuesta_hrs' => $hours]);
        }
        $accounts = [
            'alumno' => ['Alumno de demostración', 'demo@alumnos.edu.mx', 'Alumno'],
            'profesor' => ['Profesor de demostración', 'demo@profesores.edu.mx', 'Profesor'],
            'coordinador' => ['Coordinación de demostración', 'coordinacion@univ.edu.mx', 'Coordinador'],
            'tecnico' => ['Técnico de demostración', 'demo@tecnicos.edu.mx', 'Tecnico'],
            'almacen' => ['Almacén de demostración', 'almacen@univ.edu.mx', 'Almacen'],
        ];
        $users = [];
        foreach ($accounts as $type => [$name, $email, $roleName]) {
            $role = Rol::firstOrCreate(['nombre_rol' => $roleName]);
            $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => 'SimicDemo2026!', 'email_tipo' => $type]);
            $user->roles()->syncWithoutDetaching([$role->id_rol => $type === 'tecnico' ? ['turno_tecnico' => 'Matutino'] : []]);
            $users[$type] = $user;
        }
        $section = SeccionAlmacen::firstOrCreate(['nombre_seccion' => 'Eléctrico'], ['ubicacion_almacen' => 'Anaquel E-02 · Gaveta 4']);
        foreach ([['SIMIC-DEMO-E01', 'Balastro electrónico 2x32W', 'Pieza', 3, 15], ['SIMIC-DEMO-E02', 'Cable THW calibre 12', 'Metro', 340, 100], ['SIMIC-DEMO-E03', 'Luminaria LED', 'Pieza', 20, 5]] as [$sku, $name, $unit, $stock, $minimum]) {
            $material = Material::firstOrCreate(['codigo_qr_barras' => $sku], ['id_seccion' => $section->id_seccion, 'nombre_material' => $name, 'unidad_medida' => $unit]);
            $material->existencia()->firstOrCreate([], ['cantidad_disponible' => $stock, 'stock_minimo' => $minimum]);
        }
        if (! Solicitud::where('id_usuario', $users['alumno']->id)->exists()) {
            app(SolicitudService::class)->crearSolicitud(['id_edificio' => $building->id_edificio, 'id_espacio' => $space->id_espacio, 'id_categoria' => $category->id_categoria, 'descripcion_falla' => '[Demostración] La luminaria parpadea y presenta un zumbido constante.'], $users['alumno']);
            app(SolicitudService::class)->crearSolicitud(['id_edificio' => $building->id_edificio, 'id_espacio' => $space->id_espacio, 'id_categoria' => $category->id_categoria, 'descripcion_falla' => '[Demostración] La luz del fondo del aula se apaga durante la clase.'], $users['profesor']);
            $second = $building->espacios()->where('nombre_espacio', 'Sanitarios PB')->firstOrFail();
            $report = app(SolicitudService::class)->crearSolicitud(['id_edificio' => $building->id_edificio, 'id_espacio' => $second->id_espacio, 'id_categoria' => $category->id_categoria, 'descripcion_falla' => '[Demostración] Revisión de luminarias del pasillo de sanitarios.'], $users['alumno']);
            app(CoordinacionService::class)->asignarTecnicoAOrden($report->ordenTrabajo, $users['tecnico'], $users['coordinador'], 18000);
        }
    }
}
