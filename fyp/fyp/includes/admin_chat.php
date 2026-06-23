<div class="admin-layout" style="display: flex; height: 500px; border: 1px solid #ccc;">
    <div id="user-list" style="width: 200px; border-right: 1px solid #eee; overflow-y: auto;">
        </div>

    <div class="chat-main" style="flex: 1; display: flex; flex-direction: column;">
        <div id="admin-msg-history" style="flex: 1; overflow-y: auto; padding: 10px; background: #f4f4f4;">
            </div>
        <div style="padding: 10px;">
            <input type="text" id="admin-input" style="width: 80%;">
            <button onclick="sendAdminReply()"></button>
        </div>
    </div>
</div>

<script>
    let currentCustomer = ""; 
function loadUserList() {
    fetch('getmessage.php') 
    .then(res => res.json())
    .then(users => {
        const list = document.getElementById('user-list');
        list.innerHTML = users.map(u => `
            <div onclick="selectUser('${u}')" style="padding:10px; cursor:pointer; border-bottom:1px solid #eee;">
                User: ${u}
            </div>
        `).join('');
    });
}

function selectUser(sessionId) {
    currentCustomer = sessionId;
    fetchAdminMessages();
}

function fetchAdminMessages() {
    if (!currentCustomer) return;
    fetch(`get_messages.php?session_id=${currentCustomer}`)
    .then(res => res.json())
    .then(data => {
        const history = document.getElementById('admin-msg-history');
        history.innerHTML = data.map(msg => `
            <div style="margin-bottom: 10px; text-align: ${msg.sender === 'staff' ? 'right' : 'left'}">
                <span style="background: ${msg.sender === 'staff' ? '#20304a' : '#fff'}; 
                             color: ${msg.sender === 'staff' ? '#fff' : '#000'}; 
                             padding: 5px 10px; border-radius: 5px;">
                    ${msg.message}
                </span>
            </div>
        `).join('');
    });
}

function sendAdminReply() {
    const text = document.getElementById('admin-input').value;
    let fd = new FormData();
    fd.append('session_id', currentCustomer);
    fd.append('sender', 'staff'); // 关键：后台回复，sender 改为 staff
    fd.append('message', text);

    fetch('send_messages.php', { method: 'POST', body: fd }).then(() => {
        document.getElementById('admin-input').value = "";
        fetchAdminMessages();
    });
}

// 自动刷新
setInterval(loadUserList, 5000);
setInterval(fetchAdminMessages, 3000);
</script>
