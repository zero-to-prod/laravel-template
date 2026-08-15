import './bootstrap';

const dismissToast = (toast) => {
    if (! toast || toast.dataset.dismissing !== undefined) {
        return;
    }

    toast.dataset.dismissing = '';
    toast.classList.add('translate-y-2', 'opacity-0');
    toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    setTimeout(() => toast.remove(), 300);
};

const syncAbilityColumn = (table, verb) => {
    const toggles = [...table.querySelectorAll(`[data-ability-verb="${verb}"]`)];
    const checked = toggles.length > 0 && toggles.every((toggle) => toggle.checked);

    table.querySelectorAll(`[data-ability-column="${verb}"]`).forEach((button) => {
        button.setAttribute('aria-pressed', String(checked));
    });
};

document.querySelectorAll('[data-ability-column]').forEach((button) => {
    syncAbilityColumn(button.closest('table'), button.dataset.abilityColumn);
});

document.querySelectorAll('[data-toast][data-autodismiss]').forEach((toast) => {
    setTimeout(() => dismissToast(toast), Number(toast.dataset.autodismiss));
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-dismiss-toast]');

    if (button) {
        dismissToast(button.closest('[data-toast]'));
    }

    const openDialog = event.target.closest('[data-delete-dialog-open]');

    if (openDialog) {
        document.getElementById(openDialog.dataset.deleteDialogOpen)?.showModal();
    }

    if (event.target.closest('[data-delete-dialog-close]')) {
        event.target.closest('[data-delete-dialog]')?.close();
    }

    const abilityColumn = event.target.closest('[data-ability-column]');

    if (abilityColumn) {
        const table = abilityColumn.closest('table');
        const verb = abilityColumn.dataset.abilityColumn;
        const toggles = [...table.querySelectorAll(`[data-ability-verb="${verb}"]`)];
        const checked = ! toggles.every((toggle) => toggle.checked);

        toggles.forEach((toggle) => {
            toggle.checked = checked;
        });

        syncAbilityColumn(table, verb);
    }
});

document.addEventListener('input', (event) => {
    if (event.target.matches('[data-ability-verb]')) {
        syncAbilityColumn(event.target.closest('table'), event.target.dataset.abilityVerb);
    }

    if (event.target.matches('[data-delete-confirm-input]')) {
        const dialog = event.target.closest('[data-delete-dialog]');
        const submit = dialog?.querySelector('[data-delete-confirm-submit]');

        if (submit) {
            submit.disabled = event.target.value !== 'delete';
        }
    }
});
