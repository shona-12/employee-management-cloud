<?php
require "db.php";

$search = trim($_GET['search'] ?? '');
$department = trim($_GET['department'] ?? '');
$status = trim($_GET['status'] ?? '');

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(name LIKE ? OR email LIKE ? OR role LIKE ? OR department LIKE ?)";
    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
    $types = "ssss";
}

if ($department !== '') {
    $where[] = "department = ?";
    $params[] = $department;
    $types .= "s";
}

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= "s";
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT * FROM employees $whereSql ORDER BY id DESC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$total = $conn->query(
    "SELECT COUNT(*) AS c FROM employees"
)->fetch_assoc()['c'];

$active = $conn->query(
    "SELECT COUNT(*) AS c FROM employees WHERE status='Active'"
)->fetch_assoc()['c'];

$inactive = $conn->query(
    "SELECT COUNT(*) AS c FROM employees WHERE status='Inactive'"
)->fetch_assoc()['c'];

$departments = $conn->query(
    "SELECT COUNT(DISTINCT department) AS c FROM employees"
)->fetch_assoc()['c'];

$recent = $conn->query(
    "SELECT COUNT(*) AS c FROM employees
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
)->fetch_assoc()['c'];

$departmentResult = $conn->query(
    "SELECT department, COUNT(*) AS total
     FROM employees
     GROUP BY department
     ORDER BY total DESC"
);

$departmentList = [];

while ($d = $departmentResult->fetch_assoc()) {
    $departmentList[] = $d;
}

$filterDepartments = $conn->query(
    "SELECT DISTINCT department
     FROM employees
     WHERE department IS NOT NULL
     AND department <> ''
     ORDER BY department"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Employee Hub | Dashboard</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Inter, Arial, sans-serif;
    background: #f5f7fb;
    color: #172033;
}

/* SIDEBAR */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 245px;
    height: 100vh;
    background: linear-gradient(180deg, #071b41, #0b2b63);
    color: white;
    padding: 25px 18px;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 5px 10px 30px;
    border-bottom: 1px solid rgba(255,255,255,.12);
}

.logo-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.logo h2 {
    margin: 0;
    font-size: 20px;
}

.nav {
    margin-top: 28px;
}

.nav a {
    display: flex;
    align-items: center;
    gap: 14px;
    color: #dbeafe;
    text-decoration: none;
    padding: 14px 15px;
    margin-bottom: 7px;
    border-radius: 10px;
    transition: .2s;
}

.nav a:hover,
.nav a.active {
    background: #2563eb;
    color: white;
}

.admin {
    position: absolute;
    bottom: 25px;
    left: 18px;
    right: 18px;
    padding: 15px;
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 12px;
}

.admin small {
    color: #93c5fd;
}

/* MAIN */

.main {
    margin-left: 245px;
    padding: 32px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
}

.topbar h1 {
    margin: 0;
    font-size: 28px;
}

.topbar p {
    margin: 7px 0 0;
    color: #64748b;
}

.date {
    background: white;
    padding: 12px 18px;
    border-radius: 10px;
    box-shadow: 0 3px 15px rgba(15,23,42,.06);
}

/* CARDS */

.cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.card {
    background: white;
    padding: 23px;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(15,23,42,.06);
}

.card-title {
    color: #64748b;
    font-size: 14px;
}

.card-value {
    font-size: 31px;
    font-weight: 700;
    margin-top: 10px;
}

.card-sub {
    margin-top: 8px;
    color: #16a34a;
    font-size: 13px;
}

.icon {
    float: right;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #eff6ff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

/* CONTENT */

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    margin-bottom: 25px;
}

.panel {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 18px rgba(15,23,42,.06);
}

.panel h2 {
    margin-top: 0;
    font-size: 18px;
}

/* DEPARTMENTS */

.department {
    margin-bottom: 18px;
}

.department-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 7px;
    font-size: 14px;
}

.progress {
    height: 8px;
    background: #e8eef7;
    border-radius: 20px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg,#2563eb,#60a5fa);
    border-radius: 20px;
}

/* QUICK ACTION */

.action {
    display: block;
    text-decoration: none;
    padding: 15px;
    margin-bottom: 12px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    transition: .2s;
}

.action:hover {
    border-color: #2563eb;
    background: #eff6ff;
}

.action.primary {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
    text-align: center;
    font-weight: 600;
}

/* EMPLOYEE TABLE */

.table-panel {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(15,23,42,.06);
    overflow: hidden;
}

.table-header {
    padding: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.table-header h2 {
    margin: 0 0 18px;
}

.filters {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 10px;
}

input,
select {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #dbe2ea;
    border-radius: 9px;
    outline: none;
    background: white;
}

input:focus,
select:focus {
    border-color: #2563eb;
}

button {
    border: 0;
    border-radius: 9px;
    background: #2563eb;
    color: white;
    padding: 12px 20px;
    cursor: pointer;
    font-weight: 600;
}

button:hover {
    background: #1d4ed8;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 16px 20px;
    text-align: left;
    border-bottom: 1px solid #eef2f7;
}

th {
    color: #64748b;
    font-size: 13px;
    background: #fafbfc;
}

td {
    font-size: 14px;
}

.name {
    font-weight: 600;
}

.email {
    color: #64748b;
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.active {
    background: #dcfce7;
    color: #15803d;
}

.inactive {
    background: #fee2e2;
    color: #b91c1c;
}

.department-badge {
    background: #eff6ff;
    color: #2563eb;
}

.empty {
    text-align: center;
    padding: 45px;
    color: #64748b;
}

/* RESPONSIVE */

@media(max-width:1100px) {

    .cards {
        grid-template-columns: repeat(2,1fr);
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .filters {
        grid-template-columns: 1fr 1fr;
    }
}

@media(max-width:750px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .admin {
        position: static;
        margin-top: 20px;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .cards {
        grid-template-columns: 1fr;
    }

    .topbar {
        display: block;
    }

    .date {
        margin-top: 15px;
        display: inline-block;
    }

    .filters {
        grid-template-columns: 1fr;
    }

    .table-panel {
        overflow-x: auto;
    }

    table {
        min-width: 850px;
    }
}

</style>
</head>

<body>

<aside class="sidebar">

    <div class="logo">
        <div class="logo-icon">👥</div>
        <h2>Employee Hub</h2>
    </div>

    <nav class="nav">

        <a href="index.php" class="active">
            🏠 Dashboard
        </a>

        <a href="#employees">
            👥 Employees
        </a>

        <a href="add_employee.php">
            ➕ Add Employee
        </a>

        <a href="#departments">
            🏢 Departments
        </a>

        <a href="#reports">
            📊 Reports
        </a>

    </nav>

    <div class="admin">
        <strong>Administrator</strong><br>
        <small>Employee Hub</small>
    </div>

</aside>


<main class="main">

    <div class="topbar">

        <div>
            <h1>Employee Management</h1>
            <p>Welcome back. Here's your workforce overview.</p>
        </div>

        <div class="date">
            📅 <?= date("F d, Y") ?>
        </div>

    </div>


    <!-- STATISTICS -->

    <section class="cards">

        <div class="card">
            <div class="icon">👥</div>
            <div class="card-title">Total Employees</div>
            <div class="card-value"><?= $total ?></div>
            <div class="card-sub">All employees</div>
        </div>

        <div class="card">
            <div class="icon">✓</div>
            <div class="card-title">Active Employees</div>
            <div class="card-value"><?= $active ?></div>
            <div class="card-sub">Currently active</div>
        </div>

        <div class="card">
            <div class="icon">🏢</div>
            <div class="card-title">Departments</div>
            <div class="card-value"><?= $departments ?></div>
            <div class="card-sub">Unique departments</div>
        </div>

        <div class="card">
            <div class="icon">✨</div>
            <div class="card-title">New This Month</div>
            <div class="card-value"><?= $recent ?></div>
            <div class="card-sub">Joined recently</div>
        </div>

    </section>


    <!-- SECONDARY PANELS -->

    <section class="content-grid">

        <div class="panel" id="departments">

            <h2>Employees by Department</h2>

            <?php if (count($departmentList) > 0): ?>

                <?php foreach ($departmentList as $d): ?>

                    <?php
                    $percentage = $total > 0
                        ? round(($d['total'] / $total) * 100)
                        : 0;
                    ?>

                    <div class="department">

                        <div class="department-row">
                            <span><?= htmlspecialchars($d['department']) ?></span>
                            <strong><?= $d['total'] ?></strong>
                        </div>

                        <div class="progress">
                            <div
                                class="progress-bar"
                                style="width: <?= $percentage ?>%">
                            </div>
                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="empty">No departments available.</p>

            <?php endif; ?>

        </div>


        <div class="panel">

            <h2>Quick Actions</h2>

            <a class="action primary" href="add_employee.php">
                ➕ Add New Employee
            </a>

            <a class="action" href="#employees">
                👥 View All Employees
            </a>

            <a class="action" href="#departments">
                🏢 View Departments
            </a>

            <a class="action" href="index.php">
                🔄 Refresh Dashboard
            </a>

        </div>

    </section>


    <!-- EMPLOYEE TABLE -->

    <section class="table-panel" id="employees">

        <div class="table-header">

            <h2>All Employees</h2>

            <form method="GET" class="filters">

                <input
                    type="text"
                    name="search"
                    placeholder="Search name, email, role..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <select name="department">

                    <option value="">All Departments</option>

                    <?php while ($d = $filterDepartments->fetch_assoc()): ?>

                        <option
                            value="<?= htmlspecialchars($d['department']) ?>"
                            <?= $department === $d['department'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($d['department']) ?>
                        </option>

                    <?php endwhile; ?>

                </select>


                <select name="status">

                    <option value="">All Status</option>

                    <option
                        value="Active"
                        <?= $status === 'Active' ? 'selected' : '' ?>
                    >
                        Active
                    </option>

                    <option
                        value="Inactive"
                        <?= $status === 'Inactive' ? 'selected' : '' ?>
                    >
                        Inactive
                    </option>

                </select>

                <button type="submit">Search</button>

            </form>

        </div>


        <?php if ($result && $result->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>

                </thead>

                <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>

                        <td><?= (int)$row['id'] ?></td>

                        <td class="name">
                            <?= htmlspecialchars($row['name']) ?>
                        </td>

                        <td class="email">
                            <?= htmlspecialchars($row['email']) ?>
                        </td>

                        <td>
                            <span class="badge department-badge">
                                <?= htmlspecialchars($row['department']) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['role']) ?>
                        </td>

                        <td>

                            <?php if ($row['status'] === 'Active'): ?>

                                <span class="badge active">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="badge inactive">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= date("M d, Y", strtotime($row['created_at'])) ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">
                <h3>No employees found</h3>
                <p>Try changing your search or add a new employee.</p>

                <a href="add_employee.php">
                    <button>Add Employee</button>
                </a>
            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
