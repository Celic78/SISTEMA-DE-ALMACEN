document.addEventListener('click', event => {
    const control = event.target.closest('[data-evidence-open], [data-evidence-close]');
    const dialog = control?.closest('.evidence-photo')?.querySelector('dialog');
    if (!dialog) return;

    if (control.hasAttribute('data-evidence-close')) {
        dialog.close();
    } else if (!dialog.open) {
        dialog.showModal();
    }
});
