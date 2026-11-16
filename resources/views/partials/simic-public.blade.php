@extends('layouts.simic')
@section('title', isset($help) ? 'Ayuda y soporte' : 'Mantenimiento escolar')
@section('content')
@if(isset($help))
    <section class="public-hero"><div><span class="eyebrow">ACCESO INSTITUCIONAL</span><h1>Ayuda y soporte.</h1><p>Consulta al responsable de mantenimiento o al administrador de cuentas de tu plantel.</p><a class="btn" href="{{ route('login') }}">Volver al acceso</a></div><div class="panel"><h2>¿Necesitas una cuenta o recuperar tu contraseña?</h2><p>Solicita el alta o el restablecimiento al administrador institucional. Debe verificar tu identidad y el perfil autorizado.</p><p>SIMIC no envía correos de recuperación en esta instalación. Nunca compartas tu contraseña.</p><h3>¿Tienes una incidencia?</h3><p>Inicia sesión y usa Nuevo reporte. Consulta el avance desde Mis reportes.</p></div></section>
@else
    <section class="public-hero"><div><span class="eyebrow">RECURSOS MATERIALES Y MANTENIMIENTO</span><h1>Un campus cuidado.<br>Una atención con<br>seguimiento.</h1><p>Reporta fallas de infraestructura y consulta el avance de su atención desde un mismo lugar.</p><a class="btn" href="{{ route(auth()->check() ? 'dashboard' : 'login') }}">{{ auth()->check() ? 'Ir a mi espacio' : 'Iniciar sesión' }} <span aria-hidden="true">↗</span></a></div><div class="service-card"><span class="eyebrow">SIMIC</span><h2>Del reporte a la solución</h2><ol><li><span>01</span>Registra la ubicación y la falla.</li><li><span>02</span>Adjunta evidencia fotográfica.</li><li><span>03</span>Consulta el estado de tu reporte.</li></ol><small>Acceso con correo institucional</small></div></section>
    <section class="panel public-notice"><h2>Avisos de mantenimiento</h2><p>Inicia sesión para reportar o dar seguimiento a una incidencia del campus.</p></section>
@endif
@endsection
