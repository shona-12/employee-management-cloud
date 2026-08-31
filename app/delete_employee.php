<?php
require "db.php";

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Invalid employee ID.");
}

$stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: index.php?success=Employee%20deleted%20successfully");
    exit;
}

echo "Unable to delete employee.";

$stmt->close();
$conn->close();
?>
