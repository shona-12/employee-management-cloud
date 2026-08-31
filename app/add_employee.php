<?php
require "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $role = trim($_POST["role"] ?? "");
    $status = trim($_POST["status"] ?? "Active");

    if ($name === "" || $email === "" || $department === "" || $role === "") {

        $message = "Please fill in all required fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO employees
            (name, email, phone, department, role, status)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
            $name,
            $email,
            $phone,
            $department,
            $role,
            $status
        );

        if ($stmt->execute()) {

            header("Location: index.php?success=Employee%20added%20successfully");
            exit;

        } else {

            $message = "Unable to add employee. Please try again.";
            $messageType = "error";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Employee | Employee Hub</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
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
    font-size: 21px;
}

.logo h2 {
    margin: 0;
    font-size: 20px;
}

.nav {
    margin-top: 28px;
}

.nav a {
    display: block;
    color: #dbeafe;
    text-decoration: none;
    padding: 14px 15px;
    margin-bottom: 7px;
    border-radius: 10px;
}

.nav a:hover,
.nav a.active {
    background: #2563eb;
    color: white;
}

/* MAIN */

.main {
    margin-left: 245px;
    padding: 35px;
}

.header {
    margin-bottom: 28px;
}

.header h1 {
    margin: 0;
    font-size: 30px;
}

.header p {
    color: #64748b;
    margin-top: 8px;
}

/* FORM CARD */

.form-card {
    max-width: 850px;
    background: white;
    border-radius: 18px;
    padding: 32px;
    box-shadow: 0 5px 25px rgba(15,23,42,.07);
}

.form-card h2 {
    margin-top: 0;
    margin-bottom: 6px;
}

.form-description {
    color: #64748b;
    margin-bottom: 28px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full {
    grid-column: 1 / -1;
}

label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.required {
    color: #dc2626;
}

input,
select {
    width: 100%;
    padding: 13px 14px;
    border: 1px solid #dbe2ea;
    border-radius: 9px;
    font-size: 14px;
    outline: none;
    background: white;
}

input:focus,
select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 30px;
    padding-top: 22px;
    border-top: 1px solid #eef2f7;
}

.btn {
    padding: 12px 22px;
    border-radius: 9px;
    text-decoration: none;
    font-weight: 600;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-cancel {
    background: #f1f5f9;
    color: #475569;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-primary:hover {
    background: #1d4ed8;
}

/* MESSAGE */

.message {
    max-width: 850px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.message.error {
    background: #fee2e2;
    color: #991b1b;
}

/* RESPONSIVE */

@media(max-width: 800px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full {
        grid-column: auto;
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

        <a href="index.php">
            🏠 Dashboard
        </a>

        <a href="index.php">
            👥 Employees
        </a>

        <a href="add_employee.php" class="active">
            ➕ Add Employee
        </a>

    </nav>

</aside>


<main class="main">

    <div class="header">

        <h1>Add New Employee</h1>

        <p>
            Create a new employee record and store it securely
