import './bootstrap';

/**
 * Dismiss a toast.
 *
 * Delegated from the document so it covers every toast on the page and any
 * that a later render adds, without a listener per toast.
 */
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-dismiss-toast]');

    if (button) {
        button.closest('[data-toast]')?.remove();
    }
});
