@extends('layouts.public')

@section('title', 'Crea tu contraseña')

@section('content')
    <section class="access-card" aria-labelledby="initial-password-title">
        <h1 id="initial-password-title">Crea tu contraseña</h1>
        <p>Para continuar al panel, sustituye la contraseña temporal por una personal de al menos 8 caracteres.</p>
        @include('partials.alerts')
        <form method="POST" action="{{ route('password.initial.update') }}">
            @csrf
            <label>Nueva contraseña <input type="password" name="password" autocomplete="new-password" minlength="8" maxlength="20" required></label>
            <label>Confirma tu nueva contraseña <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" maxlength="20" required></label>
            <button class="button button-primary">Guardar contraseña</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="button button-secondary">Cerrar sesión</button>
        </form>
    </section>
@endsection
