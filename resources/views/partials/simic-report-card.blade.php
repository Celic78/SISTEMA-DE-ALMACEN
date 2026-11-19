<section class="panel">
    <h2>{{ isset($orden) && $screen !== 'report-detail' ? $orderFolio($orden) : $folio($solicitud) }}</h2>
    <span class="badge">{{ $states[$solicitud->estado] ?? $solicitud->estado }}</span>
    <dl class="data-grid"><div><dt>Ubicación</dt><dd>{{ $solicitud->espacio?->edificio?->nombre }} · {{ $solicitud->espacio?->nombre_espacio }}</dd></div><div><dt>Categoría</dt><dd>{{ $solicitud->categoria?->nombre_categoria ?? 'Sin categoría' }}</dd></div><div><dt>Fecha del hallazgo</dt><dd>{{ $solicitud->fecha_hallazgo?->format('d/m/Y · H:i') }}</dd></div><div><dt>Técnico asignado</dt><dd>{{ $orden?->tecnico?->name ?? 'Pendiente de asignación' }}</dd></div></dl>
    <h3>Descripción</h3><p class="description">{{ $solicitud->descripcion_falla }}</p>
    @if($solicitud->id_solicitud_padre)<div class="notice">Seguimiento vinculado al reporte base #{{ $solicitud->id_solicitud_padre }}.</div>@endif
    @if($solicitud->evidencia_url)<div class="evidence"><span>IMG</span><div>Evidencia inicial<small>Fotografía del reporte</small></div><a class="btn btn-secondary btn-small" href="{{ $solicitud->evidencia_url }}" target="_blank" rel="noopener">Ver imagen</a></div>@endif
</section>
