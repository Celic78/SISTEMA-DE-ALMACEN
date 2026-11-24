<div class="grid gap-3 sm:grid-cols-2">
    <label class="grid gap-1">Código de inventario
        <input class="rounded border p-2" name="codigo_inventario" maxlength="60" value="{{ old('codigo_inventario', $bien->codigo_inventario) }}" placeholder="Ej. E2-A04-015" required>
    </label>
    <label class="grid gap-1">Nombre del bien
        <input class="rounded border p-2" name="nombre" maxlength="120" value="{{ old('nombre', $bien->nombre) }}" placeholder="Ej. Mesa de trabajo" required>
    </label>
    <label class="grid gap-1">Tipo
        <input class="rounded border p-2" name="tipo" maxlength="80" value="{{ old('tipo', $bien->tipo) }}" placeholder="Ej. Mobiliario o instalación fija" required>
    </label>
    <label class="grid gap-1">Ubicación exacta dentro del espacio
        <input class="rounded border p-2" name="ubicacion_interna" maxlength="150" value="{{ old('ubicacion_interna', $bien->ubicacion_interna) }}" placeholder="Ej. Pared norte, junto a la ventana" required>
    </label>
    <label class="grid gap-1">Estado
        <select class="rounded border p-2" name="estado" required>
            @foreach (\App\Models\BienEspacio::ESTADOS as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected(old('estado', $bien->estado) === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </label>
    <label class="grid gap-1">Marca (opcional)
        <input class="rounded border p-2" name="marca" maxlength="80" value="{{ old('marca', $bien->marca) }}">
    </label>
    <label class="grid gap-1">Modelo (opcional)
        <input class="rounded border p-2" name="modelo" maxlength="80" value="{{ old('modelo', $bien->modelo) }}">
    </label>
    <label class="grid gap-1">Número de serie (opcional)
        <input class="rounded border p-2" name="numero_serie" maxlength="80" value="{{ old('numero_serie', $bien->numero_serie) }}">
    </label>
    <label class="grid gap-1 sm:col-span-2">Observaciones (opcional)
        <textarea class="rounded border p-2" name="observaciones" rows="3" maxlength="1500">{{ old('observaciones', $bien->observaciones) }}</textarea>
    </label>
</div>