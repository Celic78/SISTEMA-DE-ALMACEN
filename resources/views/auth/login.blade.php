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
                @if ($registro)
                    <ul class="password-requirements" data-password-requirements aria-label="Requisitos de la contrase&#241;a">
                        <li data-requirement="length"><span aria-hidden="true">&#10007;</span><span>Mínimo 7 caracteres</span></li>
                        <li data-requirement="uppercase"><span aria-hidden="true">&#10007;</span><span>Al menos una letra may&#250;scula</span></li>
                        <li data-requirement="lowercase"><span aria-hidden="true">&#10007;</span><span>Al menos una letra min&#250;scula</span></li>
                        <li data-requirement="number"><span aria-hidden="true">&#10007;</span><span>Al menos un n&#250;mero</span></li>
                        <li data-requirement="special"><span aria-hidden="true">&#10007;</span><span>Al menos un car&#225;cter especial</span></li>
                        <li data-requirement="sequence"><span aria-hidden="true">&#10007;</span><span>Sin secuencias num&#233;ricas de 4 o m&#225;s d&#237;gitos</span></li>
                        <li data-requirement="repeated"><span aria-hidden="true">&#10007;</span><span>Sin 4 o m&#225;s d&#237;gitos iguales seguidos</span></li>
                    </ul>
                @endif
            @endunless
            @if ($registro || $restablecer)
                <div class="password-field">
                    <label for="access-password-confirmation">Confirma tu contraseña
                        <input id="access-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>
                    @include('partials.password-toggle', ['campo' => 'access-password-confirmation', 'etiqueta' => 'Mostrar confirmación de contraseña'])
                </div>
                @if ($registro)
                    <p class="password-match" data-password-match role="status" aria-live="polite"><span aria-hidden="true">&#10007;</span> Las contrase&#241;as deben coincidir</p>
                @endif
            @endif
            @if (request()->routeIs('login'))
                <p class="mb-4"><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>
            @endif
            <button class="button button-primary" type="submit">{{ $recuperacion ? 'Enviar enlace' : ($registro ? 'Registrarse' : ($restablecer ? $titulo : 'Entrar')) }}</button>
        </form>
        @if (request()->routeIs('login'))
            <dialog class="access-card" data-remember-dialog aria-labelledby="remember-title">
                <h2 id="remember-title">&#191;Quieres mantener tu sesi&#243;n iniciada en este dispositivo?</h2>
                <p>Podr&#225;s regresar a tu panel sin ingresar nuevamente tus credenciales. Se recomienda utilizar esta opci&#243;n &#250;nicamente en dispositivos personales o de confianza.</p>
                <nav aria-label="Preferencia de sesi&#243;n">
                    <button class="button button-primary" type="button" data-remember-yes>S&#237;, mantener mi sesi&#243;n iniciada.</button>
                    <button class="button button-secondary" type="button" data-remember-no>No, solo esta vez.</button>
                </nav>
            </dialog>
        @endif
        @unless ($mostrarPestanas)
            <nav aria-label="Opciones de acceso">
                <a href="{{ route('login') }}">Volver a inicio de sesión</a>
            </nav>
        @endunless
        </section>
    </div>
@endsection

@if ($registro)
    @push('scripts')
        <style>
            .password-requirements { margin: -.5rem 0 1rem; padding: 0; list-style: none; font-size: .8rem; }
            .password-requirements li, .password-match { display: flex; align-items: baseline; gap: .4rem; color: #B64D47; }
            .password-requirements li { margin: .2rem 0; }
            .password-requirements li[data-valid="true"], .password-match[data-valid="true"] { color: var(--positive); }
            .password-requirements li span:first-child, .password-match span:first-child { width: 1em; font-weight: 800; }
            .password-match { margin: -.5rem 0 1rem; font-size: .8rem; }
        </style>
        <script>
            (() => {
                const password = document.getElementById('access-password');
                const confirmation = document.getElementById('access-password-confirmation');
                const form = password?.form;
                const requirements = document.querySelector('[data-password-requirements]');
                const match = document.querySelector('[data-password-match]');
                if (!password || !confirmation || !form || !requirements || !match) return;

                const hasNumericSequence = (value) => /([0-9])\1{3,}/.test(value) ||
                    [...value.matchAll(/[0-9]+/g)].some(([run]) =>
                        [...run].some((digit, index) => {
                            const window = [...run].slice(index, index + 4).map(Number);
                            return window.length === 4 &&
                                (window.every((item, offset) => offset === 0 || item - window[offset - 1] === 1) ||
                                 window.every((item, offset) => offset === 0 || item - window[offset - 1] === -1));
                        }));

                const update = () => {
                    const value = password.value;
                    const checks = {
                        length: value.length >= 7 && value.length <= 20,
                        uppercase: /[A-Z]/.test(value),
                        lowercase: /[a-z]/.test(value),
                        number: /[0-9]/.test(value),
                        special: /[^A-Za-z0-9]/.test(value),
                        sequence: value.length > 0 && !hasNumericSequence(value),
                        repeated: value.length > 0 && !/([0-9])\1{3,}/.test(value)
                    };
                    Object.entries(checks).forEach(([name, valid]) => {
                        const item = requirements.querySelector(`[data-requirement="${name}"]`);
                        item.dataset.valid = String(valid);
                        item.querySelector('span').textContent = valid ? '✓' : '✕';
                    });
                    const passwordsMatch = password.value !== '' && password.value === confirmation.value;
                    match.dataset.valid = String(passwordsMatch);
                    match.querySelector('span').textContent = passwordsMatch ? '✓' : '✕';
                    match.lastChild.textContent = passwordsMatch ? ' Las contraseÃ±as coinciden' : ' Las contraseÃ±as deben coincidir';
                    Object.entries(checks).forEach(([name, valid]) => {
                        const item = requirements.querySelector(`[data-requirement="${name}"]`);
                        item.querySelector('span').textContent = valid ? String.fromCharCode(10003) : String.fromCharCode(10007);
                    });
                    match.querySelector('span').textContent = passwordsMatch ? String.fromCharCode(10003) : String.fromCharCode(10007);
                    match.lastChild.textContent = passwordsMatch
                        ? ` Las contrase${String.fromCharCode(241)}as coinciden`
                        : ` Las contrase${String.fromCharCode(241)}as deben coincidir`;
                    return Object.values(checks).every(Boolean) && passwordsMatch;
                };

                password.addEventListener('input', update);
                confirmation.addEventListener('input', update);
                form.addEventListener('submit', (event) => {
                    if (!update()) event.preventDefault();
                });
                update();
            })();
        </script>
    @endpush
@endif

@if (request()->routeIs('login'))
    @push('scripts')
        <script>
            (() => {
                const form = document.querySelector('form');
                const dialog = document.querySelector('[data-remember-dialog]');
                if (!form || !dialog) return;

                let preferenceConfirmed = false;
                const submitWithPreference = (remember) => {
                    preferenceConfirmed = true;
                    if (remember) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'remember';
                        input.value = '1';
                        form.appendChild(input);
                    }
                    dialog.close?.();
                    form.requestSubmit();
                };

                form.addEventListener('submit', (event) => {
                    if (preferenceConfirmed) return;
                    event.preventDefault();

                    if (typeof dialog.showModal === 'function') {
                        dialog.showModal();
                    } else if (window.confirm('¿Quieres mantener tu sesión iniciada en este dispositivo?')) {
                        submitWithPreference(true);
                    } else {
                        submitWithPreference(false);
                    }
                });

                dialog.querySelector('[data-remember-yes]')?.addEventListener('click', () => submitWithPreference(true));
                dialog.querySelector('[data-remember-no]')?.addEventListener('click', () => submitWithPreference(false));
            })();
        </script>
    @endpush
@endif
