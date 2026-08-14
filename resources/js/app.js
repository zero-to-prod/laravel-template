import './bootstrap';

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-dismiss-toast]');

    if (button) {
        button.closest('[data-toast]')?.remove();
    }

    const openDialog = event.target.closest('[data-delete-dialog-open]');

    if (openDialog) {
        document.getElementById(openDialog.dataset.deleteDialogOpen)?.showModal();
    }

    if (event.target.closest('[data-delete-dialog-close]')) {
        event.target.closest('[data-delete-dialog]')?.close();
    }
});

document.addEventListener('input', (event) => {
    if (! event.target.matches('[data-delete-confirm-input]')) {
        return;
    }

    const dialog = event.target.closest('[data-delete-dialog]');
    const submit = dialog?.querySelector('[data-delete-confirm-submit]');

    if (submit) {
        submit.disabled = event.target.value !== 'delete';
    }
});
