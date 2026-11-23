<a href="{{ route('almacen.index') }}" @if (request()->routeIs('almacen.index') && ! request()->has('categoria')) aria-current="page" @endif>
    Vista General / Inventario
</a>
@foreach (\App\Models\SeccionAlmacen::CATEGORIAS as $clave => $categoriaInventario)
    <a href="{{ route('almacen.index', ['categoria' => $clave]) }}" @if (request()->routeIs('almacen.index') && request('categoria') === $clave) aria-current="page" @endif>
        {{ $categoriaInventario['etiqueta'] }}
    </a>
@endforeach
<a href="{{ route('almacen.movimientos') }}" @if (request()->routeIs('almacen.movimientos')) aria-current="page" @endif>
    Entradas / Salidas
</a>
<a href="{{ route('almacen.requisiciones') }}" @if (request()->routeIs('almacen.requisiciones')) aria-current="page" @endif>
    Reportes y Requisiciones
</a>
