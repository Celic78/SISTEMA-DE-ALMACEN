const panel = document.querySelector('[data-live-panel]');

if (panel) {
    let refreshing = false;
    let editing = false;

    panel.addEventListener('input', () => { editing = true; });
    panel.addEventListener('change', event => {
        const toggle = event.target;
        if (!toggle.matches('.assignment-option input[type="checkbox"]')) return;

        const field = toggle.closest('.assignment-option')?.querySelector('.assignment-option-field :is(input, select)');
        if (!field) return;

        field.disabled = !toggle.checked;
        field.required = toggle.checked;
    });

    window.setInterval(async () => {
        if (document.hidden || refreshing || editing || panel.contains(document.activeElement)) return;

        refreshing = true;
        try {
            const response = await fetch(window.location.href, { cache: 'no-store' });
            if (!response.ok || response.redirected) return;

            const documentUpdate = new DOMParser().parseFromString(await response.text(), 'text/html');
            const updatedPanel = documentUpdate.querySelector('[data-live-panel]');
            if (updatedPanel && updatedPanel.innerHTML !== panel.innerHTML) {
                panel.replaceChildren(...updatedPanel.childNodes);
            }
        } catch {
            // Keep the last confirmed state when a refresh fails.
        } finally {
            refreshing = false;
        }
    }, 15000);
}
