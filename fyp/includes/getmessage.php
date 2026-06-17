<?php
include "db.php";

$query = "SELECT DISTINCT session_id FROM chat_messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

header('Content-Type: application/json');
echo json_encode($users);
?>