import './bootstrap';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import { initStores, initKeyboardShortcuts } from './app-store';

// Make Sortable available globally
window.Sortable = Sortable;

// Custom Alpine directive for sortable lists
Alpine.directive('sortable', (el, { expression, modifiers }, { evaluate, effect }) => {
    const options = expression ? evaluate(expression) : {};

    const defaultOptions = {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        handle: modifiers.includes('handle') ? '.sortable-handle' : undefined,
        ...options
    };

    const sortable = Sortable.create(el, defaultOptions);

    // Cleanup on element removal
    el._sortable = sortable;
});

// Initialize Alpine.js stores
initStores(Alpine);

// Initialize keyboard shortcuts
initKeyboardShortcuts(Alpine);

// Initialize theme on load
document.addEventListener('DOMContentLoaded', () => {
    Alpine.store('theme').init();
    // Initialize notifications
    Alpine.store('notifications').init();
});

window.Alpine = Alpine;

Alpine.start();
