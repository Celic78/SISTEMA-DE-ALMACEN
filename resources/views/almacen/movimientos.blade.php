@extends('layouts.private')

@section('title', 'Entradas / Salidas')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Entradas / Salidas</h1>
    @include('partials.alerts')
    <div class="mb-4 flex flex-wrap gap-2">
        <a class="button button-secondary" href="{{ route('almacen.index') }}">Registrar entrada en inventario</a>
        <a class="button button-secondary" href="{{ route('almacen.requisiciones') }}">Despachar requisiciones</a>
    </div>
    <form method="GET" action="{{ route('almacen.movimientos') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <label>Tipo de movimiento
            <select name="tipo" class="block rounded border p-2">
                <option value="todos" @selected(! in_array($tipo, ['entrada', 'salida'], true))>Todos</option>
                <option value="entrada" @selected($tipo === 'entrada')>Entradas</option>
                <option value="salida" @selected($tipo === 'salida')>Salidas</option>
            </select>
        </label>
        <button class="button button-secondary">Filtrar</button>
    </form>
    <h2 class="mb-3 text-xl font-bold">{{ match ($tipo) {
        'salida' => 'Salida',
        'entrada' => 'Entrada',
        default => 'Todos los movimientos',
    } }}</h2>
    <div class="warehouse-table-wrap rounded-xl border border-slate-200 bg-white" role="region" aria-label="Historial de movimientos" tabindex="0">
        <table class="warehouse-table">
            <thead><tr><th scope="col">Fecha</th><th scope="col">Material</th><th scope="col">Tipo</th><th scope="col">Cantidad</th><th scope="col">Motivo</th><th scope="col">Prestado a</th><th scope="col">Devolución</th><th scope="col">Registró</th></tr></thead>
            <tbody>
                @forelse ($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento->fecha_movimiento?->format('d/m/Y H:i') }}</td>
                        <td>{{ $movimiento->material?->nombre_material }}</td>
                        <td>{{ match ($movimiento->clase_salida) {
                            'reposicion' => 'Reposición',
                            'prestamo' => 'Préstamo',
                            default => ucfirst($movimiento->tipo_movimiento),
                        } }}</td>
                        <td>{{ $movimiento->cantidad }} {{ $movimiento->material?->unidad_medida }}</td>
                        <td>{{ $movimiento->motivo ?? '—' }}</td>
                        <td>{{ $movimiento->prestado_a ?? '—' }}</td>
                        <td>
                            @if ($movimiento->devuelto_en)
                                {{ $movimiento->devuelto_en->format('d/m/Y H:i') }}
                            @elseif ($movimiento->clase_salida === 'prestamo')
                                <p>Pendiente</p>
                                <form method="POST" action="{{ route('almacen.movimientos.devolver', $movimiento) }}">
                                    @csrf
                                    <button class="button button-secondary">Registrar devolución</button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $movimiento->usuarioAlmacen?->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8">No hay movimientos registrados para este filtro.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movimientos->links() }}</div>
@endsection
