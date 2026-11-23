<div class="evidence-picker" data-evidence-picker>
    <label>{{ $etiqueta }}
        <input type="file" name="{{ $campo }}" accept="{{ $formatos }}" data-evidence-files
            @if ($multiple ?? false) multiple @else required @endif>
    </label>
    <button type="button" class="button button-secondary" data-open-camera hidden>Tomar foto</button>
    <p role="status" data-camera-status></p>
    <div class="evidence-previews" data-evidence-previews aria-label="Vista previa de fotografías"></div>
</div>

@pushOnce('scripts')
    <dialog class="evidence-camera" aria-labelledby="evidence-camera-title" data-camera-dialog>
        <h2 id="evidence-camera-title">Tomar foto</h2>
        <video autoplay playsinline muted></video>
        <div class="actions">
            <button type="button" class="button button-secondary" data-close-camera>Cancelar</button>
            <button type="button" class="button button-primary" data-take-photo disabled>Capturar foto</button>
        </div>
    </dialog>
    <script src="{{ asset('mantresc-evidencia.js') }}" defer></script>
@endPushOnce
