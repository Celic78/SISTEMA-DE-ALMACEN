@extends('layouts.private')

@section('title', 'Mi perfil')

@section('content')
    <section class="access-card">
        <h1>Mi perfil</h1>
        <dl>
            <dt>Nombre</dt>
            <dd>{{ auth()->user()->name }}</dd>
            <dt>Correo institucional</dt>
            <dd>{{ auth()->user()->email }}</dd>
        </dl>
    </section>
@endsection
