(() => {
    const root = document.documentElement;
    const preference = window.matchMedia('(prefers-color-scheme: dark)');
    const savedTheme = () => {
        try { return localStorage.getItem('mantresc-theme'); } catch { return null; }
    };

    function applyTheme(theme) {
        root.dataset.theme = theme || (preference.matches ? 'dark' : 'light');
        const toggle = document.querySelector('[data-theme-toggle]');
        if (toggle) {
            if (toggle.hasAttribute('aria-pressed')) {
                toggle.setAttribute('aria-pressed', String(root.dataset.theme === 'dark'));
            } else {
                toggle.setAttribute('aria-label', root.dataset.theme === 'dark' ? 'Activar tema claro' : 'Activar tema oscuro');
            }
        }
    }

    applyTheme(savedTheme());
    preference.addEventListener('change', () => applyTheme(savedTheme()));
    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(savedTheme());
        document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
            const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
            try { localStorage.setItem('mantresc-theme', nextTheme); } catch {}
            applyTheme(nextTheme);
        });
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-password-toggle]');
    if (!toggle) return;

    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!input) return;

    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(visible));
    });
});
