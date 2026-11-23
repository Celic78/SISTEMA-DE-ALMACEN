@php
    $baseLocal = rtrim((string) config('filesystems.disks.public.url'), '/');
    $rutaLocal = parse_url($baseLocal, PHP_URL_PATH);
    if ($rutaLocal && str_starts_with($url, $baseLocal.'/')) {
        $url = $rutaLocal.substr($url, strlen($baseLocal));
    }
@endphp
<figure class="evidence-photo">
    <a href="{{ $url }}" target="_blank" rel="noopener">
        <img src="{{ $url }}" alt="{{ $etiqueta }}" loading="lazy">
        <span>{{ $etiqueta }}</span>
    </a>
</figure>
