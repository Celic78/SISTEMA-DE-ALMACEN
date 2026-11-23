@extends('layouts.public')

@section('title', 'Acceso institucional')
@section('body-class', 'site-access')

@section('access-header')
    <button type="button" class="theme-button" data-theme-toggle aria-label="Tema oscuro" aria-pressed="false">
        <span>Claro</span>
        <span class="theme-track" aria-hidden="true"></span>
        <span>Oscuro</span>
    </button>
@endsection

@section('content')
    @php
        $registro = request()->routeIs('register');
        $recuperacion = request()->routeIs('password.request');
        $restablecer = request()->routeIs('password.reset');
        $mostrarPestanas = ! $recuperacion && ! $restablecer;
        $titulo = $registro ? 'Solicitar una cuenta' : ($recuperacion ? 'Recuperar contraseña' : ($restablecer ? 'Crear nueva contraseña' : 'Iniciar sesión'));
        $destino = $registro ? 'register.store' : ($recuperacion ? 'password.email' : ($restablecer ? 'password.update' : 'login.store'));
    @endphp
    <div class="access-layout">
        <a class="access-back" href="{{ route('home') }}">← Volver</a>
        @if ($errors->any())
            <ul class="form-message access-errors" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        <section class="access-welcome" aria-labelledby="access-welcome-title">
            <p class="eyebrow">SIRE-M</p>
            <h2 id="access-welcome-title">Bienvenido al<br>portal institucional.</h2>
            <p>Ingresa con tu cuenta institucional.</p>
            <div class="access-help">
                <h3>¿Necesitas ayuda?</h3>
                <p>Recupera el acceso con tu correo institucional.</p>
                <a class="button button-secondary" href="{{ route('password.request') }}">Ayuda de acceso</a>
            </div>
        </section>
        <section class="access-card" aria-labelledby="access-title">
        @if ($mostrarPestanas)
            <nav class="access-tabs" aria-label="Acceso institucional">
                <a href="{{ route('login') }}" @if (! $registro) aria-current="page" @endif>Inicio de sesión</a>
                <a href="{{ route('register') }}" @if ($registro) aria-current="page" @endif>Registrarse</a>
            </nav>
        @endif
        <h1 id="access-title">{{ $titulo }}</h1>
        @if ($registro)
            <p class="registration-notice">Solo los alumnos pueden registrarse. Los profesores, técnicos y el personal de almacén deben acudir con el coordinador para solicitar su cuenta.</p>
        @endif
        @if (session('status'))
            <p class="form-message" role="status">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route($destino) }}">
            @csrf
            @if ($restablecer)
                <input type="hidden" name="token" value="{{ request()->route('token') }}">
            @endif
            @if ($registro)
                <label>Nombre's <input name="nombres" value="{{ old('nombres') }}" autocomplete="given-name" maxlength="100" required></label>
                <label>Apellido paterno <input name="apellido_paterno" value="{{ old('apellido_paterno') }}" autocomplete="section-paterno family-name" maxlength="76" required></label>
                <label>Apellido materno <input name="apellido_materno" value="{{ old('apellido_materno') }}" autocomplete="section-materno family-name" maxlength="76" required></label>
            @endif
            <label>Correo institucional <input type="text" inputmode="email" name="email" value="{{ old('email', $restablecer ? request()->query('email') : '') }}" autocomplete="email" required></label>
            @unless ($recuperacion)
                <div class="password-field">
                    <label for="access-password">Contraseña
                        <input id="access-password" type="password" name="password" autocomplete="{{ $registro || $restablecer ? 'new-password' : 'current-password' }}" required>
                    </label>
                    @include('partials.password-toggle', ['campo' => 'access-password', 'etiqueta' => 'Mostrar contraseña'])
                </div>
            @endunless
            @if ($registro || $restablecer)
                <div class="password-field">
                    <label for="access-password-confirmation">Confirma tu contraseña
                        <input id="access-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>
                    @include('partials.password-toggle', ['campo' => 'access-password-confirmation', 'etiqueta' => 'Mostrar confirmación de contraseña'])
                </div>
            @endif
            @if (request()->routeIs('login'))
                <p class="mb-4"><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>
            @endif
            <button class="button button-primary" type="submit">{{ $recuperacion ? 'Enviar enlace' : ($registro ? 'Registrarse' : ($restablecer ? $titulo : 'Entrar')) }}</button>
        </form>
        @unless ($mostrarPestanas)
            <nav aria-label="Opciones de acceso">
                <a href="{{ route('login') }}">Volver a inicio de sesión</a>
            </nav>
        @endunless
        </section>
    </div>
@endsection
