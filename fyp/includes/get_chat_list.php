<?php
session_start();
include "db.php";

header('Content-Type: application/json');

$query = "SELECT session_id, 
                 SUM(CASE WHEN is_read = 0 AND sender != 'staff' THEN 1 ELSE 0 END) AS unread_count 
          FROM chat_messages 
          GROUP BY session_id 
          ORDER BY MAX(created_at) DESC";

$result = mysqli_query($conn, $query);

$chat_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $chat_list[] = $row;
}

echo json_encode($chat_list);
?>