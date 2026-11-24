@php
    $baseLocal = rtrim((string) config('filesystems.disks.public.url'), '/');
    $rutaLocal = parse_url($baseLocal, PHP_URL_PATH);
    if ($rutaLocal && str_starts_with($url, $baseLocal.'/')) {
        $url = $rutaLocal.substr($url, strlen($baseLocal));
    }
@endphp
<figure class="evidence-photo">
    <button type="button" class="evidence-photo-trigger" data-evidence-open aria-haspopup="dialog">
        <img src="{{ $url }}" alt="" loading="lazy">
        <span>{{ $etiqueta }}</span>
    </button>
    <dialog class="evidence-viewer" aria-label="{{ $etiqueta }}">
        <div class="evidence-viewer-header">
            <strong>{{ $etiqueta }}</strong>
            <button type="button" class="button button-secondary" data-evidence-close autofocus>Cerrar imagen</button>
        </div>
        <img src="{{ $url }}" alt="{{ $etiqueta }}" loading="lazy">
    </dialog>
</figure>
