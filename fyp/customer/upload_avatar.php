<?php
session_start();
require_once("../includes/db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $customer_id = $_SESSION['customer_id'];
    $file = $_FILES['avatar'];

    // 1. 配置
    $upload_dir = "../uploads/avatars/"; // 确保这个文件夹存在并有写入权限
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    
    // 2. 校验
    if (!in_array($file_ext, $allowed_ext)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
        exit();
    }

    if ($file['size'] > 2 * 1024 * 1024) { // 限制 2MB
        echo json_encode(['success' => false, 'message' => 'File too large (Max 2MB).']);
        exit();
    }

    // 3. 重命名文件防止冲突 (例如: user_1_1710412800.jpg)
    $new_file_name = "user_" . $customer_id . "_" . time() . "." . $file_ext;
    $target_path = $upload_dir . $new_file_name;

    // 4. 执行上传
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // 5. 更新数据库
        $sql = "UPDATE customers SET profile_image = ? WHERE customer_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $target_path, $customer_id);
        if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'SQL Prepare Error: ' . mysqli_error($conn)]);
        exit();
    }
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Uploaded!',
                'new_path' => $target_path
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
    }
}