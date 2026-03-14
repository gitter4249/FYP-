<?php
session_start();
// 1. 引入数据库连接 - 请确保路径正确
require_once("../includes/db.php"); 

// 2. 检查登录状态
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// --- 修正 1：获取用户信息，并增加错误检查 ---
$sql_user = "SELECT status, name, email, phone, address, profile_image FROM customers WHERE customer_id = ?";
$status_check = mysqli_prepare($conn, $sql_user);

if (!$status_check) {
    // 如果报错，这里会打印出具体的 SQL 错误，帮你定位是哪个字段名写错了
    die("Database Error: " . mysqli_error($conn)); 
}

mysqli_stmt_bind_param($status_check, "i", $customer_id);
mysqli_stmt_execute($status_check);
$user_result = mysqli_stmt_get_result($status_check);
$user_data = $user_result->fetch_assoc();

if (!$user_data || $user_data['status'] !== 'active') {
    session_destroy();
    header("Location: customer_login.php?error=Your account is no longer active.");
    exit();
}

$name = $user_data['name'];

$update_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $new_name = $_POST['name'];
    $new_phone = $_POST['phone'];
    $new_address = $_POST['address'];

    // 使用预处理语句更新，无需手动 escape_string
    $update_sql = "UPDATE customers SET name = ?, phone = ?, address = ? WHERE customer_id = ?";
    $upd_stmt = mysqli_prepare($conn, $update_sql);
    if ($upd_stmt) {
        mysqli_stmt_bind_param($upd_stmt, "sssi", $new_name, $new_phone, $new_address, $customer_id);
        if (mysqli_stmt_execute($upd_stmt)) {
            $_SESSION['name'] = $new_name;
            // 同步更新当前页面显示的数据
            $user_data['name'] = $new_name;
            $user_data['phone'] = $new_phone;
            $user_data['address'] = $new_address;
            $name = $new_name;
            $update_msg = "Profile updated successfully!";
        }
    }
}
 // --- 修正 3：获取预约数据 (只保留一份，删除冗余代码) ---
$appointments = [];
$sql_appt = "SELECT a.appt_date, a.appt_time, a.status, p.product_name 
             FROM appointments a 
             LEFT JOIN products p ON a.product_id = p.product_id 
             WHERE a.customer_id = ? 
             ORDER BY a.appt_date DESC";

$stmt = mysqli_prepare($conn, $sql_appt);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
} else {
    // 调试用：如果获取预约失败
    error_log("Appointment SQL Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal - YS Aluminum</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* --- 完全保留你提供的所有 CSS 变量和样式 --- */
        :root {
            --sidebar-width: 260px;
            --pure-black: #000000;
            --border-color: #e5e7eb;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-light: #fafafa;
            --success-green: #22c55e;
            --danger-red: #ef4444;
        }
        <img id="profileAvatar" 
        src="${avatarSrc}" 
        alt="User Avatar" 
        onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(customerData.name)}&background=eeeeee&color=000000&size=150'"
        style="width: 130px; 
                height: 130px; 
                border-radius: 15px; 
                border: 5px solid #fff; 
                object-fit: cover; 
                background: #f5f5f5; 
                display: block;
                box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; background-color: #ffffff; color: var(--text-main); }
        .sidebar { width: var(--sidebar-width); height: 100vh; background-color: var(--pure-black); color: white; position: fixed; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-header { padding: 30px 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #262626; }
        .sidebar-header img { width: 60px; height: auto; border-radius: 6px; background: white; padding: 2px; }
        .sidebar-header .brand-name { font-size: 1.1rem; font-weight: 800; letter-spacing: 0.5px; color: #ffffff; }
        .sidebar-menu { list-style: none; padding: 0; margin: 20px 0; flex-grow: 1; }
        .sidebar-menu li { padding: 14px 24px; cursor: pointer; transition: 0.2s; font-size: 0.9rem; color: #a3a3a3; font-weight: 500; }
        .sidebar-menu li:hover { background-color: #171717; color: white; }
        .sidebar-menu li.active { background-color: #ffffff; color: var(--pure-black); font-weight: 700; }
        .main-container { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); min-height: 100vh; background: var(--bg-light); }
        .navbar { height: 70px; background-color: white; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; border-bottom: 1px solid var(--border-color); }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; margin-right: 25px; text-transform: uppercase; }
        .account-area { display: flex; align-items: center; gap: 20px; }
        .logout-btn { text-decoration: none; color: #ef4444; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; border: 1px solid #ef4444; padding: 5px 12px; border-radius: 4px; transition: 0.3s; }
        .logout-btn:hover { background: #ef4444; color: white; }
        .content-area { padding: 40px; }
        .page-header { margin-bottom: 30px; border-bottom: 2px solid var(--pure-black); padding-bottom: 15px; }
        .page-title { font-size: 1.5rem; font-weight: 800; text-transform: uppercase; margin: 0; }
        .data-card { background: white; border: 1px solid var(--border-color); padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 20px; }
        .profile-layout { display: grid; grid-template-columns: 200px 1fr; gap: 40px; }
        .profile-pic-box img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #f3f4f6; margin-bottom: 15px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-weight: 700; font-size: 0.8rem; margin-bottom: 8px; text-transform: uppercase; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; }
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .custom-table th { text-align: left; padding: 15px; background: #f8fafc; border-bottom: 2px solid var(--pure-black); font-size: 0.75rem; text-transform: uppercase; }
        .custom-table td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .btn-pdf { background: #f1f5f9; color: #0f172a; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.8rem; border: 1px solid #e2e8f0; cursor: pointer;}
        .upload-section { border: 2px dashed var(--border-color); padding: 20px; border-radius: 8px; text-align: center; background: #f9fafb; margin-top: 20px; }
        .file-input-wrapper { margin: 15px 0; }
        #paymentReceipt { display: none; }
        .custom-file-upload { display: inline-block; padding: 10px 20px; background: var(--pure-black); color: white; border-radius: 4px; cursor: pointer; font-weight: 700; font-size: 0.8rem; }
        .horizontal-timeline { display: flex; justify-content: space-between; align-items: flex-start; margin: 40px 0; position: relative; }
        .timeline-line { position: absolute; top: 20px; left: 0; width: 100%; height: 4px; background: #e5e7eb; z-index: 1; }
        .timeline-line-fill { position: absolute; top: 20px; left: 0; height: 4px; background: var(--success-green); z-index: 2; transition: width 0.5s ease; }
        .step-item { position: relative; z-index: 3; text-align: center; width: 100px; }
        .step-circle { width: 40px; height: 40px; background: white; border: 4px solid #e5e7eb; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; }
        .step-item.active .step-circle { border-color: var(--success-green); color: var(--success-green); }
        .step-item.completed .step-circle { background: var(--success-green); border-color: var(--success-green); color: white; }
        .step-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .btn-action { background: black; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 700; text-transform: uppercase; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: none; align-items: center; justify-content: center; z-index: 1000; }
        .modal-box { background: white; padding: 30px; border-radius: 8px; width: 450px; }

        /* --- 新增：Search Bar 样式 --- */
        .search-wrapper { position: relative; flex-grow: 1; max-width: 350px; margin: 0 30px; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
        .search-bar { width: 100%; padding: 10px 15px 10px 40px; border-radius: 25px; border: 1px solid var(--border-color); outline: none; font-size: 0.85rem; transition: 0.3s; }
        .search-bar:focus { border-color: var(--pure-black); box-shadow: 0 0 0 4px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="../images/ys.jpg" alt="YS Logo">
            <span class="brand-name">YS ALUMINUM</span>
        </div>
        <ul class="sidebar-menu">
            <li class="active" onclick="showPage('Profile', this)">Profile</li>
            <li onclick="showPage('Appointment', this)">Appointment</li>
            <li onclick="showPage('Quotation', this)">Quotation</li>
            <li onclick="showPage('Invoice / Purchase', this)">Invoice / Purchase</li>
            <li onclick="showPage('Development Progress', this)">Development Progress</li>
            <li onclick="showPage('Payment', this)">Payment</li>
        </ul>
    </nav>

    <div class="main-container">
        <header class="navbar">
            <div class="nav-links">
                <a href="../homepage.php">Home</a>
                <a href="../product.php">Products</a>
                <a href="../aboutus.php">About Us</a>
            </div>

            <div class="search-wrapper">
                <i class="bi bi-search"></i>
                <input type="text" class="search-bar" id="tableSearch" placeholder="Search data..." onkeyup="filterData()">
            </div>

            <div class="account-area">
                <div style="font-size: 0.85rem; font-weight: 500;">
                    User: <span id="topBarUserName" style="font-weight: 700;"><?php echo htmlspecialchars($name); ?></span>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <main class="content-area">
            <div class="page-header">
                <h1 id="currentPageTitle" class="page-title">Profile</h1>
            </div>
            <div id="pageContent"></div>
        </main>
    </div>

    <div id="rejectModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Reject Quotation</h3>
            <textarea id="rejectReasonInput" style="width:100%; height:100px; padding:10px; margin-bottom:15px;" placeholder="Reason..."></textarea>
            <div style="text-align:right">
                <button class="btn-pdf" onclick="closeRejectModal()">Cancel</button>
                <button class="btn-action" style="background:var(--danger-red)" onclick="confirmReject()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        // --- 完全保留你的 JS 逻辑状态 ---
        let portalStatus = {
            quotationAccepted: false,
            quotationRejected: false,
            paymentPercent: 0, 
            devStep: 1 
        };

        const appointmentsFromDB = <?php echo json_encode($appointments); ?>;

        const customerData = {
            id: "<?php echo $customer_id; ?>",
            name: "<?php echo addslashes($user_data['name']); ?>",
            email: "<?php echo addslashes($user_data['email']); ?>",
            phone: "<?php echo addslashes($user_data['phone']); ?>",
            address: "<?php echo addslashes($user_data['address']); ?>",
            photo: "<?php echo !empty($user_data['profile_image']) ? $user_data['profile_image'] : ''; ?>"
        };

        // --- 完善：数据过滤逻辑 ---
        function filterData() {
            const val = document.getElementById('tableSearch').value.toLowerCase();
            const table = document.querySelector('#pageContent table');
            if (!table) return; // 如果当前页面没有表格则不执行
            
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if(row.innerText.toLowerCase().includes(val)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // --- 新增：处理头像预览 ---
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileAvatar').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // --- 新增：模拟保存个人资料 ---
       function saveProfile(event) {
        event.preventDefault(); // 防止页面刷新

        // 获取用户输入的新值
        const updatedData = {
            name: document.getElementById('update_name').value,
            phone: document.getElementById('update_phone').value,
            address: document.getElementById('update_address').value
        };

        // 发送到后端接口
        fetch('update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updatedData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully!');
                location.reload(); // 刷新以显示新数据
                document.getElementById('topBarUserName').innerText = document.getElementById('update_name').value;
                document.getElementById('profileCardName').innerText = document.getElementById('update_name').value;
            } else {
                alert('Update failed: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
       }

        // --- 完全保留你的 showPage 函数和所有分支条件 ---
        function showPage(pageName, element) {
            // 切换页面时清空搜索框
            document.getElementById('tableSearch').value = "";
            
            document.getElementById('currentPageTitle').innerText = pageName;
            const menuItems = document.querySelectorAll('.sidebar-menu li');
            menuItems.forEach(item => item.classList.remove('active'));
            
            if(element) {
                element.classList.add('active');
            } else {
                const items = document.querySelectorAll('.sidebar-menu li');
                items.forEach(li => {
                    if(li.innerText === pageName) li.classList.add('active');
                });
            }

            const container = document.getElementById('pageContent');

            if (pageName === 'Profile') {
                // --- 1. 这里是关键：判断是否有上传过的头像 ---
                const defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(customerData.name)}&background=f0f0f0&color=666666&size=150&bold=true`;
                const avatarSrc = (customerData.photo && customerData.photo !== 'null' && customerData.photo.trim() !== '') 
                      ? customerData.photo 
                      : defaultAvatar;
                container.innerHTML = `
                    <div style="animation: fadeIn 0.5s ease;">
                        <div class="data-card" style="padding: 0; overflow: hidden; border: none; margin-bottom: 60px; position: relative; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                            <div style="height: 120px; background: linear-gradient(135deg, #1a1a1a 0%, #434343 100%);"></div>
                            
                            <div style="padding: 0 40px 30px 40px; display: flex; align-items: flex-end; gap: 25px; margin-top: -50px;">
                                <div style="position: relative;">
                                   <img id="profileAvatar" 
                                    src="${avatarSrc}" 
                                    alt="User Avatar" 
                                    /* 关键：如果图片加载失败，强制换成默认字母头像 */
                                    onerror="this.onerror=null; this.src='${defaultAvatar}';" 
                                    style="width: 130px; 
                                            height: 130px; 
                                            border-radius: 15px; 
                                            border: 5px solid #fff; 
                                            object-fit: cover; 
                                            background: #f5f5f5; 
                                            display: block;
                                            box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                                    <button type="button" onclick="document.getElementById('avatarInput').click()" style="position: absolute; bottom: 8px; right: 8px; background: var(--pure-black); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                        <i class="bi bi-camera" style="font-size: 16px;"></i>
                                    </button>
                                    <input type="file" id="avatarInput" style="display:none" accept="image/*" onchange="previewAvatar(this)">
                                </div>
                                <div style="padding-bottom: 10px;">
                                    <h2 id="profileCardName" style="margin: 0; font-size: 1.5rem; font-weight: 800; color: #111;">${customerData.name}</h2>
                                    <p style="margin: 5px 0 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">
                                        <i class="bi bi-shield-check" style="color: var(--success-green);"></i> Verified Member
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="data-card">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px;">
                                <i class="bi bi-person-lines-fill" style="font-size: 1.2rem;"></i>
                                <h3 style="margin: 0; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px;">Account Details</h3>
                            </div>
                            
                            <form style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                                <div class="form-group">
                                    <label>User ID</label>
                                    <input type="text" value="CUST-${customerData.id.padStart(4, '0')}" disabled style="background: #f9f9f9; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" id="update_name" value="${customerData.name}">
                                </div>
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" value="${customerData.email}" disabled style="background: #f9f9f9; color: #999;">
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="text" id="update_phone" value="${customerData.phone}">
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label>Installation Address</label>
                                    <input type="text" id="update_address" value="${customerData.address}">
                                </div>
                                <div style="grid-column: span 2; display: flex; justify-content: flex-end; margin-top: 10px;">
                                    <button type="button" class="btn-action" onclick="saveProfile(event)" style="padding: 12px 35px; border-radius: 6px; letter-spacing: 1px;">
                                        Save Information
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>`;
} 
            else if (pageName === 'Appointment') {
                let apptRows = appointmentsFromDB.length > 0 ? appointmentsFromDB.map(appt => {
                    let color = appt.status === 'Confirmed' ? 'green' : 'orange';
                    return `<tr><td>${appt.appt_date}</td><td>${appt.appt_time}</td><td>${appt.product_name || 'Aluminium Work'}</td><td><b style="color:${color}">${appt.status}</b></td></tr>`;
                }).join('') : '<tr><td colspan="4" style="text-align:center">No appointment found.</td></tr>';

                container.innerHTML = `
                    <div class="data-card">
                        <table class="custom-table">
                            <thead><tr><th>Date</th><th>Time</th><th>Product</th><th>Status</th></tr></thead>
                            <tbody>${apptRows}</tbody>
                        </table>
                    </div>`;
            }
            else if (pageName === 'Quotation') {
                let actionHtml = '';
                let statusHtml = '<b style="color:blue">UPDATED</b>';

                if (portalStatus.quotationAccepted) {
                    actionHtml = '<span style="color:green; font-weight:800">ACCEPTED</span>';
                    statusHtml = '<b style="color:green">FINLIZE</b>';
                } else if (portalStatus.quotationRejected) {
                    actionHtml = '<span style="color:red; font-weight:800">REJECTED</span>';
                    statusHtml = '<b style="color:red">CANCELLED</b>';
                } else {
                    actionHtml = `
                        <button class="btn-action" style="background:var(--success-green); padding:5px 15px;" onclick="handleQuotation('accept')">ACCEPT</button>
                        <button class="btn-action" style="background:var(--danger-red); padding:5px 15px;" onclick="handleQuotation('reject')">REJECT</button>
                    `;
                }

                container.innerHTML = `
                    <div class="data-card">
                        <table class="custom-table">
                            <thead><tr><th>QTN ID</th><th>Date</th><th>File</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td>#QTN-2023-001</td><td>2023-11-16</td>
                                    <td><a href="../uploads/quotation_001.pdf" target="_blank" class="btn-pdf">📄 View PDF</a></td>
                                    <td>${statusHtml}</td>
                                    <td>${actionHtml}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>`;
            }
            else if (pageName === 'Invoice / Purchase') {
                container.innerHTML = `
                    <div class="data-card">
                        ${portalStatus.quotationAccepted ? `
                            <table class="custom-table">
                                <thead><tr><th>Invoice ID</th><th>Date</th><th>Total Amount</th><th>File</th></tr></thead>
                                <tbody>
                                    <tr>
                                        <td>#INV-9901</td><td>2026-03-17</td><td>RM 2,500.00</td>
                                        <td><a href="#" class="btn-pdf">📄 Download Invoice</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        ` : `
                            <div style="text-align:center; padding:20px;">
                                <p style="color:var(--text-muted); font-size:0.9rem;">Waiting for Admin to upload / Please accept Quotation first.</p>
                            </div>
                        `}
                    </div>`;
            }
            else if (pageName === 'Payment') {
                const fillWidth = portalStatus.paymentPercent + '%';
                container.innerHTML = `
                    <div class="data-card">
                        <h3 style="margin-top:0">Payment Status (View Only)</h3>
                        <div class="horizontal-timeline">
                            <div class="timeline-line"></div>
                            <div class="timeline-line-fill" style="width: ${fillWidth}"></div>
                            <div class="step-item ${portalStatus.paymentPercent >= 0 ? 'completed' : ''}"><div class="step-circle">0%</div><div class="step-label">Booked</div></div>
                            <div class="step-item ${portalStatus.paymentPercent >= 50 ? 'completed' : 'active'}"><div class="step-circle">50%</div><div class="step-label">Deposit</div></div>
                            <div class="step-item ${portalStatus.paymentPercent === 100 ? 'completed' : ''}"><div class="step-circle">100%</div><div class="step-label">Balance</div></div>
                        </div>
                    </div>
                    <div class="data-card">
                        <h3 style="margin-top:0">Upload Payment Receipt</h3>
                        <div class="upload-section">
                            <div class="file-input-wrapper">
                                <label for="paymentReceipt" class="custom-file-upload">Choose Receipt File</label>
                                <input type="file" id="paymentReceipt" onchange="updateFileName()">
                                <p id="fileNameDisplay" style="margin-top:10px; font-size:0.8rem; color:var(--success-green); font-weight:600;"></p>
                            </div>
                            <button class="btn-action" onclick="uploadReceipt()">Upload Receipt</button>
                        </div>
                    </div>`;
            }
            else if (pageName === 'Development Progress') {
                const devFill = ((portalStatus.devStep - 1) / 3) * 100 + '%';
                container.innerHTML = `
                    <div class="data-card">
                        <div class="horizontal-timeline">
                            <div class="timeline-line"></div>
                            <div class="timeline-line-fill" style="width: ${devFill}"></div>
                            <div class="step-item ${portalStatus.devStep >= 1 ? 'completed' : ''}"><div class="step-circle">1</div><div class="step-label">Measurement</div></div>
                            <div class="step-item ${portalStatus.devStep >= 2 ? 'completed' : ''}"><div class="step-circle">2</div><div class="step-label">Production</div></div>
                            <div class="step-item ${portalStatus.devStep >= 3 ? 'completed' : ''}"><div class="step-circle">3</div><div class="step-label">Installation</div></div>
                            <div class="step-item ${portalStatus.devStep >= 4 ? 'completed' : ''}"><div class="step-circle">4</div><div class="step-label">Completed</div></div>
                        </div>
                    </div>`;
            }
        }

        // --- 完全保留你的所有辅助 JS 函数 ---
        function updateFileName() {
            const input = document.getElementById('paymentReceipt');
            if(input.files[0]) document.getElementById('fileNameDisplay').innerText = "Selected: " + input.files[0].name;
        }

        function uploadReceipt() {
            const fileInput = document.getElementById('paymentReceipt');
            if(fileInput.files.length === 0) { alert("Please select a file first."); return; }
            alert("Receipt uploaded successfully! Admin will verify your payment.");
            fileInput.value = "";
            document.getElementById('fileNameDisplay').innerText = "";
        }

        function handleQuotation(action) {
            if(action === 'accept') {
                if(confirm('Accept this quotation?')) {
                    portalStatus.quotationAccepted = true;
                    showPage('Payment');
                }
            } else {
                document.getElementById('rejectModal').style.display = 'flex';
            }
        }

        function closeRejectModal() { document.getElementById('rejectModal').style.display = 'none'; }
        
        function confirmReject() {
            if(!document.getElementById('rejectReasonInput').value.trim()) { alert("Please enter a reason."); return; }
            portalStatus.quotationRejected = true;
            closeRejectModal();
            showPage('Quotation');
        }
        function previewAvatar(input) {
    if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // 1. 先做前端本地预览（让用户立刻看到选中的图）
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileAvatar').src = e.target.result;
            }
            reader.readAsDataURL(file);

            // 2. 立即上传到服务器
            const formData = new FormData();
            formData.append('avatar', file);

            fetch('upload_avatar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Avatar uploaded successfully!');
                    // 重要：把服务器返回的新路径存入本地变量，防止切换页面时丢失
                    customerData.photo = data.new_path; 
                } else {
                    alert('Upload failed: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
            }
        }
        window.onload = () => showPage('Profile', document.querySelector('.sidebar-menu li'));
    </script>
</body>
</html>