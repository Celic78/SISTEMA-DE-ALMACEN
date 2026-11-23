(() => {
    const button = document.querySelector('.workspace-menu-toggle');
    if (!button) return;

    const menu = document.getElementById(button.getAttribute('aria-controls'));
    if (!menu) return;

    const compact = window.matchMedia('(max-width: 900px)');

    function setExpanded(expanded) {
        button.setAttribute('aria-expanded', String(expanded));
        menu.hidden = !expanded;
    }

    function updateLayout() {
        const focusInsideMenu = menu.contains(document.activeElement);
        button.hidden = !compact.matches;
        setExpanded(!compact.matches || focusInsideMenu);
    }

    button.addEventListener('click', () => setExpanded(menu.hidden));
    menu.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || !compact.matches) return;
        setExpanded(false);
        button.focus();
    });

    compact.addEventListener('change', updateLayout);
    updateLayout();
})();
