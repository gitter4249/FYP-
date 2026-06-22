<?php
include "db.php";

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = mysqli_real_escape_string($conn, $_POST['session_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $sender = 'staff'; 

    if ($session_id && $message) {
        $query = "INSERT INTO chat_messages (session_id, sender, message) VALUES ('$session_id', '$sender', '$message')";
        if (mysqli_query($conn, $query)) {
            $response['success'] = true;
        } else {
            $response['message'] = mysqli_error($conn);
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>