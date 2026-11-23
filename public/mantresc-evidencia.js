(() => {
    const dialog = document.querySelector('[data-camera-dialog]');
    if (!dialog) return;

    const video = dialog.querySelector('video');
    const capture = dialog.querySelector('[data-take-photo]');
    const close = dialog.querySelector('[data-close-camera]');
    let activeSession;

    function stopCamera() {
        activeSession?.stream?.getTracks().forEach(track => track.stop());
        activeSession = undefined;
        video.srcObject = null;
        capture.disabled = true;
    }

    close.addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', stopCamera);
    window.addEventListener('pagehide', () => {
        stopCamera();
        if (dialog.open) dialog.close();
    });

    function updatePreview(input, preview) {
        for (const image of preview.querySelectorAll('img')) URL.revokeObjectURL(image.src);
        preview.replaceChildren();
        for (const file of input.files) {
            if (!file.type.startsWith('image/')) continue;
            const image = new Image();
            image.src = URL.createObjectURL(file);
            image.alt = 'Vista previa de ' + file.name;
            preview.append(image);
        }
    }

    function attachPhoto(input, file, notice) {
        const selected = input.multiple ? [...input.files] : [];
        if (selected.length >= 5) {
            notice.textContent = 'Puedes adjuntar hasta cinco archivos. Selecciona menos archivos para tomar otra fotografía.';
            return;
        }
        const transfer = new DataTransfer();
        for (const existing of selected) transfer.items.add(existing);
        transfer.items.add(file);
        input.files = transfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    document.querySelectorAll('[data-evidence-picker]').forEach(picker => {
        const files = picker.querySelector('[data-evidence-files]');
        const notice = picker.querySelector('[data-camera-status]');
        const preview = picker.querySelector('[data-evidence-previews]');
        const button = picker.querySelector('[data-open-camera]');

        files.addEventListener('change', () => {
            notice.textContent = '';
            updatePreview(files, preview);
        });
        if (typeof DataTransfer !== 'function') return;
        button.hidden = false;

        const nativeCamera = document.createElement('input');
        nativeCamera.type = 'file';
        nativeCamera.accept = 'image/jpeg,image/png,image/webp';
        nativeCamera.setAttribute('capture', 'environment');
        nativeCamera.hidden = true;
        picker.append(nativeCamera);
        nativeCamera.addEventListener('change', () => {
            if (nativeCamera.files.length) attachPhoto(files, nativeCamera.files[0], notice);
            nativeCamera.value = '';
        });

        button.addEventListener('click', async () => {
            if (files.multiple && files.files.length >= 5) {
                notice.textContent = 'Puedes adjuntar hasta cinco archivos.';
                return;
            }
            if (!navigator.mediaDevices?.getUserMedia || typeof dialog.showModal !== 'function' || matchMedia('(pointer: coarse)').matches) {
                nativeCamera.click();
                return;
            }

            notice.textContent = '';
            const session = { files, notice };
            activeSession = session;
            dialog.showModal();
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
                if (activeSession !== session || !dialog.open) {
                    stream.getTracks().forEach(track => track.stop());
                    return;
                }
                session.stream = stream;
                video.srcObject = stream;
                await video.play();
                if (activeSession === session && dialog.open) capture.disabled = false;
            } catch {
                if (activeSession !== session) return;
                dialog.close();
                notice.textContent = 'No se pudo abrir la cámara. Revisa los permisos del navegador o elige una imagen guardada.';
            }
        });
    });

    capture.addEventListener('click', () => {
        const session = activeSession;
        if (!session || !video.videoWidth) return;

        capture.disabled = true;
        const canvas = document.createElement('canvas');
        const scale = Math.min(1, 1600 / Math.max(video.videoWidth, video.videoHeight));
        canvas.width = Math.round(video.videoWidth * scale);
        canvas.height = Math.round(video.videoHeight * scale);
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(blob => {
            if (activeSession !== session || !dialog.open) return;
            dialog.close();
            if (blob) {
                attachPhoto(session.files, new File([blob], 'evidencia-' + Date.now() + '.jpg', { type: 'image/jpeg' }), session.notice);
            } else {
                session.notice.textContent = 'No se pudo guardar la captura. Inténtalo de nuevo.';
            }
        }, 'image/jpeg', 0.88);
    });
})();
