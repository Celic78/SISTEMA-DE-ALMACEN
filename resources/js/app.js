const root = document.documentElement;
function syncTheme() {
    const dark = root.dataset.theme === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
        button.setAttribute('aria-pressed', String(dark));
        button.setAttribute('aria-label', dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
    });
    document.querySelectorAll('[data-theme-label]').forEach(label => { label.textContent = dark ? 'Oscuro' : 'Claro'; });
}
document.querySelectorAll('[data-theme-toggle]').forEach(button => button.addEventListener('click', () => {
    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    try { localStorage.setItem('simic-theme', root.dataset.theme); } catch (_) {}
    syncTheme();
}));
syncTheme();
const menuButton = document.querySelector('[data-open-menu]');
const shade = document.querySelector('[data-close-menu]');
function setMenu(open) {
    document.body.classList.toggle('menu-open', open);
    menuButton?.setAttribute('aria-expanded', String(open));
    if (shade) shade.hidden = !open;
}
menuButton?.addEventListener('click', () => setMenu(!document.body.classList.contains('menu-open')));
shade?.addEventListener('click', () => setMenu(false));
document.addEventListener('keydown', event => { if (event.key === 'Escape') { setMenu(false); menuButton?.focus(); } });
document.querySelector('[data-toggle-password]')?.addEventListener('click', event => {
    const input = document.getElementById('login-password');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    event.currentTarget.textContent = show ? 'Ocultar' : 'Ver';
    event.currentTarget.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
});
const building = document.querySelector('[data-building]');
const space = document.querySelector('[data-space]');
if (building && space) {
    const options = [...space.options].map(option => option.cloneNode(true));
    const updateSpaces = () => {
        const selected = space.value;
        space.replaceChildren(...options.filter(option => !option.value || option.dataset.building === building.value).map(option => option.cloneNode(true)));
        if ([...space.options].some(option => option.value === selected)) space.value = selected;
        space.disabled = !building.value;
    };
    building.addEventListener('change', updateSpaces);
    updateSpaces();
}
document.querySelectorAll('[data-file-input]').forEach(input => {
    let url;
    input.addEventListener('change', () => {
        const preview = input.parentElement.querySelector('[data-file-preview]');
        const label = input.parentElement.querySelector('[data-file-name]');
        const file = input.files[0];
        if (url) URL.revokeObjectURL(url);
        if (label) label.textContent = file ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(1)} MB` : 'JPG, PNG o WEBP · Máximo 10 MB';
        if (preview) { preview.hidden = !file || !file.type.startsWith('image/'); if (!preview.hidden) { url = URL.createObjectURL(file); preview.src = url; } }
    });
});
document.querySelectorAll('[data-material-select]').forEach(select => {
    const update = () => {
        const option = select.selectedOptions[0];
        document.querySelectorAll('[data-material-detail]').forEach(element => {
            element.textContent = option?.dataset[element.dataset.materialDetail] || '—';
        });
    };
    select.addEventListener('change', update);
    update();
});
document.querySelectorAll('form[method=POST]').forEach(form => form.addEventListener('submit', event => {
    if (form.dataset.submitting) { event.preventDefault(); return; }
    form.dataset.submitting = 'true';
    form.querySelectorAll('button[type=submit]').forEach(button => { button.setAttribute('aria-disabled', 'true'); });
}));
document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => window.print()));
