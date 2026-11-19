@extends('layouts.public')

@section('title', 'Servicios')

@section('content')
    <section class="services-page" aria-labelledby="servicios-titulo">
        <h1 id="servicios-titulo">Servicios</h1>
        <div class="service-grid">
            <article><h3>Mantenimiento Eléctrico y Electrónico</h3><p>Atención de fallas en iluminación, contactos, proyectores y equipos escolares.</p></article>
            <article><h3>Plomería e Hidrosanitario</h3><p>Revisión de fugas, sanitarios, tuberías y desagües.</p></article>
            <article><h3>Mobiliario e Inmuebles</h3><p>Reparación de mesas, sillas, puertas y accesorios de las instalaciones.</p></article>
            <article><h3>Mantenimiento Preventivo Programado</h3><p>Inspecciones y servicios periódicos para conservar las instalaciones.</p></article>
            <article><h3>Gestión de Almacén</h3><p>Control de inventario, solicitudes, entregas y devoluciones de materiales.</p></article>
            <article><h3>Atención de Emergencias</h3><p>Recepción y priorización de incidencias urgentes en el plantel.</p></article>
        </div>
    </section>
@endsection
