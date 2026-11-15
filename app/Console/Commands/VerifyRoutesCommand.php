<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyRoutesCommand extends Command
{
    protected $signature = 'routes:verify';

    protected $description = 'Verifica que todas las rutas del sistema respondan correctamente';

    public function handle(Kernel $kernel): int
    {
        $this->info('=== VERIFICANDO TODAS LAS RUTAS DEL SISTEMA ===');

        $coord = User::where('email_tipo', 'coordinador')->first();
        $tecnico = User::where('email_tipo', 'tecnico')->first();
        $almacen = User::where('email_tipo', 'almacen')->first();
        $alumno = User::where('email_tipo', 'alumno')->first();

        $routesToTest = [
            // Públicas
            ['method' => 'GET', 'uri' => '/', 'name' => 'home', 'user' => null, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/contacto', 'name' => 'contacto', 'user' => null, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/login', 'name' => 'login', 'user' => null, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/dashboard', 'name' => 'dashboard', 'user' => $coord, 'expectedStatus' => [200, 302]],

            // Reportes
            ['method' => 'GET', 'uri' => '/reportes/nuevo', 'name' => 'reportes.create', 'user' => $alumno, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/reportes/crear', 'name' => 'reportes.crear', 'user' => $alumno, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/reportes', 'name' => 'reportes.index', 'user' => $alumno, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/reportes/mis-reportes', 'name' => 'reportes.mis-reportes', 'user' => $alumno, 'expectedStatus' => [200]],

            // Coordinación
            ['method' => 'GET', 'uri' => '/coordinacion', 'name' => 'coordinacion.index', 'user' => $coord, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/coordinacion/duplicados', 'name' => 'coordinacion.duplicados', 'user' => $coord, 'expectedStatus' => [200]],

            // Cuadrillas / Técnicos
            ['method' => 'GET', 'uri' => '/cuadrillas', 'name' => 'cuadrillas.index', 'user' => $tecnico, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/tecnico/mis-ordenes', 'name' => 'tecnico.ordenes', 'user' => $tecnico, 'expectedStatus' => [200]],

            // Almacén
            ['method' => 'GET', 'uri' => '/almacen', 'name' => 'almacen.index', 'user' => $almacen, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/almacen/inventario', 'name' => 'almacen.inventario', 'user' => $almacen, 'expectedStatus' => [200]],
            ['method' => 'GET', 'uri' => '/almacen/requisiciones', 'name' => 'almacen.requisiciones', 'user' => $almacen, 'expectedStatus' => [200]],

            // Autenticación POST
            [
                'method' => 'POST',
                'uri' => '/login',
                'name' => 'login.post (inválido)',
                'user' => null,
                'data' => [
                    'email' => 'invalido@gmail.com',
                    'password' => 'wrong',
                    'rol' => 'alumno',
                ],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [422],
            ],
            [
                'method' => 'POST',
                'uri' => '/login',
                'name' => 'login.post (válido coordinador)',
                'user' => null,
                'data' => [
                    'email' => (string) env('SEED_COORDINADOR_EMAIL', 'coordinacion@univ.edu.mx'),
                    'password' => (string) env('SEED_COORDINADOR_PASSWORD', 'Coordinador123!'),
                    'rol' => 'coordinador',
                ],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [200],
            ],
            [
                'method' => 'POST',
                'uri' => '/logout',
                'name' => 'logout',
                'user' => $coord,
                'expectedStatus' => [302],
            ],

            // Acciones POST operativas (con validación JSON 422 o 302 ante datos vacíos/incompletos)
            [
                'method' => 'POST',
                'uri' => '/reportes',
                'name' => 'reportes.store (validación)',
                'user' => $alumno,
                'data' => [],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [422],
            ],
            [
                'method' => 'POST',
                'uri' => '/coordinacion/consolidar-duplicados',
                'name' => 'coordinacion.consolidar (validación)',
                'user' => $coord,
                'data' => [],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [422],
            ],
            [
                'method' => 'POST',
                'uri' => '/coordinacion/asignar-cuadrilla',
                'name' => 'coordinacion.asignar (validación)',
                'user' => $coord,
                'data' => [],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [302, 404, 422],
            ],
            [
                'method' => 'POST',
                'uri' => '/tecnico/actualizar-orden',
                'name' => 'tecnico.actualizar (validación)',
                'user' => $tecnico,
                'data' => [],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [302, 422],
            ],
            [
                'method' => 'POST',
                'uri' => '/tecnico/solicitar-material',
                'name' => 'tecnico.solicitar-material (validación)',
                'user' => $tecnico,
                'data' => [],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [302, 404, 422],
            ],
            [
                'method' => 'POST',
                'uri' => '/tecnico/estimar-tiempo',
                'name' => 'tecnico.estimar-tiempo (validación)',
                'user' => $tecnico,
                'data' => [],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [302, 422],
            ],
            [
                'method' => 'POST',
                'uri' => '/almacen/despachar-vale',
                'name' => 'almacen.despachar (validación)',
                'user' => $almacen,
                'data' => [],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [302, 404, 422],
            ],
            [
                'method' => 'POST',
                'uri' => '/almacen/entrada',
                'name' => 'almacen.entrada (validación)',
                'user' => $almacen,
                'data' => [],
                'headers' => ['Accept' => 'application/json'],
                'expectedStatus' => [302, 404, 422],
            ],
        ];

        $total = count($routesToTest);
        $passed = 0;

        foreach ($routesToTest as $route) {
            if ($route['user']) {
                Auth::login($route['user']);
            } else {
                Auth::logout();
            }

            // Inicializar sesión y token CSRF
            $session = app('session')->driver();
            $session->start();
            $csrfToken = $session->token();

            $postData = $route['data'] ?? [];
            if ($route['method'] === 'POST') {
                $postData['_token'] = $csrfToken;
            }

            $headers = [
                'HTTP_ACCEPT' => $route['headers']['Accept'] ?? 'text/html,application/xhtml+xml,application/xml',
                'HTTP_X_CSRF_TOKEN' => $csrfToken,
            ];

            $req = Request::create(
                $route['uri'],
                $route['method'],
                $postData,
                [],
                [],
                $headers
            );

            $req->setLaravelSession($session);

            try {
                $response = $kernel->handle($req);
                $status = $response->getStatusCode();
                $kernel->terminate($req, $response);

                if (in_array($status, $route['expectedStatus'], true)) {
                    $this->line("<info>[PASS {$status}]</info> {$route['method']} {$route['uri']} ({$route['name']})");
                    $passed++;
                } else {
                    $this->error("[FAIL {$status}] {$route['method']} {$route['uri']} ({$route['name']}) - Esperado: " . implode(',', $route['expectedStatus']));
                }
            } catch (\Throwable $e) {
                $this->error("[EXCEPTION] {$route['method']} {$route['uri']} ({$route['name']}): " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Resultado: {$passed} de {$total} rutas validadas exitosamente.");

        return $passed === $total ? 0 : 1;
    }
}
