async function loadEmployees() {
    const res = await fetch('../backend/api/dashboard.php');
    const employees = await res.json();

    const tbody = document.querySelector('#employee-table tbody');

    tbody.innerHTML = '';

    employees.forEach((emp) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${emp.first_name} ${emp.last_name}</td>
            <td>${emp.email}</td>
            <td>${emp.department_id}</td>
            <td>
                <button onclick="editEmployee(${emp.employee_id})">
                    Edit
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });
}

function editEmployee(id) {
    const confirmChange = confirm('Are you sure you want to make this change?');

    if (!confirmChange) return;

    // TODO: Add in call to update API. Task DEV-18
}

loadEmployees();
