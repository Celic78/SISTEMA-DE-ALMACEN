@if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
@if (session('error'))<div class="notice notice-error" role="alert">{{ session('error') }}</div>@endif
@if ($errors->any())<div class="notice notice-error" role="alert"><strong>Revisa los datos para continuar.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
