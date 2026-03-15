/*
 * manages dashboard UI sections
 */

export class DashboardUI {
    constructor(sections) {
        this.sections = sections; // object { employees: element, salary: element, audit: element }
    }

    show(sectionId) {
        Object.keys(this.sections).forEach(id => {
            this.sections[id].style.display = id === sectionId ? 'block' : 'none';
        });
    }
}