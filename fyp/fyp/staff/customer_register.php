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
    $confirm = $_POST['confirm_password'];

    if ($pass !== $confirm) {
        echo "<script>alert('Error: Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    $check = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "<script>alert('Error: Email already registered!'); window.history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "INSERT INTO customers (name, email, phone, address, gender, race, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sssssss", $name, $email, $phone, $address, $gender, $race, $hashed_password);
        if ($stmt->execute()) {
            echo "<script>alert('Success: Account created!'); window.location.href='staff_dashboard.php';</script>";
        } else {
            echo "Execute Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Prepare Error: " . $conn->error;
    }
    $conn->close();
}
?>