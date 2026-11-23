<a href="{{ route('almacen.index') }}" @if (request()->routeIs('almacen.index') && ! request()->has('categoria')) aria-current="page" @endif>
    <span aria-hidden="true">▦</span> Vista General / Inventario
</a>
@foreach (\App\Models\SeccionAlmacen::CATEGORIAS as $clave => $categoriaInventario)
    <a href="{{ route('almacen.index', ['categoria' => $clave]) }}" @if (request()->routeIs('almacen.index') && request('categoria') === $clave) aria-current="page" @endif>
        <span aria-hidden="true">{{ ['insumos' => '▤', 'mobiliario' => '▣', 'herramientas' => '⚒', 'electricos' => 'ϟ'][$clave] }}</span>
        {{ $categoriaInventario['etiqueta'] }}
    </a>
@endforeach
<a href="{{ route('almacen.movimientos') }}" @if (request()->routeIs('almacen.movimientos')) aria-current="page" @endif>
    <span aria-hidden="true">⇄</span> Entradas / Salidas
</a>
<a href="{{ route('almacen.requisiciones') }}" @if (request()->routeIs('almacen.requisiciones')) aria-current="page" @endif>
    <span aria-hidden="true">▧</span> Reportes y Requisiciones
</a>
