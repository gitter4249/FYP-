<?php
session_start();
include "db.php";

if (isset($_GET['session_id'])) {
    $session_id = mysqli_real_escape_string($conn, $_GET['session_id']);
} else {
    $session_id = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : 'guest_' . substr(session_id(), 0, 5);
}

$query = "SELECT sender, message FROM chat_messages WHERE session_id = '$session_id' ORDER BY created_at ASC";
$result = mysqli_query($conn, $query);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

if (isset($_GET['session_id'])) {
    header('Content-Type: application/json');
    echo json_encode($messages);
} else {
    foreach ($messages as $msg) {
        $class = ($msg['sender'] === 'customer') ? 'msg-user' : 'msg-ai';
        echo '<div class="msg-bubble ' . $class . '">' . htmlspecialchars($msg['message']) . '</div>';
    }
}
?>