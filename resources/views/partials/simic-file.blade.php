<label>{{ $fileLabel ?? 'Evidencia fotográfica' }}
    <input type="file" name="{{ $fileName ?? 'evidencia' }}" accept="image/jpeg,image/png,image/webp" data-file-input @required($fileRequired ?? false)>
    <span class="muted" data-file-name>JPG, PNG o WEBP · Máximo 10 MB</span>
    <img class="file-preview" data-file-preview alt="Vista previa de la fotografía seleccionada" hidden>
</label>
