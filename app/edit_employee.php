<?php
require "db.php";

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    die("Invalid employee ID.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $role = trim($_POST["role"] ?? "");
    $status = trim($_POST["status"] ?? "Active");

    if ($name === "" || $email === "" || $department === "" || $role === "") {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        $stmt = $conn->prepare(
            "UPDATE employees
             SET name=?, email=?, phone=?, department=?, role=?, status=?
             WHERE id=?"
        );

        $stmt->bind_param(
            "ssssssi",
            $name,
            $email,
            $phone,
            $department,
            $role,
            $status,
            $id
        );

        if ($stmt->execute()) {
            header("Location: index.php?success=Employee%20updated%20successfully");
            exit;
        }

        $error = "Unable to update employee.";
        $stmt->close();
    }

} else {

    $stmt = $conn->prepare(
        "SELECT name, email, phone, department, role, status
         FROM employees WHERE id=?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    $stmt->close();

    if (!$employee) {
        die("Employee not found.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Employee | Employee Hub</title>

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

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 245px;
    height: 100vh;
    background: linear-gradient(180deg,#071b41,#0b2b63);
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
}

.form-card {
    max-width: 850px;
    background: white;
    border-radius: 18px;
    padding: 32px;
    box-shadow: 0 5px 25px rgba(15,23,42,.07);
}

.form-card h2 {
    margin-top: 0;
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
}

.actions {
    display: flex
