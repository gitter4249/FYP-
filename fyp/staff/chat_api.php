<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php'; 

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

$staff_id = isset($_SESSION['staff_id']) ? intval($_SESSION['staff_id']) : 0;

switch ($action) {
    case 'conversations':
        getConversations($conn, $staff_id);
        break;
        
    case 'messages':
        $session_id = isset($_GET['session_id']) ? $_GET['session_id'] : '';
        getMessages($conn, $session_id, $staff_id);
        break;
        
    case 'send':
        sendMessage($conn, $staff_id);
        break;
        
    case 'mark_read':
        markAsRead($conn);
        break;
        
    case 'unread_count':
        getUnreadCount($conn, $staff_id);
        break;
        
    case 'delete_conversation':
        deleteConversation($conn);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}


function getConversations($conn, $staff_id) {
    $sql = "SELECT 
                cm.session_id,
                MAX(cm.created_at) as last_time,
                COUNT(CASE WHEN cm.sender = 'customer' AND cm.is_read = 0 THEN 1 END) as unread_staff
            FROM chat_messages cm
            GROUP BY cm.session_id
            ORDER BY last_time DESC";
    $result = mysqli_query($conn, $sql);
    
    $conversations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $last_msg_sql = "SELECT message, sender FROM chat_messages WHERE session_id = '{$row['session_id']}' ORDER BY created_at DESC LIMIT 1";
        $last_res = mysqli_query($conn, $last_msg_sql);
        $last_msg = mysqli_fetch_assoc($last_res);
        
        $conversations[] = [
            'session_id' => $row['session_id'],
            'name' => $row['session_id'],
            'last_message' => $last_msg ? $last_msg['message'] : '',
            'unread_staff' => intval($row['unread_staff']),
            'last_time' => $row['last_time']
        ];
    }
    echo json_encode($conversations);
}

function getMessages($conn, $session_id, $staff_id) {
    if (empty($session_id)) {
        echo json_encode(['error' => 'Session ID required']);
        return;
    }
    $sql = "SELECT sender, message, created_at, is_read FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'sender' => $row['sender'],
            'message' => $row['message'],
            'created_at' => $row['created_at'],
            'is_read' => (bool)$row['is_read']
        ];
    }
    echo json_encode(['messages' => $messages]);
}

function sendMessage($conn, $staff_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $session_id = $input['session_id'] ?? '';
    $message = trim($input['message'] ?? '');
    
    if (empty($session_id) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }
    
    $sender = 'staff';
    $sql = "INSERT INTO chat_messages (session_id, sender, message, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $session_id, $sender, $message);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
}

function markAsRead($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $session_id = $input['session_id'] ?? '';
    if (empty($session_id)) {
        echo json_encode(['success' => false]);
        return;
    }
    $sql = "UPDATE chat_messages SET is_read = 1 WHERE session_id = ? AND sender = 'customer' AND is_read = 0";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    mysqli_stmt_execute($stmt);
    echo json_encode(['success' => true]);
}

function getUnreadCount($conn, $staff_id) {
    $sql = "SELECT COUNT(*) as cnt FROM chat_messages WHERE sender = 'customer' AND is_read = 0";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    echo json_encode(['count' => intval($row['cnt'])]);
}

function deleteConversation($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    $session_id = $input['session_id'] ?? '';
    
    if (empty($session_id)) {
        echo json_encode(['success' => false, 'message' => 'Session ID required']);
        return;
    }
    
    $sql = "DELETE FROM chat_messages WHERE session_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
}
?>