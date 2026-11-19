@extends('layouts.simic')
@section('title', $title)
@section('actions')
@if($screen === 'reports')<a class="btn" href="{{ route('reportes.create') }}">Nuevo reporte</a>@endif
@if($screen === 'report-detail')<a class="btn btn-secondary" href="{{ route(auth()->user()->isCoordinador() ? 'coordinacion.incidencias' : 'reportes.index') }}">Volver a reportes</a>@endif
@if($screen === 'inventory')<a class="btn" href="{{ route('almacen.entrada') }}">Registrar entrada</a>@endif
@if($screen === 'ledger')<a class="btn btn-secondary" href="{{ route('almacen.exportar', request()->query()) }}">Exportar CSV</a><a class="btn btn-secondary" href="{{ route('almacen.exportar', array_merge(request()->query(), ['formato' => 'pdf'])) }}" target="_blank" rel="noopener">Imprimir / guardar PDF</a>@endif
@endsection
@section('content')
@php
    $states = ['pendiente' => 'En espera', 'abierta' => 'Por asignar', 'en_revision' => 'Por revisar', 'asignada' => 'Asignada', 'en_proceso' => 'En atención', 'requiere_aprobacion' => 'Requiere autorización', 'aprobada' => 'Aprobada', 'resuelta' => 'Concluida', 'cerrada' => 'Cerrada', 'rechazada' => 'Rechazada', 'agrupada' => 'Agrupada', 'cancelada_duplicada' => 'Agrupada'];
    $folio = static fn($s) => 'FOL-'.($s->created_at?->format('Y') ?? date('Y')).'-'.str_pad((string)$s->id_solicitud, 4, '0', STR_PAD_LEFT);
    $orderFolio = static fn($o) => 'OT-'.($o->created_at?->format('Y') ?? date('Y')).'-'.str_pad((string)$o->id_orden, 4, '0', STR_PAD_LEFT);
@endphp
@isset($stats)<div class="stats">@foreach($stats as $label => $value)<div class="stat"><span>{{ $label }}</span><strong>{{ $value }}</strong><small>Registros del sistema</small></div>@endforeach</div>@endisset
@if(in_array($screen, ['report-form', 'reports', 'report-detail']))
    @include('partials.simic-reports')
@elseif(in_array($screen, ['summary', 'incidents', 'duplicates', 'assignment', 'approvals']))
    @include('partials.simic-coordination')
@elseif(in_array($screen, ['orders', 'order', 'material-request', 'closure']))
    @include('partials.simic-technical')
@else
    @include('partials.simic-warehouse')
@endif
@endsection
