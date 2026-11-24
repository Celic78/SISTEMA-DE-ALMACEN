(() => {
    const form = document.querySelector('[data-report-wizard]');
    if (!form) return;

    const steps = Array.from(form.querySelectorAll('[data-step]'));
    const previous = form.querySelector('[data-previous]');
    const next = form.querySelector('[data-next]');
    const submit = form.querySelector('[type="submit"]');
    let index = 0;

    const building = form.querySelector('[data-report-building]');
    const kind = form.querySelector('[data-report-space-kind]');
    const kindWrapper = form.querySelector('[data-report-space-kind-wrapper]');
    const space = form.querySelector('[data-report-space]');
    const spaceLabel = form.querySelector('[data-report-space-label]');
    const catalog = form.querySelector('[data-report-space-catalog]');
    const spaces = Array.from(catalog.content.querySelectorAll('option'));
    const kinds = new Map([['Aula', 'Aulas'], ['Laboratorio', 'Laboratorios'], ['Otro', 'Otros espacios']]);

    function group(option) {
        return kinds.has(option.dataset.kind) ? option.dataset.kind : 'Otro';
    }

    function showSpaces(selected = '') {
        const matching = spaces.filter(option =>
            option.dataset.building === building.value && group(option) === kind.value
        );
        spaceLabel.textContent = kind.value === 'Otro' ? 'Espacio' : kind.value || 'Espacio';
        const placeholder = matching.length ? 'Selecciona un espacio'
            : building.value ? 'Este edificio aún no tiene espacios' : 'Primero selecciona un edificio';
        space.replaceChildren(new Option(placeholder, ''));
        matching.forEach(option => space.add(new Option(option.dataset.spaceName, option.value)));
        space.value = matching.some(option => option.value === selected) ? selected : '';
    }

    function showKinds(selected = '') {
        const available = new Set(spaces.filter(option => option.dataset.building === building.value).map(group));
        kind.replaceChildren();
        for (const [value, label] of kinds) {
            if (available.has(value)) kind.add(new Option(label, value));
        }
        const current = spaces.find(option => option.value === selected && option.dataset.building === building.value);
        if (current) kind.value = group(current);
        kindWrapper.hidden = kind.options.length < 2;
        showSpaces(selected);
    }

    building.addEventListener('change', () => showKinds());
    kind.addEventListener('change', () => showSpaces());
    showKinds(space.dataset.selected);

    const calendar = form.querySelector('[data-report-calendar]');
    calendar?.addEventListener('change', event => {
        calendar.querySelector('[data-report-calendar-date]').textContent = event.target.dataset.dateLabel;
    });


    function showStep(focus) {
        steps.forEach((step, position) => { step.hidden = position !== index; });
        previous.hidden = index === 0;
        next.hidden = index === steps.length - 1;
        submit.hidden = index !== steps.length - 1;
        if (focus) steps[index].querySelector('input, select, textarea')?.focus();
    }

    next.addEventListener('click', () => {
        if (Array.from(steps[index].querySelectorAll('input, select, textarea')).every(field => field.reportValidity())) {
            index += 1;
            showStep(true);
        }
    });
    previous.addEventListener('click', () => {
        index -= 1;
        showStep(true);
    });
    form.addEventListener('invalid', event => {
        const position = steps.findIndex(step => step.contains(event.target));
        if (position !== -1 && steps[position].hidden) {
            index = position;
            showStep(false);
        }
    }, true);

    showStep(false);
})();
