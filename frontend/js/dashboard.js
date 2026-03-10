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

async function loadSalaries() {

    const res = await fetch("../backend/api/salaries.php");
    const salaries = await res.json();

    const tbody = document.querySelector("#salary-table tbody");

    tbody.innerHTML = "";

    salaries.forEach(s => {

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${s.first_name} ${s.last_name}</td>
            <td>$${s.salary_amount}</td>
            <td>${s.effective_date}</td>
        `;

        tbody.appendChild(row);
    });
}

async function applyRaises() {

    const confirmAction = confirm(
        "Are you sure you want to apply department raises?"
    );

    if (!confirmAction) return;

    const res = await fetch("../backend/api/apply_raises.php", {
        method: "POST"
    });

    const data = await res.json();

    alert(data.message);

    loadSalaries();
}

function editEmployee(id) {
    const confirmChange = confirm('Are you sure you want to make this change?');

    if (!confirmChange) return;

    // TODO: Add in call to update API. Task DEV-18
}

function showEmployees() {
    document.getElementById("employee-section").style.display = "block";
    document.getElementById("salary-section").style.display = "none";
}

function showSalaries() {
    document.getElementById("employee-section").style.display = "none";
    document.getElementById("salary-section").style.display = "block";
    loadSalaries();
}

loadEmployees();
