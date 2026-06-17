<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php'; 

$currentUser = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : 'Guest_' . substr(session_id(), 0, 5);
?>

<style>

.chat-icon-container {
    position: fixed; 
    bottom: 30px; 
    right: 30px; 
    z-index: 5000; 
    cursor: pointer; 
    transition: transform 0.3s ease; 
}
.chat-icon-container:hover { 
    transform: scale(1.1); 
}

#chat-icon { 
    display: block;
}

#chat-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 14px;
    height: 14px;
    background-color: #ff4d4f; 
    border-radius: 50%;
    display: none; 
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
    
#chat-container { 
    display: none; 
    position: fixed; 
    bottom: 120px; 
    right: 20px; 
    width: 400px; 
    height: 500px; 
    background: white; 
    border-radius: 15px; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
    flex-direction: column; 
    overflow: hidden; 
    z-index: 5000; 
    border: 1px solid #eee; 
}
    
.chat-header { 
    background: #20304a; 
    color: white; 
    padding: 15px; 
    font-weight: bold; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
}
    
#msg-history { 
    flex: 1; 
    overflow-y: auto; 
    padding: 15px; 
    background: #f9f9f9; 
    display: flex; 
    flex-direction: column; 
}
    
.msg-bubble { 
    margin-bottom: 12px; 
    max-width: 85%; 
    padding: 8px 12px; 
    border-radius: 10px; 
    font-size: 13px; 
    line-height: 1.5; 
    word-wrap: break-word; 
}
    
.msg-user { 
    align-self: flex-end; 
    background: #ffd453; 
    color: black; 
    border-bottom-right-radius: 2px;
}
    
.msg-ai { 
    align-self: flex-start; 
    background: #20304a; 
    color: white; 
    border-bottom-left-radius: 2px; 
}

.quick-opt-btn {
    display: block;
    width: fit-content; 
    max-width: 90%;
    background: white;
    border: 1px solid #d1d9e6;
    padding: 8px 15px;
    margin-bottom: 8px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #555;
    text-align: left;
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.quick-opt-btn:hover {
    background: #f0f4f8;
    border-color: #20304a;
    color: #20304a;
    transform: translateY(-1px);
}
    
.chat-input-box { 
    display: flex; 
    padding: 10px; 
    border-top: 1px solid #eee; 
    background: white; 
}

#msg-input { 
    flex: 1; 
    border: 1px solid #ddd; 
    padding: 10px; 
    border-radius: 5px; 
    outline: none; 
}

.chat-send-btn { 
    background: #20304a; 
    color: white; 
    border: none; 
    padding: 0 15px; 
    margin-left: 8px; 
    border-radius: 5px; 
    cursor: pointer; 
}
</style>

<div class="chat-icon-container" onclick="toggleChat()">
    <img id="chat-icon" src="images/chat-icon.png" width="90" alt="Chat">
    <div id="chat-badge"></div>
</div>

<div id="chat-container">
    <div class="chat-header">
        <span>YS Assistant (<?php echo htmlspecialchars($currentUser); ?>)</span>
        <span onclick="toggleChat()" style="cursor:pointer; font-size: 20px;">&times;</span>
    </div>
    
    <div id="msg-history"></div>
    
    <div id="quick-options" style="padding: 8px 12px; background: #f9f9f9; border-top: 1px solid #eee; display: flex; flex-wrap: wrap; gap: 6px;">
        <button class="quick-opt-btn" onclick="quickSend('Account')" style="margin:0; padding: 6px 12px; font-size:12px;">Need help setting up account</button>
        <button class="quick-opt-btn" onclick="quickSend('business hours')" style="margin:0; padding: 6px 12px; font-size:12px;">View business hours</button>
        <button class="quick-opt-btn" onclick="quickSend('Price')" style="margin:0; padding: 6px 12px; font-size:12px;">About Price</button>
    </div>
    
    <div class="chat-input-box">
        <input type="text" id="msg-input" placeholder="Enter message..." onkeypress="if(event.keyCode==13) sendMessage()">
        <button class="chat-send-btn" onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
const msgSound = new Audio('../fyp/audio/ding.mp3'); 

let audioUnlocked = false;
function unlockAudio() {
    if (audioUnlocked) return;
    msgSound.play().then(() => {
        msgSound.pause();
        msgSound.currentTime = 0;
        audioUnlocked = true;
        console.log("Audio permission successfully unlocked!");
        document.removeEventListener('click', unlockAudio);
    }).catch(err => {
        console.log("Waiting for more explicit user interaction to unlock audio", err);
    });
}
document.addEventListener('click', unlockAudio);


let localMessageCount = -1; 
let isFirstLoad = true;

function toggleChat() {
    const container = document.getElementById('chat-container');
    const badge = document.getElementById('chat-badge');
    const history = document.getElementById('msg-history');
    
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'flex';
        badge.style.display = 'none';
        loadMessages(true);
    } else {
        container.style.display = 'none';
    }
}

function sendMessage() {
    const input = document.getElementById('msg-input');
    const message = input.value.trim();
    if (message === "") return;

    input.value = '';

    fetch('includes/send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `message=${encodeURIComponent(message)}`
    })
    .then(response => response.text())
    .then(data => {
        loadMessages(true);
    });
}

function loadMessages(forceScrollBottom = false) {
    const history = document.getElementById('msg-history');
    const container = document.getElementById('chat-container');
    const badge = document.getElementById('chat-badge');
    
    const isAtBottom = history.scrollHeight - history.scrollTop <= history.clientHeight + 50;

    fetch('includes/get_messages.php')
    .then(response => response.text())
    .then(data => {
        const welcomeMsg = '<div class="msg-bubble msg-ai">Hello! I am an customer service representative from YS Aluminium. How can I help you?</div>';
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = data;
        const currentMessages = tempDiv.querySelectorAll('.msg-bubble');
        const currentCount = currentMessages.length;

        console.log("currentCount:", currentCount, "localMessageCount:", localMessageCount);

        if (!isFirstLoad && localMessageCount !== -1 && currentCount > localMessageCount) {
            const lastMessage = currentMessages[currentCount - 1];
            if (lastMessage && lastMessage.classList.contains('msg-ai')) {
                if (container.style.display === 'none' || container.style.display === '') {
                    badge.style.display = 'block';
                    msgSound.play().catch(error => {
                        console.log("Error playing message sound:", error);
                    });
                }
            }
        }
        
        if (isFirstLoad) {
            isFirstLoad = false;
        }
        
        localMessageCount = currentCount;
        history.innerHTML = welcomeMsg + data;
        
        if (container.style.display === 'flex' && (forceScrollBottom || isAtBottom)) {
            history.scrollTop = history.scrollHeight;
        }
    })
    .catch(err => console.error("Failed to fetch messages, please check the path to get_messages.php:", err));
}

function quickSend(text) {
    const input = document.getElementById('msg-input');
    input.value = text;
    sendMessage();
}

setInterval(() => {
    loadMessages();
}, 3000);

document.addEventListener("DOMContentLoaded", () => {
    loadMessages();
});
</script>