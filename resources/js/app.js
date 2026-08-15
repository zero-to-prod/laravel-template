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
