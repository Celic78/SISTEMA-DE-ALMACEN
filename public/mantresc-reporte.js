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
        space.dispatchEvent(new Event('change'));
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

    const assets = form.querySelector('[data-report-assets]');
    const assetName = assets.querySelector('[data-report-asset-name]');
    const unit = assets.querySelector('[data-report-asset-unit]');
    const unitWrapper = assets.querySelector('[data-report-asset-unit-wrapper]');
    const other = assets.querySelector('[data-report-asset-other]');
    const otherFields = Array.from(other.querySelectorAll('input'));
    const status = assets.querySelector('[data-report-asset-status]');
    const otherValue = '__otro__';
    let inventory = [];
    let loadId = 0;

    function showAssetUnits(selected = '') {
        const isOther = assetName.value === otherValue;
        other.hidden = !isOther;
        otherFields.forEach(input => { input.disabled = !isOther; });
        otherFields[0].required = isOther;

        const matches = inventory.filter(item => item.nombre === assetName.value);
        unitWrapper.hidden = matches.length === 0;
        unit.disabled = matches.length === 0;
        unit.required = matches.length > 0;
        unit.replaceChildren(new Option('Selecciona el número o código', ''));
        matches.forEach(item => unit.add(new Option(`${item.codigo_inventario} · ${item.ubicacion_interna}`, item.id)));
        if (matches.length === 1) unit.value = unit.options[1].value;
        if (matches.some(item => String(item.id) === selected)) unit.value = selected;
    }

    async function loadAssets(selectedName = '', selectedUnit = '') {
        const requestId = ++loadId;
        inventory = [];
        status.hidden = true;
        assetName.replaceChildren(new Option(space.value ? 'Cargando inventario…' : 'Primero selecciona un espacio', ''));
        showAssetUnits();
        if (!space.value) return;

        try {
            const response = await fetch(assets.dataset.url.replace('__espacio__', encodeURIComponent(space.value)), {
                headers: { Accept: 'application/json' }
            });
            if (!response.ok || response.redirected) throw new Error('Inventario no disponible');
            const items = await response.json();
            if (requestId !== loadId) return;
            inventory = items;
        } catch {
            if (requestId !== loadId) return;
            status.textContent = 'No se pudo consultar el inventario. Puedes escribir el nombre y número del bien.';
            status.hidden = false;
        }

        assetName.replaceChildren(new Option('Selecciona el bien afectado', ''));
        for (const name of new Set(inventory.map(item => item.nombre))) assetName.add(new Option(name, name));
        assetName.add(new Option('Otro bien o elemento no inventariado', otherValue));
        if (Array.from(assetName.options).some(option => option.value === selectedName)) assetName.value = selectedName;
        showAssetUnits(selectedUnit);
    }

    space.addEventListener('change', () => loadAssets());
    assetName.addEventListener('change', () => showAssetUnits());
    loadAssets(assetName.dataset.selected, unit.dataset.selected);


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
