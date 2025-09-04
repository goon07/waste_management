import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    console.log('app.js loaded');
    console.log('Alpine version:', Alpine.version);
});