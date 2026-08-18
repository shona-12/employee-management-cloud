<?php
require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $department = $_POST["department"];
    $role = $_POST["role"];

    $stmt = $conn->prepare(
        "INSERT INTO employees (name,email,phone,department,role) VALUES (?,?,?,?,?)"
    );

    $stmt->bind_param("sssss", $name, $email, $phone, $department, $role);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Employee</title>
<style>
body{font-family:Arial;background:#f4f7fb;padding:50px}
.box{max-width:600px;margin:auto;background:white;padding:35px;border-radius:15px}
input{width:100%;padding:12px;margin:8px 0 18px;box-sizing:border-box}
button{background:#2563eb;color:white;border:0;padding:12px 20px;border-radius:8px}
</style>
</head>
<body>

<div class="box">
<h1>Add Employee</h1>

<form method="POST">

<label>Name</label>
<input name="name" required>

<label>Email</label>
<input type="email" name="email" required>

<label>Phone</label>
<input name="phone">

<label>Department</label>
<input name="department" required>

<label>Role</label>
<input name="role" required>

<button type="submit">Add Employee</button>

</form>
</div>

</body>
</html>
