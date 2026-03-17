/*
 * manages modals in the application
 */

export class ModalManager {
    constructor() {
        this.modals = {};
    }

    register(id) {
        this.modals[id] = document.getElementById(id);
    }

    open(id) {
        if (this.modals[id]) this.modals[id].style.display = 'block';
    }

    close(id) {
        if (this.modals[id]) this.modals[id].style.display = 'none';
    }
}
