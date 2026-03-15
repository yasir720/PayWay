/*
 * renders data tables in the dashboard
 */

export class TableRenderer {
    constructor(selector) {
        this.tableBody = document.querySelector(`${selector} tbody`);
    }

    clear() {
        this.tableBody.innerHTML = '';
    }

    renderRows(data, rowMapper) {
        this.clear();
        data.forEach((item) => {
            const row = document.createElement('tr');
            row.innerHTML = rowMapper(item);
            this.tableBody.appendChild(row);
        });
    }
}
