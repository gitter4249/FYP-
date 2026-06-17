<?php
require 'db.php';

if (isset($_POST['session_id'])) {
    $session_id = mysqli_real_escape_string($conn, $_POST['session_id']);
    
    $query = "DELETE FROM chat_messages WHERE session_id = '$session_id'";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}
?>