<?php
session_start();
require_once '../includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, name, password FROM customers WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['name'];

            // 成功跳转，带上 success 参数
            header("Location: ../homepage.php?login=success");
            exit();
        } else {
            header("Location: customer_login.php?error=Incorrect password");
            exit();
        }
    } else {
        header("Location: customer_login.php?error=User not found");
        exit();
    }
}