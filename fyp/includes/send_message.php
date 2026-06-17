<?php
session_start();
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $session_id = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : 'guest_' . substr(session_id(), 0, 5);
    
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $sender = 'customer'; 

    $query = "INSERT INTO chat_messages (session_id, sender, message, is_read) VALUES ('$session_id', '$sender', '$message', 0)";
    
    if (mysqli_query($conn, $query)) {
        $customer_msg = trim($_POST['message']);

        $auto_replies = [
            "price" => "Our aluminum windows and doors are customized based on dimensions. Please provide an approximate size, and I will give you a quote.",
            "business hours" => "Our business hours are from 8:30 AM to 6:00 PM, Monday to Saturday.",
            "account" => "To set up your account, please visit our website and click on the Products page. You can browse our products and contact us for a quote in WhatsApp. Our staff will assist you in creating an account and placing your order.",
        ];

        foreach ($auto_replies as $key => $reply) {
            if (stripos($customer_msg, $key) !== false) {
                
                $reply_safe = mysqli_real_escape_string($conn, $reply);
                
                $auto_query = "INSERT INTO chat_messages (session_id, sender, message, is_read) 
                               VALUES ('$session_id', 'staff', '$reply_safe', 0)";
                
                mysqli_query($conn, $auto_query);
                
                break;
            }
        }
        echo "success";
    } else {
        echo "error";
    }
}
?>