<?php
session_start();
// 确保路径正确，指向你的 includes/db.php
require_once '../includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // --- 修改点 1: 将 id 改为 customer_id ---
    $sql = "SELECT customer_id, name, password FROM customers WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            
            // --- 修改点 2: 存入 Session 时使用新的列名 ---
            $_SESSION['customer_id'] = $user['customer_id'];
            $_SESSION['customer_name'] = $user['name'];

            // 成功跳转
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
?>