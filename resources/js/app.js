

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if (import.meta.env.PROD && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch((error) => {
            console.error('PMS service worker registration failed.', error);
        });
    });
}
