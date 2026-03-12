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
            <td>${emp.department_name}</td>
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
    const res = await fetch('../backend/api/salaries.php');
    const salaries = await res.json();

    const tbody = document.querySelector('#salary-table tbody');

    tbody.innerHTML = '';

    salaries.forEach((s) => {
        const row = document.createElement('tr');

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
        'Are you sure you want to apply department raises?',
    );

    if (!confirmAction) return;

    const res = await fetch('../backend/api/apply_raises.php', {
        method: 'POST',
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
    document.getElementById('employee-section').style.display = 'block';
    document.getElementById('salary-section').style.display = 'none';
}

function showSalaries() {
    document.getElementById('employee-section').style.display = 'none';
    document.getElementById('salary-section').style.display = 'block';
    loadSalaries();
}

async function logout() {
    const confirmAction = confirm('Are you sure you want to logout?');

    if (!confirmAction) return;

    try {
        const res = await fetch('../backend/api/auth/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const data = await res.json();

        if (res.ok) {
            alert(data.message);
            window.location.href = 'login.html'; // Redirect to login page
        } else {
            alert('Failed to logout. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while logging out.');
    }
}

async function loadSalaries() {
    const res = await fetch('../backend/api/salaries.php');
    const data = await res.json();

    const tbodyCurrent = document.querySelector('#salary-table tbody');
    tbodyCurrent.innerHTML = '';

    // Current salaries
    data.current.forEach((s) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${s.first_name} ${s.last_name}</td>
            <td>$${s.salary_amount}</td>
            <td>${s.effective_date}</td>
        `;

        tbodyCurrent.appendChild(row);
    });

    // Remove old history table if it exists
    let oldHistoryTable = document.querySelector('#salary-history-table');
    if (oldHistoryTable) oldHistoryTable.remove();

    // Only show history if there is data
    if (data.history.length > 0) {
        const section = document.getElementById('salary-section');

        const historyTitle = document.createElement('h3');
        historyTitle.textContent = 'Salary History';
        section.appendChild(historyTitle);

        const historyTable = document.createElement('table');
        historyTable.id = 'salary-history-table';
        historyTable.classList.add('data-table');

        historyTable.innerHTML = `
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Old Salary</th>
                    <th>New Salary</th>
                    <th>Change %</th>
                    <th>Change Date</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;

        section.appendChild(historyTable);

        const tbodyHistory = historyTable.querySelector('tbody');

        data.history.forEach((h) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${h.first_name} ${h.last_name}</td>
                <td>$${h.old_salary}</td>
                <td>$${h.new_salary}</td>
                <td>${h.change_percent != null ? h.change_percent + '%' : ''}</td>
                <td>${h.change_date}</td>
                <td>${h.change_reason || ''}</td>
            `;
            tbodyHistory.appendChild(row);
        });
    }
}

loadEmployees();
