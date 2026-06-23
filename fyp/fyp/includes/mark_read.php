<?php
require 'db.php';

if (isset($_POST['session_id'])) {
    $session_id = $_POST['session_id'];
    
    $query = "UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender != 'staff' AND is_read = 0";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}
?>