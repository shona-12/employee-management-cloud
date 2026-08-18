<?php
require "db.php";

$result = $conn->query("SELECT * FROM employees ORDER BY id DESC");
$total = $conn->query("SELECT COUNT(*) AS c FROM employees")->fetch_assoc()['c'];
$active = $conn->query("SELECT COUNT(*) AS c FROM employees WHERE status='Active'")->fetch_assoc()['c'];
$departments = $conn->query("SELECT COUNT(DISTINCT department) AS c FROM employees")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Employee Management System</title>
<style>
body{margin:0;font-family:Arial;background:#f4f7fb;color:#172033}
.sidebar{position:fixed;width:230px;height:100%;background:#111827;color:white;padding:25px}
.sidebar h2{margin-bottom:40px}
.sidebar p{padding:12px;color:#cbd5e1}
.main{margin-left:280px;padding:35px}
.cards{display:flex;gap:20px;margin:30px 0}
.card{background:white;padding:25px;border-radius:15px;width:200px;box-shadow:0 4px 15px #00000010}
.card h3{color:#64748b;margin:0}
.card h1{font-size:32px;margin:12px 0}
table{width:100%;background:white;border-collapse:collapse;border-radius:15px;overflow:hidden}
th,td{padding:16px;text-align:left;border-bottom:1px solid #eee}
th{background:#f8fafc}
.status{color:#16a34a;font-weight:bold}
button{background:#2563eb;color:white;border:0;padding:12px 18px;border-radius:8px}
</style>
</head>

<body>

<div class="sidebar">
<h2>EMPLOYEE HUB</h2>
<p>🏠 Dashboard</p>
<p>👥 Employees</p>
<p>🏢 Departments</p>
<p>📊 Analytics</p>
<p>⚙ Settings</p>
</div>

<div class="main">

<h1>Employee Management Dashboard</h1>
<p>Welcome back. Here's your workforce overview.</p>

<div class="cards">

<div class="card">
<h3>Total Employees</h3>
<h1><?= $total ?></h1>
</div>

<div class="card">
<h3>Active Employees</h3>
<h1><?= $active ?></h1>
</div>

<div class="card">
<h3>Departments</h3>
<h1><?= $departments ?></h1>
</div>

</div>

<h2>Recent Employees</h2>

<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Department</th>
<th>Role</th>
<th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['department']) ?></td>
<td><?= htmlspecialchars($row['role']) ?></td>
<td class="status"><?= htmlspecialchars($row['status']) ?></td>
</tr>

<?php endwhile; ?>

</table>

</div>
</body>
</html>
