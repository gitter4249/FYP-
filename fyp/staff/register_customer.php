<?php
session_start();
require '../includes/db.php'; 

if (isset($_POST['register_customer'])) {

    $name    = mysqli_real_escape_string($conn, $_POST['cust_name']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);
    $race    = mysqli_real_escape_string($conn, $_POST['race']);
    $pass    = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($pass !== $confirm_pass) {
        echo "<script>alert('error: password mismatch'); window.history.back();</script>";
        exit;
    }

    $check_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('error: email already registered'); window.history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, gender, race, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");

    $stmt->bind_param("sssssss", $name, $email, $phone, $address, $gender, $race, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('success: new customer account created!'); window.location.href='staff_dashboard.php';</script>";
    } else {
        echo "<script>alert('database error: " . addslashes($conn->error) . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: staff_dashboard.php");
    exit;
}
?>