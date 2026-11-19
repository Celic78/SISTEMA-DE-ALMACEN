(() => {
    const form = document.querySelector('[data-report-wizard]');
    if (!form) return;

    const steps = Array.from(form.querySelectorAll('[data-step]'));
    const previous = form.querySelector('[data-previous]');
    const next = form.querySelector('[data-next]');
    const submit = form.querySelector('[type="submit"]');
    let index = 0;

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
