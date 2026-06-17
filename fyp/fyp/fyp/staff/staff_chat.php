<?php
session_start();
require '../includes/db.php';

// 确保员工已登录
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}

$staff_id = intval($_SESSION['staff_id']);
$staff_name = $_SESSION['staff_name'] ?? "Staff";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Customer Chat | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f4f5;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-bar {
            background: white;
            border-bottom: 1px solid #e4e4e7;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-area img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
        }
        .logo-area span {
            font-weight: 800;
            font-size: 1.2rem;
            color: #1c1c1e;
        }
        .staff-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8f9fa;
            padding: 6px 16px;
            border-radius: 40px;
        }
        .staff-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
        .chat-container {
            flex: 1;
            display: flex;
            margin: 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .user-sidebar {
            width: 280px;
            background: #fcfcfd;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
        }
        .user-header {
            padding: 18px 20px;
            font-weight: 700;
            border-bottom: 1px solid #e9ecef;
            background: white;
            font-size: 1rem;
        }
        .user-list {
            flex: 1;
            overflow-y: auto;
        }
        .user-item {
            padding: 14px 20px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f0f0f2;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .user-item:hover {
            background: #f8f9fa;
        }
        .user-item.active {
            background: #eef2ff;
            border-left: 3px solid #20304a;
            font-weight: 500;
        }
        .user-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: #1f2937;
        }
        .unread-badge {
            background: #dc3545;
            color: white;
            border-radius: 30px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .chat-room {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }
        .chat-header {
            padding: 16px 24px;
            border-bottom: 1px solid #e9ecef;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-header h5 {
            margin: 0;
            font-weight: 600;
        }
        .btn-delete {
            background: transparent;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 30px;
            padding: 5px 14px;
            font-size: 0.8rem;
            transition: 0.2s;
        }
        .btn-delete:hover {
            background: #fee2e2;
        }
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #fefefe;
        }
        .msg-bubble {
            max-width: 70%;
            padding: 10px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            line-height: 1.4;
            word-wrap: break-word;
        }
        .msg-staff {
            background: #20304a;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .msg-customer {
            background: #e9ecef;
            color: #1f2937;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        .input-area {
            padding: 16px 20px;
            border-top: 1px solid #e9ecef;
            background: white;
            display: flex;
            gap: 12px;
        }
        .input-area input {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            padding: 10px 18px;
            outline: none;
            font-size: 0.9rem;
        }
        .input-area button {
            background: #20304a;
            border: none;
            color: white;
            border-radius: 30px;
            padding: 0 20px;
            font-weight: 600;
        }
        .empty-placeholder {
            text-align: center;
            margin-top: 100px;
            color: #94a3b8;
        }
        @media (max-width: 640px) {
            .user-sidebar { width: 240px; }
            .msg-bubble { max-width: 85%; }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <div class="logo-area">
        <img src="../images/ys.jpg" alt="YS Logo" onerror="this.src='https://via.placeholder.com/40'">
        <span>YS ALUMINIUM</span>
    </div>
    <div class="staff-info">
        <img src="../images/default-avatar.jpg" class="staff-avatar" alt="Avatar" onerror="this.src='https://ui-avatars.com/api/?name=Staff&background=20304a&color=fff&size=36'">
        <span><?php echo htmlspecialchars($staff_name); ?></span>
    </div>
</div>

<div class="chat-container">
    <div class="user-sidebar">
        <div class="user-header">
            <i class="bi bi-chat-dots-fill me-2"></i> Customers
        </div>
        <div class="user-list" id="userList">
            <div class="text-center text-muted p-4">Loading...</div>
        </div>
    </div>

    <div class="chat-room">
        <div class="chat-header">
            <h5 id="chatWithLabel">Select a customer</h5>
            <button class="btn-delete" id="deleteChatBtn" style="display: none;" onclick="confirmDeleteChat()">
                <i class="bi bi-trash3"></i> Delete Chat
            </button>
        </div>
        <div class="messages-area" id="messagesArea">
            <div class="empty-placeholder">
                <i class="bi bi-chat-left-text" style="font-size: 2rem;"></i>
                <p class="mt-2">No conversation selected</p>
            </div>
        </div>
        <div class="input-area" id="inputArea" style="display: none;">
            <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off">
            <button id="sendBtn">Send <i class="bi bi-send-fill"></i></button>
        </div>
    </div>
</div>

<script>

let currentCustomer = null;
let refreshInterval = null;

function loadCustomerList() {
    fetch('chat_api.php?action=conversations')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('userList');
            if (!data || data.length === 0) {
                container.innerHTML = '<div class="text-center text-muted p-4">No customers yet</div>';
                return;
            }
            let html = '';
            data.forEach(conv => {
                const isActive = (currentCustomer === conv.session_id);
                const unreadHtml = (conv.unread_staff > 0) ? `<span class="unread-badge">${conv.unread_staff}</span>` : '';
                html += `
                    <div class="user-item ${isActive ? 'active' : ''}" data-session="${conv.session_id}" onclick="selectCustomer('${conv.session_id.replace(/'/g, "\\'")}')">
                        <span class="user-name"><i class="bi bi-person-circle me-2"></i>${escapeHtml(conv.name)}</span>
                        ${unreadHtml}
                    </div>
                `;
            });
            container.innerHTML = html;
            const totalUnread = data.reduce((sum, c) => sum + (c.unread_staff || 0), 0);
            document.title = totalUnread ? `(${totalUnread}) Customer Chat` : 'Customer Chat';
        })
        .catch(err => console.error('加载顾客列表失败:', err));
}

function selectCustomer(sessionId) {
    if (currentCustomer === sessionId) return;
    currentCustomer = sessionId;

    document.querySelectorAll('.user-item').forEach(item => {
        if (item.getAttribute('data-session') === sessionId) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    const convName = document.querySelector(`.user-item[data-session="${sessionId}"] .user-name`)?.innerText || sessionId;
    document.getElementById('chatWithLabel').innerHTML = `<i class="bi bi-person-circle me-1"></i> ${convName}`;
    document.getElementById('deleteChatBtn').style.display = 'inline-block';
    document.getElementById('inputArea').style.display = 'flex';

    loadMessages(sessionId);
    markAsRead(sessionId);
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(() => {
        if (currentCustomer) {
            loadMessages(currentCustomer);
        }
    }, 5000);
}

function loadMessages(sessionId) {
    fetch(`chat_api.php?action=messages&session_id=${encodeURIComponent(sessionId)}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('messagesArea');
            if (data.error) {
                container.innerHTML = '<div class="text-center text-danger">Failed to load messages</div>';
                return;
            }
            if (!data.messages || data.messages.length === 0) {
                container.innerHTML = '<div class="empty-placeholder"><i class="bi bi-chat-dots"></i><p class="mt-2">No messages yet. Start the conversation!</p></div>';
                return;
            }
            let html = '';
            data.messages.forEach(msg => {
                const isStaff = (msg.sender === 'staff');
                const bubbleClass = isStaff ? 'msg-staff' : 'msg-customer';
                html += `<div class="msg-bubble ${bubbleClass}">${escapeHtml(msg.message)}</div>`;
            });
            container.innerHTML = html;
            container.scrollTop = container.scrollHeight;
            loadCustomerList();
        })
        .catch(err => {
            console.error('加载消息失败:', err);
            document.getElementById('messagesArea').innerHTML = '<div class="empty-placeholder text-danger">Network error</div>';
        });
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text || !currentCustomer) return;

    fetch('chat_api.php?action=send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ session_id: currentCustomer, message: text })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages(currentCustomer);
            loadCustomerList();
        } else {
            alert('Failed to send message: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error while sending');
    });
}

function markAsRead(sessionId) {
    fetch('chat_api.php?action=mark_read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ session_id: sessionId })
    }).catch(e => console.warn('mark_read failed', e));
}

function confirmDeleteChat() {
    if (!currentCustomer) return;
    if (confirm(`Are you sure you want to delete all messages with this customer?`)) {
        fetch('chat_api.php?action=delete_conversation', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ session_id: currentCustomer })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('messagesArea').innerHTML = '<div class="empty-placeholder"><i class="bi bi-chat-left-text"></i><p>Conversation deleted</p></div>';
                document.getElementById('chatWithLabel').innerHTML = 'Select a customer';
                document.getElementById('deleteChatBtn').style.display = 'none';
                document.getElementById('inputArea').style.display = 'none';
                currentCustomer = null;
                if (refreshInterval) clearInterval(refreshInterval);
                loadCustomerList();
            } else {
                alert('Failed to delete conversation');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Network error');
        });
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

window.onload = () => {
    loadCustomerList();
    setInterval(() => {
        if (!currentCustomer) loadCustomerList();
    }, 8000);
    document.getElementById('sendBtn').addEventListener('click', sendMessage);
    document.getElementById('messageInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
};

window.addEventListener('beforeunload', () => {
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>
</body>
</html>