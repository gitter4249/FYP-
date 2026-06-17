<?php
session_start();
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin'] = $row['full_name'];
            $_SESSION['admin_id'] = $row['id'];
            
            header("Location: admin_dashboard.php");
            exit;
        }
    }
    echo "<script>alert('Wrong email or password'); window.location='admin_login.php';</script>";
    exit;
}
?>