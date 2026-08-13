import './bootstrap';

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-dismiss-toast]');

    if (button) {
        button.closest('[data-toast]')?.remove();
    }
});
