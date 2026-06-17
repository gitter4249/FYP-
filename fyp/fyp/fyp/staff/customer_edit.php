<?php
session_start();
require '../includes/db.php'; 

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['update_customer'])) {
    $id = intval($_POST['customer_id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $race = $_POST['race'];
    $address = $_POST['address'];

    $sql = "UPDATE customers SET name=?, email=?, phone=?, gender=?, race=?, address=? WHERE customer_id=?";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssi", $name, $email, $phone, $gender, $race, $address, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: staff_dashboard.php?msg=update_success");
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>