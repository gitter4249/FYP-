<?php
session_start();
require '../includes/db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM admins WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['admin'] = $username;
        header("Location: admin_dashboard.php");
        exit;
    }
}

echo "<script>alert('Wrong username or password'); window.location='admin_login.php';</script>";
exit;
?>