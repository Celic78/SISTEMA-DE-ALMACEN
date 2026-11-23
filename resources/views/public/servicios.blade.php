@extends('layouts.public')

@section('title', 'Servicios')

@section('content')
    <section class="services-page" aria-labelledby="servicios-titulo">
        <h1 id="servicios-titulo">Mantenimiento de inmuebles</h1>
        <div class="service-grid">
            <article>
                <h3>Aulas y oficinas</h3>
                <p>Reporta daños en puertas, ventanas, pisos, muros y otros elementos fijos del edificio.</p>
            </article>
            <article>
                <h3>Pasillos, accesos y áreas comunes</h3>
                <p>Informa sobre escalones dañados, barandales flojos o deterioro en los accesos y las zonas de circulación.</p>
            </article>
            <article>
                <h3>Mantenimiento preventivo</h3>
                <p>Consulta avisos sobre inspecciones y trabajos programados en los inmuebles del plantel.</p>
            </article>
        </div>
    </section>
@endsection
