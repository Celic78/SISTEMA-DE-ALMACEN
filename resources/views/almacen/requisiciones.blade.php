@extends('layouts.private')

@section('title', 'Requisiciones')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Requisiciones de materiales</h1>
    @include('partials.alerts')
    <div class="grid gap-4">
        @forelse ($requisiciones as $requisicion)
            <article class="rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="font-bold">{{ $requisicion['folio_orden'] }} · {{ $requisicion['usuario_solicitante'] }}</h2>
                <p>{{ $requisicion['fecha_solicitud'] }} · {{ $requisicion['estado'] }}</p>
                <ul class="list-inside list-disc">
                    @foreach ($requisicion['insumos'] as $insumo)
                        <li>{{ $insumo['nombre'] ?? $insumo['nombre_material'] ?? $insumo['id_material'] ?? '' }} · {{ $insumo['cantidad'] ?? $insumo['cantidad_solicitada'] ?? '' }}</li>
                    @endforeach
                </ul>
                @if ($requisicion['recoleccion'])
                    <p>Recolección: {{ \Illuminate\Support\Carbon::parse($requisicion['recoleccion'])->format('d/m/Y H:i') }}</p>
                @endif
                @if ($requisicion['instrucciones'])<p>{{ $requisicion['instrucciones'] }}</p>@endif
                @if ($requisicion['motivo_cancelacion'])
                    <p>Motivo de cancelación: {{ $requisicion['motivo_cancelacion'] }}</p>
                @endif
                @if ($requisicion['entrega'])
                    <p>Entrega: {{ $requisicion['fecha_entrega'] }} · {{ $requisicion['entrega'] }}</p>
                @elseif ($requisicion['tipo'] === 'REQUISICION_MATERIALES_ALMACEN' && $requisicion['estado'] === 'pendiente')
                    <form method="POST" action="{{ route('almacen.requisiciones.despacho', $requisicion['id_requisicion']) }}" class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <label>Descripción de la entrega <input name="entrega" maxlength="500" class="block rounded border p-2" required></label>
                        <button class="button button-primary">Registrar entrega</button>
                    </form>
                    <form method="POST" action="{{ route('almacen.requisiciones.cancelar', $requisicion['id_requisicion']) }}" class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <label>Motivo de cancelación <input name="motivo" maxlength="500" class="block rounded border p-2" required></label>
                        <button class="button button-secondary">Cancelar requisición</button>
                    </form>
                @endif
                @if ($requisicion['devolucion'])
                    <p>Devolución: {{ $requisicion['fecha_devolucion'] }} · {{ $requisicion['cantidad_devuelta'] }} · {{ $requisicion['devolucion'] }}</p>
                @elseif ($requisicion['estado'] === 'entregada' && count($requisicion['insumos']) === 1)
                    <form method="POST" action="{{ route('almacen.requisiciones.devolucion', $requisicion['id_requisicion']) }}" class="mt-3 flex flex-wrap items-end gap-2">
                        @csrf
                        <label>Cantidad devuelta <input class="block rounded border p-2" name="cantidad" type="number" min="0" step=".01" max="{{ $requisicion['insumos'][0]['cantidad'] }}" required></label>
                        <label>Condición y justificación del consumo <input class="block rounded border p-2" name="motivo" maxlength="500" required></label>
                        <button class="button button-secondary">Registrar devolución o consumo</button>
                    </form>
                @endif
            </article>
        @empty
            <p>Todavía no hay requisiciones.</p>
        @endforelse
    </div>
@endsection
