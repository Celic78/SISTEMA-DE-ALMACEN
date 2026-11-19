@extends('layouts.simic')
@section('title', 'Acceso al sistema')
@section('content')
<section class="public-hero login-hero">
    <div><span class="eyebrow">SIMIC</span><h1>Bienvenido al<br>portal institucional.</h1><p>Ingresa con tu cuenta y el rol que tienes asignado.</p><div class="login-help"><h3>¿Necesitas ayuda?</h3><p>Consulta las indicaciones de soporte de tu institución.</p><a class="btn btn-secondary" href="{{ route('help') }}">Ayuda de acceso</a></div></div>
    <form class="panel form-stack" method="POST" action="{{ route('login.store') }}">@csrf
        <h2>Iniciar sesión</h2>
        <label>Rol institucional *<select name="rol" required><option value="">Selecciona tu rol</option>@foreach(['alumno' => 'Alumno / Solicitante', 'profesor' => 'Profesor / Solicitante', 'coordinador' => 'Coordinador General', 'tecnico' => 'Técnico', 'almacen' => 'Encargado de Almacén'] as $value => $label)<option value="{{ $value }}" @selected(old('rol') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>Correo institucional *<input type="email" name="email" value="{{ old('email') }}" placeholder="nombre@dominio-institucional" autocomplete="username" required></label>
        <label>Contraseña *<span class="password-field"><input type="password" name="password" autocomplete="current-password" required id="login-password"><button type="button" data-toggle-password aria-controls="login-password" aria-label="Mostrar contraseña">Ver</button></span></label>
        <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))>Mantener sesión activa</label>
        <button class="btn" type="submit">Ingresar <span aria-hidden="true">→</span></button>
        <p class="muted">El dominio del correo debe corresponder al rol seleccionado.</p><a class="text-link" href="{{ route('help') }}">Alta de cuenta y recuperación de acceso</a>
    </form>
</section>
@endsection
