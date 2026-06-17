<?php
session_start();
require_once("../includes/db.php");

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// get user status and info
$sql_user = "SELECT status, name, email, phone, address, profile_image FROM customers WHERE customer_id = ?";
$status_check = mysqli_prepare($conn, $sql_user);
if (!$status_check) {
    die("Database Error (prepare): " . mysqli_error($conn));
}
mysqli_stmt_bind_param($status_check, "i", $customer_id);
mysqli_stmt_execute($status_check);
$user_result = mysqli_stmt_get_result($status_check);
$user_data = $user_result->fetch_assoc();

if (!$user_data || (int)$user_data['status'] !== 1) {
    session_destroy();
    header("Location: customer_login.php?error=Your account has been disabled.Please contact support.");
    exit();
}

$name = $user_data['name'];

// refresh session name for header display
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $new_name    = $_POST['name'];
    $new_phone   = $_POST['phone'];
    $new_address = $_POST['address'];
    $update_sql = "UPDATE customers SET name = ?, phone = ?, address = ? WHERE customer_id = ?";
    $upd_stmt = mysqli_prepare($conn, $update_sql);
    if ($upd_stmt) {
        mysqli_stmt_bind_param($upd_stmt, "sssi", $new_name, $new_phone, $new_address, $customer_id);
        if (mysqli_stmt_execute($upd_stmt)) {
            $_SESSION['name'] = $new_name;
            $user_data['name']    = $new_name;
            $user_data['phone']   = $new_phone;
            $user_data['address'] = $new_address;
            $name = $new_name;
        }
    }
}

// get quotations
$quotations = [];
$sql_qtn = "SELECT qtn_id, qtn_number, file_path, total_amount, status, created_at 
            FROM quotations 
            WHERE customer_id = ? 
            ORDER BY created_at DESC";
$stmt_qtn = mysqli_prepare($conn, $sql_qtn);
if ($stmt_qtn) {
    mysqli_stmt_bind_param($stmt_qtn, "i", $customer_id);
    mysqli_stmt_execute($stmt_qtn);
    $result_qtn = mysqli_stmt_get_result($stmt_qtn);
    while ($row = mysqli_fetch_assoc($result_qtn)) {
        $quotations[] = $row;
    }
} else {
    error_log("Prepare failed for quotations: " . mysqli_error($conn));
}

// check if any accepted quotation exists
$hasAcceptedQuotation = false;
foreach ($quotations as $q) {
    if ($q['status'] === 'Accepted') {
        $hasAcceptedQuotation = true;
        break;
    }
}

// get progress data for Progress page
$user_progress = [];
$sql_progress = "SELECT progress_step, status, notes, updated_at 
                 FROM project_progress 
                 WHERE customer_id = ? 
                 ORDER BY updated_at DESC";
$stmt_prog = mysqli_prepare($conn, $sql_progress);
if ($stmt_prog) {
    mysqli_stmt_bind_param($stmt_prog, "i", $customer_id);
    mysqli_stmt_execute($stmt_prog);
    $result_prog = mysqli_stmt_get_result($stmt_prog);
    while ($row = mysqli_fetch_assoc($result_prog)) {
        $user_progress[] = $row;
    }
} else {
    error_log("Prepare failed for progress: " . mysqli_error($conn));
}

// Build progress data structure for Progress page (based on the latest progress step of the accepted quotation)
$step_order = [
    'Deposit 50%'     => 0,
    'Order'           => 1,
    'Fabrication'     => 2,
    'Installation'    => 3,
    '30% on going job'=> 4,
    '20% complete job'=> 5
];
$step_names = array_keys($step_order);
$step_icons = ['bi-cash-stack', 'bi-receipt', 'bi-tools', 'bi-truck', 'bi-hourglass-split', 'bi-check2-circle'];
$total_steps = count($step_names);

$quotations_with_progress = [];
$accepted_quotations = array_filter($quotations, fn($q) => $q['status'] === 'Accepted');
foreach ($accepted_quotations as $qtn) {
    $qtn_id = $qtn['qtn_id'];
    
    // get product name for this quotation (first try product_id, if null then fallback to description first line)
    $product_name = '';
    $prod_query = "SELECT 
                    CASE 
                        WHEN qi.product_id IS NOT NULL THEN p.door_brand 
                        ELSE SUBSTRING_INDEX(qi.description, '\n', 1)
                    END as product_name
                   FROM quotation_items qi 
                   LEFT JOIN products p ON qi.product_id = p.product_id 
                   WHERE qi.qtn_id = $qtn_id LIMIT 1";
    $prod_res = mysqli_query($conn, $prod_query);
    if ($prod_res && $prod_row = mysqli_fetch_assoc($prod_res)) {
        $product_name = $prod_row['product_name'] ?: 'Product';
    }
    if (empty($product_name)) $product_name = 'Product';
    
    $sql_steps = "SELECT progress_step, status, notes, updated_at FROM project_progress WHERE qtn_id = $qtn_id ORDER BY updated_at ASC";
    $steps_res = mysqli_query($conn, $sql_steps);
    $progress_records = [];
    $step_status = array_fill(0, $total_steps, 'Pending');
    $step_notes = array_fill(0, $total_steps, '');
    $raw_records = [];
    while ($row = mysqli_fetch_assoc($steps_res)) {
        $raw_records[] = $row;
    }
    foreach ($raw_records as $row) {
        $step_name = $row['progress_step'];
        if (isset($step_order[$step_name])) {
            $idx = $step_order[$step_name];
            if ($row['status'] == 'Completed') {
                $step_status[$idx] = 'Completed';
                $step_notes[$idx] = $row['notes'] ?? '';
            } elseif ($row['status'] == 'In Progress') {
                $step_status[$idx] = 'In Progress';
                $step_notes[$idx] = $row['notes'] ?? '';
            }
        }
    }
    $progress_records = array_reverse($raw_records);
    $completed_steps = count(array_filter($step_status, fn($s) => $s == 'Completed'));
    $next_step_index = -1;
    for ($i = 0; $i < $total_steps; $i++) {
        if ($step_status[$i] != 'Completed') {
            $next_step_index = $i;
            break;
        }
    }
    // calculate unlocked payment stages based on progress (Deposit 50% always unlocked once accepted, then each subsequent stage unlocks with certain progress steps)
    $unlocked_payment_stages = [
        'deposit'  => true,   // Once quotation is accepted, deposit stage is always unlocked
        'progress' => false,
        'final'    => false
    ];
    if (isset($step_status[0]) && $step_status[0] == 'Completed') {
        $unlocked_payment_stages['progress'] = true;
    }
    if (isset($step_status[4]) && $step_status[4] == 'Completed') {
        $unlocked_payment_stages['final'] = true;
    }
    
    $quotations_with_progress[] = [
        'qtn_id' => $qtn_id,
        'qtn_number' => $qtn['qtn_number'],
        'product_name' => $product_name,
        'total_amount' => $qtn['total_amount'],
        'step_status' => $step_status,
        'step_notes' => $step_notes,
        'progress_records' => $progress_records,
        'completed_steps' => $completed_steps,
        'next_step_index' => $next_step_index,
        'unlocked_payment_stages' => $unlocked_payment_stages
    ];
}

// Get invoices and associated product names (if any)
$invoices = [];
$invoices_with_products = [];
$sql_inv = "SELECT inv_id, invoice_number, total_amount, IFNULL(final_amount, total_amount) AS final_amount, issue_date, due_date, status, file_path, qtn_id, stage
            FROM invoices 
            WHERE customer_id = ? 
            ORDER BY issue_date DESC";
$stmt_inv = mysqli_prepare($conn, $sql_inv);
if ($stmt_inv) {
    mysqli_stmt_bind_param($stmt_inv, "i", $customer_id);
    mysqli_stmt_execute($stmt_inv);
    $result_inv = mysqli_stmt_get_result($stmt_inv);
    while ($row = mysqli_fetch_assoc($result_inv)) {
        $invoices[] = $row;
        $product_name = '';
        if (!empty($row['qtn_id'])) {
            $prod_query = "SELECT 
                            CASE 
                                WHEN qi.product_id IS NOT NULL THEN p.door_brand 
                                ELSE SUBSTRING_INDEX(qi.description, '\n', 1)
                            END as product_name
                           FROM quotation_items qi 
                           LEFT JOIN products p ON qi.product_id = p.product_id 
                           WHERE qi.qtn_id = " . intval($row['qtn_id']) . " LIMIT 1";
            $prod_res = mysqli_query($conn, $prod_query);
            if ($prod_res && $prod_row = mysqli_fetch_assoc($prod_res)) {
                $product_name = $prod_row['product_name'] ?: 'Product';
            }
        }
        if (empty($product_name)) $product_name = 'No product';
        $invoices_with_products[] = [
            'inv_id' => $row['inv_id'],
            'invoice_number' => $row['invoice_number'],
            'final_amount' => $row['final_amount'],
            'issue_date' => $row['issue_date'],
            'due_date' => $row['due_date'],
            'file_path' => $row['file_path'],
            'product_name' => $product_name,
            'qtn_id' => $row['qtn_id'],
            'stage' => $row['stage'] 
        ];
    }
} else {
    error_log("Prepare failed for invoices: " . mysqli_error($conn));
}

// Get payment records
$payment_records = [];
$sql_pay = "SELECT id, qtn_id, stage, file_path, uploaded_at, status, staff_notes 
            FROM payment_records 
            WHERE customer_id = ? 
            ORDER BY uploaded_at DESC";
$stmt_pay = mysqli_prepare($conn, $sql_pay);
if ($stmt_pay) {
    mysqli_stmt_bind_param($stmt_pay, "i", $customer_id);
    mysqli_stmt_execute($stmt_pay);
    $result_pay = mysqli_stmt_get_result($stmt_pay);
    while ($row = mysqli_fetch_assoc($result_pay)) {
        $payment_records[] = $row;
    }
} else {
    error_log("Prepare failed for payment records: " . mysqli_error($conn));
}

// Manage profile image path (if exists)
$profile_image_raw = !empty($user_data['profile_image']) ? $user_data['profile_image'] : '';
$avatar_path = $profile_image_raw;
if (!empty($avatar_path) && strpos($avatar_path, '../') !== 0 && strpos($avatar_path, 'http') !== 0) {
    $avatar_path = '../' . $avatar_path;
}
$right_avatar_src = (!empty($avatar_path) && file_exists($avatar_path)) ? $avatar_path : "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=20304a&color=fff&size=40&rounded=true&bold=true";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/customer_dashboard.css">
    <link rel="stylesheet" href="../css/mobile.css">
</head>
<body>

<button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('show')">
    <i class="bi bi-list"></i>
</button>

<div class="sidebar">
    <div class="sidebar-brand">
        <img src="../images/ys.jpg" alt="YS Logo">
        <span>YS ALUMINIUM</span>
    </div>
    <div class="nav-menu">
        <div class="nav-section-title">Main Menu</div>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li class="active" onclick="showPage('Profile', this)"><i class="bi bi-person-circle"></i> Profile</li>
            <li onclick="showPage('Appointment', this)"><i class="bi bi-calendar-check"></i> Appointment</li>
            <li onclick="showPage('Quotation', this)"><i class="bi bi-file-earmark-text"></i> Quotation</li>
            <li onclick="showPage('Payment', this)"><i class="bi bi-upload"></i> Upload Payment</li>
            <li onclick="showPage('Development Progress', this)"><i class="bi bi-bar-chart-steps"></i> Progress</li>
            <li onclick="showPage('Invoice / Purchase', this)"><i class="bi bi-receipt"></i> Invoice / Purchase</li>
            <li class="logout-link" onclick="window.location.href='logout.php'"><i class="bi bi-box-arrow-left"></i> Logout</li>
        </ul>
    </div>
</div>

<header class="navbar">
    <div class="nav-links">
        <a href="../homepage.php">Home</a>
        <a href="../aboutus.php">About Us</a>
        <a href="../product.php">Products</a>
        <a href="../gallery.php">Gallery</a>
        <a href="../contactus.php">Contact Us</a>
    </div>
    <div class="search-wrapper" id="globalSearchWrapper" style="display: none;">
        <i class="bi bi-search"></i>
        <input type="text" class="search-bar" id="tableSearch" placeholder="Search data..." onkeyup="filterData()">
    </div>
    <div class="account-area" style="display: flex; align-items: center; gap: 10px;">
        <img src="<?php echo $right_avatar_src; ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e4e4e7;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=User&background=20304a&color=fff&size=40&rounded=true'">
        <span>User: <strong><?php echo htmlspecialchars($name); ?></strong></span>
    </div>
</header>

<div class="main-content">
    <div class="page-header">
        <h1 id="currentPageTitle" class="page-title">Profile</h1>
    </div>
    <div id="pageContent">Loading...</div>
</div>

<div id="rejectModal" class="modal-overlay"><div class="modal-box"><h3>Reject Quotation</h3><textarea id="rejectReasonInput" style="width:100%; height:100px; padding:10px; margin-bottom:15px;" placeholder="Reason..."></textarea><div style="text-align:right"><button class="btn-action" onclick="closeRejectModal()">Cancel</button><button class="btn-action" style="background:var(--danger-red); color:white;" onclick="confirmReject()">Confirm</button></div></div></div>
<div id="changePwdModal" class="modal-overlay"><div class="modal-box"><h3>Change Password</h3><div class="mb-3"><label class="form-label small fw-bold">Current Password</label><input type="password" id="modal_current_pwd" class="form-control"></div><div class="mb-3"><label class="form-label small fw-bold">New Password</label><input type="password" id="modal_new_pwd" class="form-control"></div><div class="mb-3"><label class="form-label small fw-bold">Confirm New Password</label><input type="password" id="modal_confirm_pwd" class="form-control"></div><div id="modalPwdMessage" class="mb-3" style="display:none;"></div><div style="text-align:right"><button class="btn-action" onclick="closeChangePwdModal()">Cancel</button><button class="btn-action" style="background:var(--primary-dark); color:white;" onclick="submitChangePassword()">Update Password</button></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const quotations = <?php echo json_encode($quotations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const hasAcceptedQuotation = <?php echo json_encode($hasAcceptedQuotation); ?>;
const progressFromDB = <?php echo json_encode($user_progress, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const invoicesFromDB = <?php echo json_encode($invoices, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const invoicesWithProducts = <?php echo json_encode($invoices_with_products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const paymentRecords = <?php echo json_encode($payment_records, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const customerData = {
    id: <?php echo json_encode($customer_id); ?>,
    name: <?php echo json_encode($user_data['name']); ?>,
    email: <?php echo json_encode($user_data['email']); ?>,
    phone: <?php echo json_encode($user_data['phone']); ?>,
    address: <?php echo json_encode($user_data['address']); ?>,
    photo: <?php echo json_encode($avatar_path); ?>
};

const quotationsWithProgress = <?php echo json_encode($quotations_with_progress, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

let portalStatus = { quotationAccepted: hasAcceptedQuotation, currentQtnId: null };

function recalcHasAccepted() { portalStatus.quotationAccepted = quotations.some(q => q.status === 'Accepted'); }

function filterData() {
    const val = document.getElementById('tableSearch').value.toLowerCase();
    const table = document.querySelector('#pageContent table');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => { row.innerText.toLowerCase().includes(val) ? row.style.display = "" : row.style.display = "none"; });
}

function filterPaymentHistory() {
    const input = document.getElementById('paymentSearchInput');
    if (!input) return;
    const filter = input.value.toLowerCase();
    const table = document.getElementById('paymentHistoryTable');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

function filterProgressCards() {
    const keyword = document.getElementById('progressSearchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.progress-card');
    cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        if (keyword === '' || text.includes(keyword)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function saveProfile(event) {
    event.preventDefault();
    fetch('update_profile.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: document.getElementById('update_name').value, phone: document.getElementById('update_phone').value, address: document.getElementById('update_address').value })
    }).then(res => res.json()).then(data => { if(data.success){ alert('Profile updated!'); location.reload(); } else alert('Update failed'); }).catch(err=>alert('Error updating profile'));
}

function refreshQuotationPage() { showPage('Quotation', document.querySelector('.nav-menu li.active')); }

function handleQuotation(qtnId, action) {
    if(!qtnId) return;
    if(action === 'accept') { if(confirm('Accept this quotation?')) updateQuotationStatus(qtnId, 'Accepted'); }
    else { window.pendingRejectQtnId = qtnId; document.getElementById('rejectModal').style.display = 'flex'; }
}

function updateQuotationStatus(qtnId, newStatus, reason='') {
    fetch('update_quotation_status.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ qtn_id: qtnId, status: newStatus, reason: reason })
    }).then(res => res.json()).then(data => {
        if(data.success) {
            alert('Quotation ' + newStatus);
            window.location.href = 'customer_dashboard.php?active_section=quotation';
        } else {
            alert('Error: '+data.message);
        }
    }).catch(error => { console.error(error); alert('Server error'); });
}

function closeRejectModal() { document.getElementById('rejectModal').style.display = 'none'; window.pendingRejectQtnId = null; }
function confirmReject() {
    const reason = document.getElementById('rejectReasonInput').value.trim();
    if(!reason) { alert("Please enter reason."); return; }
    if(window.pendingRejectQtnId) updateQuotationStatus(window.pendingRejectQtnId, 'Rejected', reason);
    closeRejectModal();
}

function mapStepToPaymentIndex(stepName) {
    const mapping = {
        "Deposit 50%": 0,      // now first
        "Order": 1,            // second
        "30% on going job": 2,
        "20% complete job": 3,
        "Completed": 4,
        "Successful": 4
    };
    return mapping.hasOwnProperty(stepName) ? mapping[stepName] : -1;
}
function getPaymentProgressIndex() {
    if(!progressFromDB || progressFromDB.length === 0) return -1;
    return mapStepToPaymentIndex(progressFromDB[0].progress_step);
}

function mapStepToProgressIndex(stepName) {
    const mapping = {
        "Order": 0, "Order Placed": 0,
        "Deposit 50%": 1, "Measurement": 1,
        "Fabrication": 2,
        "Installation": 3,
        "30% on going job": 4,
        "20% complete job": 5, "Completed": 5
    };
    return mapping.hasOwnProperty(stepName) ? mapping[stepName] : -1;
}
function getProgressPageIndex() {
    if(!progressFromDB || progressFromDB.length === 0) return -1;
    return mapStepToProgressIndex(progressFromDB[0].progress_step);
}

function openChangePwdModal() { 
    document.getElementById('modal_current_pwd').value = '';
    document.getElementById('modal_new_pwd').value = '';
    document.getElementById('modal_confirm_pwd').value = '';
    document.getElementById('modalPwdMessage').style.display = 'none';
    document.getElementById('changePwdModal').style.display = 'flex';
}
function closeChangePwdModal() { document.getElementById('changePwdModal').style.display = 'none'; }
function submitChangePassword() {
    const currentPwd = document.getElementById('modal_current_pwd').value;
    const newPwd = document.getElementById('modal_new_pwd').value;
    const confirmPwd = document.getElementById('modal_confirm_pwd').value;
    const msgDiv = document.getElementById('modalPwdMessage');
    if(!currentPwd || !newPwd || !confirmPwd) { msgDiv.innerHTML = '<div class="alert alert-danger">All fields required.</div>'; msgDiv.style.display='block'; return; }
    if(newPwd !== confirmPwd) { msgDiv.innerHTML = '<div class="alert alert-danger">Passwords do not match.</div>'; msgDiv.style.display='block'; return; }
    if(newPwd.length<6) { msgDiv.innerHTML = '<div class="alert alert-danger">Password min 6 chars.</div>'; msgDiv.style.display='block'; return; }
    fetch('customer_change_password.php', {
        method: 'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ current_password: currentPwd, new_password: newPwd })
    }).then(res=>res.json()).then(data=>{
        msgDiv.style.display='block';
        if(data.success){ msgDiv.innerHTML='<div class="alert alert-success">Password changed!</div>'; setTimeout(closeChangePwdModal,1500); }
        else msgDiv.innerHTML='<div class="alert alert-danger">'+data.message+'</div>';
    }).catch(err=>{ msgDiv.innerHTML='<div class="alert alert-danger">Server error.</div>'; msgDiv.style.display='block'; });
}

function previewAvatar(input) {
    if(input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = e => document.getElementById('profileAvatar').src = e.target.result;
        reader.readAsDataURL(file);
        const formData = new FormData();
        formData.append('avatar', file);
        fetch('upload_avatar.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => { if(data.success) { alert('Avatar uploaded'); customerData.photo = data.new_path; } else alert('Upload failed'); })
            .catch(err=>alert('Upload error'));
    }
}

function escapeHtml(str) { if(!str) return ''; return str.replace(/[&<>]/g, function(m){ if(m==='&') return '&amp;'; if(m==='<') return '&lt;'; if(m==='>') return '&gt;'; return m; }); }
function validateUpload() { const fileInput = document.getElementById('payment_receipt'); if(!fileInput.files.length) { alert('Please select a file'); return false; } return true; }

function showPage(pageName, element) {
    console.log("showPage called:", pageName);
    sessionStorage.setItem('currentCustomerPage', pageName);
    
    try {
        const globalSearchWrapper = document.getElementById('globalSearchWrapper');
        if(pageName === 'Quotation') {
            globalSearchWrapper.style.display = 'flex';
        } else {
            globalSearchWrapper.style.display = 'none';
        }
        document.getElementById('tableSearch').value = "";
        
        const menuItems = document.querySelectorAll('.nav-menu li');
        menuItems.forEach(item => item.classList.remove('active'));
        if(element && element.closest('.nav-menu')) element.classList.add('active');
        document.getElementById('currentPageTitle').innerText = pageName;
        const container = document.getElementById('pageContent');
        if(!container) { console.error("pageContent element missing"); return; }

        // ==================== Profile ====================
        if(pageName === 'Profile') {
            const defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(customerData.name)}&background=20304a&color=fff&size=150&bold=true&rounded=true`;
            let avatarSrc = defaultAvatar;
            if (customerData.photo && customerData.photo !== 'null' && customerData.photo.trim() !== '') {
                avatarSrc = customerData.photo;
            }
            const safeAddress = (customerData.address || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            const addressWithMap = `<div class="address-with-map"><input type="text" class="form-control" id="update_address" value="${safeAddress}"><a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(customerData.address)}" target="_blank" class="map-icon"><i class="bi bi-geo-alt-fill"></i></a></div>`;
            container.innerHTML = `<div class="card-custom"><div class="profile-cover"></div><div style="display: flex; align-items: flex-end; gap: 25px; padding: 0 30px 20px 30px;"><div class="profile-avatar"><img id="profileAvatar" src="${avatarSrc}" onerror="this.onerror=null; this.src='${defaultAvatar}';"><div class="camera-icon" onclick="document.getElementById('avatarInput').click()"><i class="bi bi-camera"></i></div><input type="file" id="avatarInput" style="display:none" accept="image/*" onchange="previewAvatar(this)"></div><div class="profile-info" style="padding-left:0;"><h2 id="profileCardName" style="margin:0;font-size:1.5rem;font-weight:800;">${escapeHtml(customerData.name)}</h2><p class="text-muted"><i class="bi bi-shield-check" style="color:var(--success-green);"></i> Verified Member</p></div></div><div class="card-header-custom"><h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i> Account Details</h5></div><div class="p-4"><form style="display:grid;grid-template-columns:1fr 1fr;gap:25px;"><div><label class="small fw-bold">User ID</label><input type="text" class="form-control" value="CUST-${String(customerData.id).padStart(4,'0')}" disabled></div><div><label class="small fw-bold">Full Name</label><input type="text" class="form-control" id="update_name" value="${escapeHtml(customerData.name)}"></div><div><label class="small fw-bold">Email</label><input type="email" class="form-control" value="${escapeHtml(customerData.email)}" disabled></div><div><label class="small fw-bold">Phone</label><input type="text" class="form-control" id="update_phone" value="${escapeHtml(customerData.phone)}"></div><div style="grid-column:span 2;"><label class="small fw-bold">Address</label>${addressWithMap}</div><div style="grid-column:span 2; display:flex; justify-content:space-between;"><button type="button" class="btn-outline-secondary" onclick="openChangePwdModal()"><i class="bi bi-key me-2"></i>Change Password</button><button type="button" class="btn-dark-custom" onclick="saveProfile(event)">Save Information</button></div></form></div></div>`;
        }
        // ==================== Appointment ====================
        else if (pageName === 'Appointment') {
            const customerEmail = customerData.email;
            let calendarHtml = '';
            if (customerEmail && customerEmail.trim() !== '') {
                const calendarSrc = `https://calendar.google.com/calendar/embed?src=${encodeURIComponent(customerEmail)}&ctz=Asia%2FKuala_Lumpur`;
                calendarHtml = `
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-google me-2"></i>My Google Calendar</h5>
                        </div>
                        <div class="card-body p-0">
                            <iframe 
                                src="${calendarSrc}"
                                style="border: 0; width: 100%; height: 600px;" 
                                frameborder="0" 
                                scrolling="no">
                            </iframe>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 p-3">
                            <div class="alert alert-warning" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Privacy Notice:</strong> To view your calendar here, it must be <strong>publicly shared</strong>. 
                                This means anyone with your email address may see your events. 
                                We strongly recommend you click the <strong>View Your Google Calender</strong> button to see your calender.
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#calendarHelp" aria-expanded="false" aria-controls="calendarHelp">
                                    <i class="bi bi-question-circle me-1"></i> How to make your calendar public (optional)
                                </button>
                                <a href="https://calendar.google.com/calendar" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> View Your Google Calendar
                                </a>
                            </div>
                            <div class="collapse mt-2" id="calendarHelp">
                                <div class="card card-body bg-light">
                                    <ol class="mb-0">
                                        <li>Open <a href="https://calendar.google.com" target="_blank">Google Calendar</a>.</li>
                                        <li>Click the gear icon <i class="bi bi-gear"></i> (Settings) and select <strong>"Settings and sharing"</strong>.</li>
                                        <li>Under <strong>"My calendars"</strong>, click the name of the calendar you want to embed (usually your email address).</li>
                                        <li>Scroll to <strong>"Access permissions"</strong> and check the box <strong>"Make available to public"</strong>.</li>
                                        <li>From the dropdown, choose <strong>"See all event details"</strong> (not just "Free/Busy").</li>
                                        <li>Your calendar is now public. Return to this page and refresh.</li>
                                    </ol>
                                    <p class="text-muted small mt-2"><i class="bi bi-info-circle"></i> Remember: only share a calendar meant for business appointments.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }else {
                calendarHtml = `
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-google me-2"></i>My Google Calendar</h5>
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-exclamation-circle fs-1 text-warning mb-3 d-block"></i>
                            <p>No email address found for your account. Please contact support.</p>
                        </div>
                    </div>
                `;
            }
            container.innerHTML = calendarHtml;
        }
        // ==================== Quotation ====================
        else if(pageName === 'Quotation') {
            if(!quotations.length) { 
                container.innerHTML = `<div class="card-custom"><div class="p-4 text-center">No quotation available.</div></div>`; 
                return; 
            }
            let rowsHtml = '';
            for (let q of quotations) {
                let total = parseFloat(q.total_amount);
                let deposit = total * 0.5;
                let progress = total * 0.3;
                let final = total * 0.2;
                
                let statusClass = q.status === 'Accepted' ? 'status-active' : (q.status === 'Rejected' ? 'status-rejected' : (q.status === 'Updated' ? 'status-updated' : 'status-pending'));
                let actionHtml = '';
                if (q.status === 'Pending' || q.status === 'Updated') {
                    actionHtml = `<button class="btn btn-sm btn-success me-2" onclick="handleQuotation(${q.qtn_id},'accept')">Accept</button>
                                <button class="btn btn-sm btn-danger" onclick="handleQuotation(${q.qtn_id},'reject')">Reject</button>`;
                } else {
                    actionHtml = '<span class="text-muted">No action</span>';
                }
                rowsHtml += `<tr>
                    <td><span style="color: #000000; font-weight: 500;">${escapeHtml(q.qtn_number)}</span></td>
                    <td>${q.created_at.split(' ')[0]}</td> 
                    <td>RM ${deposit.toFixed(2)}</td>
                    <td>RM ${progress.toFixed(2)}</td>
                    <td>RM ${final.toFixed(2)}</td>
                    <td>RM ${total.toFixed(2)}</td> 
                    <td><a href="view_pdf.php?id=${q.qtn_id}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-pdf"></i> View PDF</a></td>
                    <td><span class="status-badge ${statusClass}">${q.status}</span></td>
                    <td>${actionHtml}</td>
                </tr>`;
            }
            container.innerHTML = `<div class="card-custom">
                <div class="card-header-custom"><h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i> Quotations</h5></div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>QTN Number</th>
                                <th>Date</th>
                                <th>50% Deposit</th>
                                <th>30% Progress</th>
                                <th>20% Final</th>
                                <th>Total Amount</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </div>
            </div>`;
        }
        // ==================== Progress ====================
        else if(pageName === 'Development Progress') {
            if (quotationsWithProgress.length === 0) {
                container.innerHTML = `<div class="card-custom"><div class="p-4 text-center text-muted">No accepted quotations with progress yet.</div></div>`;
                return;
            }
            const stepNames = ["Deposit 50%", "Order", "Fabrication", "Installation", "30% on going job", "20% complete job"];
            const progressStepIcons = ["bi-cash-stack", "bi-receipt", "bi-tools", "bi-truck", "bi-hourglass-split", "bi-check2-circle"];
            const totalSteps = stepNames.length;
            
            let allHtml = `
                <div class="card-custom mb-3">
                    <div class="card-header-custom">
                        <h5 class="mb-0"><i class="bi bi-search me-2"></i>Search Quotations</h5>
                    </div>
                    <div class="p-3">
                        <input type="text" id="progressSearchInput" class="form-control" placeholder="Search by quotation number or product name..." onkeyup="filterProgressCards()">
                    </div>
                </div>
                <div id="progressCardsContainer">
            `;
            for (let q of quotationsWithProgress) {
                const stepStatus = q.step_status;
                const stepNotes = q.step_notes;
                const completedSteps = q.completed_steps;
                const progressPercent = (completedSteps / totalSteps) * 100;
                let timelineHtml = '';
                for (let idx = 0; idx < stepNames.length; idx++) {
                    const step = stepNames[idx];
                    const isCompleted = stepStatus[idx] === 'Completed';
                    const isInProgress = stepStatus[idx] === 'In Progress';
                    let circleBg = 'white';
                    let circleBorder = '#e5e7eb';
                    let iconColor = '#a1a1aa';
                    let textColor = '#a1a1aa';
                    let statusBadgeText = '';
                    let statusBadgeClass = '';
                    if (isCompleted) {
                        circleBg = '#22c55e';
                        circleBorder = '#22c55e';
                        iconColor = 'white';
                        textColor = '#27272a';
                        statusBadgeText = 'Done';
                        statusBadgeClass = 'bg-success';
                    } else if (isInProgress) {
                        circleBg = '#3b82f6';
                        circleBorder = '#3b82f6';
                        iconColor = 'white';
                        textColor = '#3b82f6';
                        statusBadgeText = 'In Progress';
                        statusBadgeClass = 'bg-primary';
                    } else {
                        statusBadgeText = 'Pending';
                        statusBadgeClass = 'bg-primary';
                    }
                    timelineHtml += `
                        <div class="text-center" style="flex: 1;">
                            <div style="width: 45px; height: 45px; margin: 0 auto 8px; background: ${circleBg}; border: 3px solid ${circleBorder}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: ${iconColor}; font-size: 1.2rem;">
                                <i class="bi ${progressStepIcons[idx]}"></i>
                            </div>
                            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: ${textColor};">${step}</div>
                            <span class="badge ${statusBadgeClass} mt-2">${statusBadgeText}</span>
                            ${isCompleted && stepNotes[idx] ? `<div class="small text-muted mt-1">Note: ${escapeHtml(stepNotes[idx]).replace(/\n/g,'<br>')}</div>` : ''}
                        </div>
                    `;
                }
                let historyHtml = '';
                if (q.progress_records && q.progress_records.length > 0) {
                    historyHtml = '<div style="max-height: 300px; overflow-y: auto;">';
                    for (let rec of q.progress_records) {
                        const dotColor = rec.status === 'Completed' ? '#22c55e' : '#d4d4d8';
                        const statusBadge = rec.status === 'Completed' ? 'status-active' : (rec.status === 'In Progress' ? 'status-pending' : 'status-inactive');
                        historyHtml += `
                            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                                <div style="margin-top: 4px;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background: ${dotColor}; border: 2px solid white; box-shadow: 0 0 0 1px ${dotColor};"></div>
                                </div>
                                <div>
                                    <div class="fw-bold">${escapeHtml(rec.progress_step)}</div>
                                    <div><span class="status-badge ${statusBadge}" style="font-size: 0.7rem; padding: 2px 8px;">${rec.status}</span></div>
                                    ${rec.notes ? `<div class="small text-muted mt-1"><i class="bi bi-chat-left-text"></i> Note: ${escapeHtml(rec.notes).replace(/\n/g,'<br>')}</div>` : ''}
                                    <div class="small text-muted mt-1">${new Date(rec.updated_at).toLocaleDateString()}, ${new Date(rec.updated_at).toLocaleTimeString()}</div>
                                </div>
                            </div>
                        `;
                    }
                    historyHtml += '</div>';
                } else {
                    historyHtml = '<div class="text-muted text-center py-3">No progress records yet.</div>';
                }
                
                allHtml += `
                    <div class="card-custom mb-4 progress-card">
                        <div class="card-header-custom">
                            <div>
                                <h5 class="mb-0"><code>${escapeHtml(q.qtn_number)}</code></h5>
                                <span class="small text-muted">${escapeHtml(q.product_name)}</span>
                            </div>
                            <span class="badge bg-secondary">${completedSteps}/${totalSteps} steps</span>
                        </div>
                        <div class="card-body p-4">
                            <div style="position: relative; margin: 30px 0 20px;">
                                <div style="position: absolute; top: 22px; left: 5%; width: 90%; height: 3px; background: #e5e7eb; z-index: 1;"></div>
                                <div style="position: absolute; top: 22px; left: 5%; width: ${progressPercent}%; max-width: 90%; height: 3px; background: #22c55e; z-index: 1; transition: width 0.3s ease;"></div>
                                <div class="d-flex justify-content-between" style="position: relative; z-index: 2;">
                                    ${timelineHtml}
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Tracking Details</h6>
                                ${historyHtml}
                            </div>
                        </div>
                    </div>
                `;
            }
            allHtml += `</div>`;
            container.innerHTML = allHtml;
            if (document.getElementById('progressSearchInput')) {
                document.getElementById('progressSearchInput').addEventListener('keyup', filterProgressCards);
            }
        }
        // ==================== Payment ====================
        else if(pageName === 'Payment') {
            const acceptedQuotations = quotations.filter(q => q.status === 'Accepted');
            const paymentMap = {};
            paymentRecords.forEach(rec => { const key = `${rec.qtn_id}_${rec.stage || 'deposit'}`; paymentMap[key] = rec; });

            let quotationsHtml = '';
            if (acceptedQuotations.length === 0) {
                quotationsHtml = '<div class="alert alert-info">No accepted quotations yet. Please accept a quotation first.</div>';
            } else {
                acceptedQuotations.forEach(q => {
                    const total = parseFloat(q.total_amount).toFixed(2);
                    const depositAmount = (parseFloat(q.total_amount) * 0.5).toFixed(2);
                    const progressAmount = (parseFloat(q.total_amount) * 0.3).toFixed(2);
                    const finalAmount = (parseFloat(q.total_amount) * 0.2).toFixed(2);
                    const progressData = quotationsWithProgress.find(p => p.qtn_id === q.qtn_id);
                    const unlocked = progressData ? progressData.unlocked_payment_stages : { deposit: true, progress: false, final: false };

                    const stages = [
                        { key:'deposit', label:'50% Deposit', amount:depositAmount, open:unlocked.deposit, percent:50 },
                        { key:'progress', label:'30% Progress', amount:progressAmount, open:unlocked.progress, percent:30 },
                        { key:'final', label:'20% Final', amount:finalAmount, open:unlocked.final, percent:20 }
                    ];

                    let stagesCardsHtml = '<div class="row g-3 mb-3">';
                    stages.forEach(stage => {
                        const paymentKey = `${q.qtn_id}_${stage.key}`;
                        const paymentRec = paymentMap[paymentKey];
                        const status = paymentRec ? paymentRec.status : null;
                        const isUnlocked = stage.open;
                        let statusBadgeClass = '', statusText = '';
                        if (status === 'Verified') { statusBadgeClass = 'bg-success'; statusText = 'Verified'; }
                        else if (status === 'Pending') { statusBadgeClass = 'bg-primary'; statusText = 'Pending Review'; }
                        else if (status === 'Rejected') { statusBadgeClass = 'bg-danger'; statusText = 'Rejected'; }
                        else {
                            statusBadgeClass = 'bg-secondary';
                            statusText = isUnlocked ? 'Not uploaded' : 'Locked';
                        }
                        const stageAmount = parseFloat(stage.amount).toFixed(2);
                        const uploadedAt = paymentRec ? paymentRec.uploaded_at : null;

                        stagesCardsHtml += `
                            <div class="col-md-4">
                                <div class="payment-stage-card h-100 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong>${stage.label}</strong>
                                        <span class="badge ${statusBadgeClass}">${statusText}</span>
                                    </div>
                                    <div class="small text-muted mb-2">Amount: RM ${stageAmount}</div>
                                    ${uploadedAt ? `<div class="small text-muted mb-2"><i class="bi bi-clock"></i> Uploaded: ${new Date(uploadedAt).toLocaleString()}</div>` : '<div class="small text-muted mb-2">—</div>'}
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        ${paymentRec && paymentRec.file_path ? `<a href="../${paymentRec.file_path}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Receipt</a>` : '<span></span>'}
                                        <div>
                                            ${status === 'Pending' ? `
                                                <button class="btn btn-sm btn-warning" onclick="uploadForQuotationStage(${q.qtn_id}, '${stage.key}')">Re-upload</button>
                                            ` : (status === 'Rejected' ? `
                                                <button class="btn btn-sm btn-warning" onclick="uploadForQuotationStage(${q.qtn_id}, '${stage.key}')">Re-upload</button>
                                            ` : (isUnlocked && !paymentRec ? `
                                                <button class="btn btn-sm btn-warning" onclick="uploadForQuotationStage(${q.qtn_id}, '${stage.key}')">Upload ${stage.percent}%</button>
                                            ` : ''))}
                                        </div>
                                    </div>
                                    ${paymentRec && paymentRec.staff_notes ? `<div class="small text-muted mt-2">Note: ${escapeHtml(paymentRec.staff_notes)}</div>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    stagesCardsHtml += '</div>';

                    quotationsHtml += `
                        <div class="card mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${escapeHtml(q.qtn_number)}</strong> - ${escapeHtml(progressData ? progressData.product_name : 'Product')}
                                </div>
                                <span class="badge bg-secondary">Total: RM ${total}</span>
                            </div>
                            <div class="card-body">
                                ${stagesCardsHtml}
                            </div>
                        </div>
                    `;
                });
            }

            let historyRows = '';
            if (paymentRecords.length === 0) {
                historyRows = '<tr><td colspan="5" class="text-center">No payment history.</td></tr>';
            } else {
                paymentRecords.forEach(rec => {
                    const qtn = quotations.find(q => q.qtn_id == rec.qtn_id);
                    const qtnNum = qtn ? qtn.qtn_number : 'N/A';
                    let stageName = '';
                    if (rec.stage === 'deposit') stageName = '50% Deposit';
                    else if (rec.stage === 'progress') stageName = '30% Progress';
                    else if (rec.stage === 'final') stageName = '20% Final';
                    else stageName = rec.stage;
                    const statusBadge = rec.status === 'Verified' ? 'bg-success' : (rec.status === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark');
                    historyRows += `<tr>
                        <td><code>${escapeHtml(qtnNum)}</code></td>
                        <td>${stageName}</td>
                        <td><a href="view_payment.php?id=${rec.id}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> View</a></td>
                        <td>${rec.uploaded_at}</td>
                        <td><span class="badge ${statusBadge}">${rec.status}</span></td>
                    </tr>`;
                });
            }

            container.innerHTML = `
                <div class="card-custom mb-3">
                    <div class="card-header-custom">
                        <h5 class="mb-0"><i class="bi bi-search me-2"></i> Search Payment History</h5>
                    </div>
                    <div class="p-3">
                        <input type="text" id="paymentSearchInput" class="form-control" placeholder="Search by Quotation Number, Stage, Status..." onkeyup="filterPaymentHistory()">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-7">
                        <div class="card-custom"><div class="card-header-custom"><h5 class="mb-0"><i class="bi bi-receipt"></i> Payment Stages</h5></div><div class="p-3">${quotationsHtml}</div></div>
                    </div>
                    <div class="col-md-5">
                        <div class="card-custom"><div class="card-header-custom"><h5 class="mb-0"><i class="bi bi-bank"></i> Bank Transfer Details</h5></div><div class="p-3"><div class="payment-instruction"><strong>Maybank</strong><br>Account Name: YS Aluminium Sdn Bhd<br>Account Number: 5123-4567-8901<br>Reference: Your Name + Quotation Number + Stage<br><small class="text-muted">Please upload the transaction slip after payment.</small></div></div></div>
                        <div class="card-custom mt-3"><div class="card-header-custom"><h5 class="mb-0"><i class="bi bi-clock-history"></i> Payment History</h5></div><div class="p-3">
                            <div class="table-responsive">
                                <table class="table table-sm" id="paymentHistoryTable">
                                    <thead><tr><th>Quotation</th><th>Stage</th><th>File</th><th>Date</th><th>Status</th></tr></thead>
                                    <tbody>${historyRows}</tbody>
                                </table>
                            </div>
                        </div></div>
                    </div>
                </div>
            `;

            window.uploadForQuotationStage = async function(qtnId, stage) {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = 'image/*,application/pdf';
                fileInput.style.display = 'none';
                document.body.appendChild(fileInput);
                fileInput.onchange = async function() {
                    if (!fileInput.files.length) { fileInput.remove(); return; }
                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('qtn_id', qtnId);
                    formData.append('stage', stage);
                    formData.append('payment_receipt', file);
                    try {
                        const response = await fetch('upload_payment.php', { method: 'POST', body: formData });
                        const text = await response.text();
                        if (text.includes('success') || text.includes('true')) { alert('Payment uploaded successfully!'); location.reload(); }
                        else alert('Upload failed: ' + text);
                    } catch(err) { alert('Upload error: ' + err.message); }
                    finally { fileInput.remove(); }
                };
                fileInput.click();
            };
        }
        // ==================== Invoice / Purchase ====================
        else if(pageName === 'Invoice / Purchase') {
            const acceptedQuotations = quotations.filter(q => q.status === 'Accepted');
            
            if (acceptedQuotations.length === 0) {
                container.innerHTML = `<div class="card-custom"><div class="p-4 text-center text-muted">No accepted quotations found. Once you accept a quotation, invoices will appear here.</div></div>`;
                return;
            }

            const paymentMap = {};
            paymentRecords.forEach(rec => {
                const key = `${rec.qtn_id}_${rec.stage || 'deposit'}`;
                paymentMap[key] = rec;
            });

            const invoiceMap = {};
            invoicesWithProducts.forEach(inv => {
                if (inv.stage) {
                    const key = `${inv.qtn_id}_${inv.stage}`;
                    invoiceMap[key] = inv;
                }
            });

            const stages = [
                { key: 'deposit',  label: '50% Deposit',  percent: 50, icon: 'bi-cash-stack' },
                { key: 'progress', label: '30% Progress', percent: 30, icon: 'bi-tools' },
                { key: 'final',    label: '20% Final',    percent: 20, icon: 'bi-check2-circle' }
            ];
            
            let allHtml = '';
            
            for (let q of acceptedQuotations) {
                const total = parseFloat(q.total_amount).toFixed(2);
                const depositAmount = (parseFloat(q.total_amount) * 0.5).toFixed(2);
                const progressAmount = (parseFloat(q.total_amount) * 0.3).toFixed(2);
                const finalAmount = (parseFloat(q.total_amount) * 0.2).toFixed(2);

                const progressData = quotationsWithProgress.find(p => p.qtn_id === q.qtn_id);
                const productName = progressData ? progressData.product_name : 'Product';
                
                let stagesHtml = '<div class="row g-3 mb-4">';
                
                for (let stage of stages) {
                    const key = `${q.qtn_id}_${stage.key}`;
                    const paymentRec = paymentMap[key];
                    const invoiceRec = invoiceMap[key];
                    
                    let stageAmount = 0;
                    if (stage.key === 'deposit') stageAmount = depositAmount;
                    else if (stage.key === 'progress') stageAmount = progressAmount;
                    else stageAmount = finalAmount;

                    const isVerified = paymentRec && paymentRec.status === 'Verified';
                    
                    let statusBadgeClass = 'bg-secondary';
                    let statusText = 'Not yet';
                    let iconHtml = '<i class="bi bi-hourglass-split me-1"></i>';
                    
                    if (isVerified) {
                        statusBadgeClass = 'bg-success';
                        statusText = 'Done ✓';
                        iconHtml = '<i class="bi bi-check-circle-fill me-1"></i>';
                    } else if (paymentRec && paymentRec.status === 'Pending') {
                        statusBadgeClass = 'bg-warning text-dark';
                        statusText = 'Pending';
                        iconHtml = '<i class="bi bi-clock-history me-1"></i>';
                    } else if (paymentRec && paymentRec.status === 'Rejected') {
                        statusBadgeClass = 'bg-danger';
                        statusText = 'Rejected';
                        iconHtml = '<i class="bi bi-exclamation-triangle-fill me-1"></i>';
                    }

                    let invoiceHtml = '';
                    if (invoiceRec && invoiceRec.file_path) {
                        invoiceHtml = `<a href="view_invoice.php?id=${invoiceRec.inv_id}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 w-100"><i class="bi bi-file-pdf"></i> View Invoice</a>`;
                    } else if (isVerified && !invoiceRec) {
                        invoiceHtml = `<span class="text-muted small mt-2 d-block">Invoice will be generated soon</span>`;
                    } else {
                        invoiceHtml = `<span class="text-muted small mt-2 d-block">—</span>`;
                    }
                    
                    stagesHtml += `
                        <div class="col-md-4">
                            <div class="payment-stage-card h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong>${iconHtml} ${stage.label}</strong>
                                    <span class="badge ${statusBadgeClass}">${statusText}</span>
                                </div>
                                <div class="small text-muted mb-2">Amount: RM ${stageAmount}</div>
                                ${paymentRec && paymentRec.uploaded_at ? `<div class="small text-muted mb-2"><i class="bi bi-clock"></i> Paid on: ${new Date(paymentRec.uploaded_at).toLocaleDateString()}</div>` : ''}
                                <div class="mt-auto">
                                    ${invoiceHtml}
                                </div>
                                ${paymentRec && paymentRec.staff_notes ? `<div class="small text-muted mt-2"><i class="bi bi-chat-left-text"></i> Note: ${escapeHtml(paymentRec.staff_notes)}</div>` : ''}
                            </div>
                        </div>
                    `;
                }
                stagesHtml += '</div>';
                
                allHtml += `
                    <div class="card-custom mb-4">
                        <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h5 class="mb-0 fw-bold"><code>${escapeHtml(q.qtn_number)}</code></h5>
                                <small class="text-muted">${escapeHtml(productName)} | Total: RM ${total}</small>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            ${stagesHtml}
                        </div>
                    </div>
                `;
            }
            
            const thankYouHtml = `
                <div class="card-custom mb-4" style="background: linear-gradient(135deg, #f0f9ff 0%, #e6f7e6 100%); border-left: 6px solid #22c55e;">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-emoji-smile fs-1" style="color: #22c55e;"></i>
                        <h4 class="mt-2 fw-bold">Thank you for choosing YS Aluminium!</h4>
                        <p class="mb-3">We hope you are satisfied with our products and services.<br>If you enjoyed your experience, please support us with a review on Google.</p>
                        <a href="https://www.google.com/search?q=yong+sheng+alu+enterprise+&sca_esv=54f2dfe3db26537b&rlz=1C1GCEA_enMY1115MY1115&sxsrf=ANbL-n6l90qhkIuAi8k5AMf8Ax-FoXNGnQ%3A1781093936037&ei=MFYpaun4AbuZjuMP7YOkuAo&ved=0ahUKEwjp0Pn60_yUAxW7jGMGHe0BCacQ4dUDCBA&uact=5&oq=yong+sheng+alu+enterprise+&gs_lp=Egxnd3Mtd2l6LXNlcnAiGnlvbmcgc2hlbmcgYWx1IGVudGVycHJpc2UgMgsQABiABBiiBBiwAzILEAAYgAQYogQYsAMyCBAAGO8FGLADMgsQABiABBiiBBiwAzIIEAAY7wUYsANIzC1Q_yxY_yxwA3gAkAEAmAG4AaABuAGqAQMwLjG4AQPIAQD4AQGYAgOgAhGYAwCIBgGQBgWSBwEzoAeLArIHALgHAMIHBTAuMi4xyAcLgAgB&sclient=gws-wiz-serp#lrd=0x31da6f23026bbdbb:0xb8c7117e1fda0761,3,,,," target="_blank" class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-google me-2"></i> Write a Google Review
                        </a>
                        <p class="text-muted small mt-3">Your feedback helps us grow and improve!</p>
                    </div>
                </div>
            `;
            
            const topStepsHtml = `
                <div class="card-custom mb-4">
                    <div class="card-header-custom">
                        <h5 class="mb-0"><i class="bi bi-receipt me-2"></i> Invoice & Payment Stages</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-center gap-5">
                            <div><i class="bi bi-cash-stack fs-3 text-success"></i><br>Deposit 50%</div>
                            <div><i class="bi bi-tools fs-3 text-success"></i><br>Progress 30%</div>
                            <div><i class="bi bi-check2-circle fs-3 text-success"></i><br>Final 20%</div>
                        </div>
                        <hr>
                        <p class="text-muted small">Each stage invoice is generated after payment verification.</p>
                    </div>
                </div>
            `;
            
            container.innerHTML = thankYouHtml + topStepsHtml + allHtml;
        }
        else {
            container.innerHTML = `<div class="card-custom p-4 text-center">Page coming soon.</div>`;
        }
    } catch(e) { console.error("Error in showPage:", e); document.getElementById('pageContent').innerHTML = `<div class="alert alert-danger">Error loading page: ${e.message}</div>`; }
}

window.onload = () => {
    const savedPage = sessionStorage.getItem('currentCustomerPage');
    if (savedPage && (savedPage === 'Profile' || savedPage === 'Quotation' || savedPage === 'Payment' || savedPage === 'Development Progress' || savedPage === 'Invoice / Purchase')) {
        const menuItems = document.querySelectorAll('.nav-menu li');
        let targetMenuItem = null;
        for (let item of menuItems) {
            if (item.innerText.trim() === savedPage) {
                targetMenuItem = item;
                break;
            }
        }
        showPage(savedPage, targetMenuItem);
    } else {
        showPage('Profile', document.querySelector('.nav-menu li.active'));
    }
};
</script>
</body>
</html>