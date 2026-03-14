<?php
session_start();
// 验证管理员登录状态
if (!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }

include "../includes/db.php"; 

// ================= DOOR 逻辑 =================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_door'])) {
    $brand    = mysqli_real_escape_string($conn, $_POST['door_brand']);
    $material = mysqli_real_escape_string($conn, $_POST['material']);
    $design   = mysqli_real_escape_string($conn, $_POST['design_type']);
    $date     = $_POST['stock_date'];
    $dimen    = mysqli_real_escape_string($conn, $_POST['dimensions']);
    $price    = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO doors (door_brand, material, design_type, stock_date, dimensions, price, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssssd", $brand, $material, $design, $date, $dimen, $price);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=doors"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_door'])) {
    $id       = $_POST['id'];
    $brand    = mysqli_real_escape_string($conn, $_POST['door_brand']);
    $material = mysqli_real_escape_string($conn, $_POST['material']);
    $design   = mysqli_real_escape_string($conn, $_POST['design_type']);
    $date     = $_POST['stock_date'];
    $dimen    = mysqli_real_escape_string($conn, $_POST['dimensions']);
    $price    = $_POST['price'];

    $stmt = $conn->prepare("UPDATE doors SET door_brand=?, material=?, design_type=?, stock_date=?, dimensions=?, price=? WHERE id=?");
    $stmt->bind_param("sssssdi", $brand, $material, $design, $date, $dimen, $price, $id);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=doors"); exit;
}

if (isset($_GET['toggle_door'])) {
    $id = $_GET['toggle_door'];
    $new_status = $_GET['status'];
    $stmt = $conn->prepare("UPDATE doors SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=doors"); exit;
}

// ================= STAFF 逻辑 =================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reg_staff'])) {
    $s_id    = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $s_name  = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $s_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
    $s_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
    $s_pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO staff (staff_id, staff_name, email, phone, password, status) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssss", $s_id, $s_name, $s_email, $s_phone, $s_pass);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=staff"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_staff'])) {
    $id      = $_POST['id'];
    $s_id    = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $s_name  = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $s_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
    $s_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
    
    if (!empty($_POST['password'])) {
        $s_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE staff SET staff_id=?, staff_name=?, email=?, phone=?, password=? WHERE id=?");
        $stmt->bind_param("sssssi", $s_id, $s_name, $s_email, $s_phone, $s_pass, $id);
    } else {
        $stmt = $conn->prepare("UPDATE staff SET staff_id=?, staff_name=?, email=?, phone=? WHERE id=?");
        $stmt->bind_param("ssssi", $s_id, $s_name, $s_email, $s_phone, $id);
    }
    $stmt->execute();
    header("Location: admin_dashboard.php?view=staff"); exit;
}

if (isset($_GET['toggle_staff'])) {
    $id = $_GET['toggle_staff'];
    $new_status = $_GET['status'];
    $stmt = $conn->prepare("UPDATE staff SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=staff"); exit;
}

// ================= CUSTOMER 逻辑 =================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {

$c_name  = $_POST['name'];
$c_email = $_POST['email'];
$c_phone = $_POST['phone'];
$c_pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
$status = 1;

$stmt = $conn->prepare("
INSERT INTO customers (name, email, phone, password, status)
VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param("ssssi", $c_name, $c_email, $c_phone, $c_pass, $status);
$stmt->execute();

header("Location: admin_dashboard.php?view=customers");
exit;
}
 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_customer'])) {
    $id      = $_POST['id'];
    $c_name  = mysqli_real_escape_string($conn, $_POST['name']);
    $c_email = mysqli_real_escape_string($conn, $_POST['email']);
    $c_phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $stmt = $conn->prepare("UPDATE customers SET name=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssi", $c_name, $c_email, $c_phone, $id);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=customers"); exit;
}

if (isset($_GET['toggle_customer'])) {
    $id = $_GET['toggle_customer'];
    $new_status = $_GET['status'];
    $stmt = $conn->prepare("UPDATE customers SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=customers"); exit;
}
// ================= DELETE 逻辑 =================
if (isset($_GET['delete_id']) && isset($_GET['view'])) {

    $id = intval($_GET['delete_id']);
    $viewType = $_GET['view'];

    if ($viewType == "doors") {
        $stmt = $conn->prepare("DELETE FROM doors WHERE id=?");
    } 
    elseif ($viewType == "staff") {
        $stmt = $conn->prepare("DELETE FROM staff WHERE id=?");
    } 
    elseif ($viewType == "customers") {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id=?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: admin_dashboard.php?view=".$viewType);
    exit;
}
$view = $_GET['view'] ?? 'doors'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-bg: #1c1c1e; /* 更柔和的深灰 */
            --main-bg: #f4f4f5;
            --text-dark: #27272a;
            --text-muted: #71717a;
            --border-color: #e4e4e7;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--main-bg); 
            margin: 0;
            color: var(--text-dark);
        }

        /* 侧边栏 */
        .sidebar { height: 100vh; width: 260px; position: fixed; top: 0; left: 0; background-color: var(--sidebar-bg); color: #d4d4d8; display: flex; flex-direction: column; }
        .sidebar-brand { padding: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #27272a; }
        .sidebar-brand img { width: 45px; height: 45px; background: white; border-radius: 8px; padding: 3px; object-fit: contain; }
        .sidebar-brand span { font-weight: 700; font-size: 1.1rem; color: #fff; }
        
        .nav-section-title { font-size: 0.65rem; font-weight: 700; color: #52525b; padding: 25px 25px 10px; letter-spacing: 1.2px; text-transform: uppercase; }
        .nav-menu a { padding: 12px 25px; text-decoration: none; color: #a1a1aa; display: flex; align-items: center; font-size: 0.9rem; transition: all 0.2s; margin: 2px 15px; border-radius: 8px; }
        .nav-menu a i { font-size: 1.2rem; margin-right: 12px; }
        .nav-menu a:hover { color: white; background-color: #27272a; }
        .nav-menu a.active { background-color: #3f3f46; color: white; font-weight: 600; }
        .logout-link { margin-top: auto; margin-bottom: 25px !important; color: #fca5a5 !important; }
        .logout-link:hover { background-color: #450a0a !important; color: #f87171 !important; }

        /* 主体内容 */
        .main-content { margin-left: 260px; padding: 40px; }
        .welcome-header h3 { font-weight: 700; color: #18181b; }
        
        .card-custom { background: white; border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; margin-top: 25px; }
        .card-header-custom { padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); background-color: #fff; }
        .card-header-custom h5 { color: #18181b; }
        
        /* 表格样式 */
        .table { margin-bottom: 0; }
        .table thead th { background-color: #fafafa; color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 16px 25px; border-bottom: 1px solid var(--border-color); }
        .table tbody td { padding: 18px 25px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; vertical-align: middle; color: #3f3f46; }
        .table tbody tr:hover { background-color: #fafafa; }
        code { background-color: #f4f4f5; color: #52525b; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; }

        /* 按钮与状态标签 (灰色系) */
        .btn-dark { background-color: #27272a; border-color: #27272a; }
        .btn-dark:hover { background-color: #18181b; border-color: #18181b; }
        
        .btn-action { color: #52525b; background: #f4f4f5; border: 1px solid var(--border-color); padding: 6px 12px; border-radius: 6px; transition: 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; margin-left: 4px; }
        .btn-action:hover { background: #e4e4e7; color: #18181b; border-color: #d4d4d8; }

        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-decoration: none; transition: 0.2s; display: inline-block; text-align: center; min-width: 80px; }
        .status-active { background-color: #18181b; color: #fff; border: 1px solid #18181b; } /* 深灰黑表示激活 */
        .status-inactive { background-color: #f4f4f5; color: #a1a1aa; border: 1px solid #e4e4e7; } /* 浅灰表示停用 */
        .status-active:hover { background-color: #3f3f46; color: white; }
        .status-inactive:hover { background-color: #e4e4e7; color: #71717a; }

        /* Modal 样式优化 */
        .modal-content { border-radius: 12px; border: none; }
        .modal-header { border-bottom: 1px solid var(--border-color); }
        .modal-footer { border-top: none; padding-top: 0; }
        .form-control, .form-select { background-color: #f8fafc; border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; }
        .form-control:focus, .form-select:focus { background-color: #fff; border-color: #a1a1aa; box-shadow: 0 0 0 3px rgba(161, 161, 170, 0.1); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <img src="../images/ys aluminium.jpg" alt="Logo">
        <span>YS ALUMINIUM</span>
    </div>
    
    <nav class="nav-menu">
        <div class="nav-section-title">Administration</div>
        <a href="admin_dashboard.php?view=doors" class="<?= $view == 'doors' ? 'active' : '' ?>"><i class="bi bi-door-open"></i> Product inventory</a>
        <a href="admin_dashboard.php?view=staff" class="<?= $view == 'staff' ? 'active' : '' ?>"><i class="bi bi-person-badge"></i> Staff Management</a>
        <a href="admin_dashboard.php?view=customers" class="<?= $view == 'customers' ? 'active' : '' ?>"><i class="bi bi-people"></i> Customer Management</a>

        <div class="nav-section-title">Business Overview</div>
        <a href="#" style="opacity: 0.4; "><i class="bi bi-calendar-check"></i> Appointment</a>
        <a href="#" style="opacity: 0.4; "><i class="bi bi-file-earmark-text"></i> Quotation</a>
        <a href="#" style="opacity: 0.4; "><i class="bi bi-receipt"></i> Invoice</a>
        
        <a href="../homepage.html" style="margin-top: 20px;"><i class="bi bi-house"></i> Back to Home</a>
        <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="welcome-header">
        <h3>System Administration</h3>
        <p class="text-muted small">Manage your inventory, staff accounts, and customer directory.</p>
    </div>

    <div class="card-custom">
        <?php if($view == 'doors'): ?>
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold">Door Product Inventory</h5>
                <button class="btn btn-dark btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addDoorModal"><i class="bi bi-plus-lg me-1"></i> Add New Product</button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Brand / Model</th><th>Material</th><th>Design Type</th><th>Dimensions</th><th>Price (RM)</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM doors ORDER BY id DESC");
                        while($d = $res->fetch_assoc()): $isActive = (isset($d['status']) && $d['status'] == 1); ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($d['door_brand']) ?></td>
                            <td><span class="text-muted small"><?= htmlspecialchars($d['material']) ?></span></td>
                            <td><span class="text-muted small"><?= htmlspecialchars($d['design_type']) ?></span></td>
                            <td><code><?= htmlspecialchars($d['dimensions']) ?></code></td>
                            <td class="fw-bold">RM <?= number_format($d['price'], 2) ?></td>
                            <td>
                                <a href="?toggle_door=<?= $d['id'] ?>&status=<?= $isActive ? 0 : 1 ?>" class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></a>
                            </td>
                            <td class="text-end">
<div class="d-flex justify-content-end">

<button class="btn-action"
onclick="editDoor(<?= $d['id'] ?>,
'<?= htmlspecialchars($d['door_brand']) ?>',
'<?= htmlspecialchars($d['material']) ?>',
'<?= htmlspecialchars($d['design_type']) ?>',
'<?= htmlspecialchars($d['dimensions']) ?>',
'<?= $d['price'] ?>',
'<?= $d['stock_date'] ?>')"
data-bs-toggle="modal"
data-bs-target="#editDoorModal">
<i class="bi bi-pencil"></i>
</button>

<a href="admin_dashboard.php?view=doors&delete_id=<?= $d['id'] ?>"
class="btn-action text-danger border-danger-subtle"
onclick="return confirm('Delete this product?')">
<i class="bi bi-trash"></i>
</a>

</div>
</td>
</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif($view == 'staff'): ?>
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold">Staff Directory</h5>
                <button class="btn btn-dark btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#regStaffModal"><i class="bi bi-person-plus me-1"></i> Add Staff</button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Staff ID</th><th>Full Name</th><th>Contact Information</th><th>System Status</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM staff ORDER BY id DESC");
                        while($s = $res->fetch_assoc()): $isActive = (isset($s['status']) && $s['status'] == 1); ?>
                        <tr>
                            <td><code><?= htmlspecialchars($s['staff_id']) ?></code></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($s['staff_name']) ?></td>
                            <td>
                                <div class="small text-muted mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($s['email'] ?? 'N/A') ?></div>
                                <div class="small text-muted"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($s['phone'] ?? 'N/A') ?></div>
                                <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Password</label>
                                <input type="password" name="password" class="form-control" required>
                                </div>
                            </td>
                            <td>
                                <a href="?view=staff&toggle_staff=<?= $s['id'] ?>&status=<?= $isActive ? 0 : 1 ?>" class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></a>
                            </td>
                            <td class="text-end">
<div class="d-flex justify-content-end">

<button class="btn-action"
onclick="editStaff(<?= $s['id'] ?>,
'<?= htmlspecialchars($s['staff_id']) ?>',
'<?= htmlspecialchars($s['staff_name']) ?>',
'<?= htmlspecialchars($s['email'] ?? '') ?>',
'<?= htmlspecialchars($s['phone'] ?? '') ?>')"
data-bs-toggle="modal"
data-bs-target="#editStaffModal">
<i class="bi bi-pencil"></i>
</button>

<a href="admin_dashboard.php?view=staff&delete_id=<?= $s['id'] ?>"
class="btn-action text-danger border-danger-subtle"
onclick="return confirm('Delete this staff account?')">
<i class="bi bi-trash"></i>
</a>

</div>
</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif($view == 'customers'): ?>
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold">Customer Directory</h5>
                <button class="btn btn-dark btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="bi bi-person-plus me-1"></i> Add Customer</button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Customer Name</th><th>Contact Information</th><th>Account Status</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM customers ORDER BY id DESC");
                        if($res) {
                            while($c = $res->fetch_assoc()): $isActive = (isset($c['status']) && $c['status'] == 1); ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($c['name']) ?></td>
                                <td>
                                    <div class="small text-muted mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($c['email'] ?? 'N/A') ?></div>
                                    <div class="small text-muted"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($c['phone'] ?? 'N/A') ?></div>
                                </td>
                                <td>
                                    <a href="?view=customers&toggle_customer=<?= $c['id'] ?>&status=<?= $isActive ? 0 : 1 ?>" class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></a>
                                </td>
                               <td class="text-end">
<div class="d-flex justify-content-end">

<button class="btn-action"
onclick="editCustomer(<?= $c['id'] ?>,
'<?= htmlspecialchars($c['name']) ?>',
'<?= htmlspecialchars($c['email'] ?? '') ?>',
'<?= htmlspecialchars($c['phone'] ?? '') ?>')"
data-bs-toggle="modal"
data-bs-target="#editCustomerModal">
<i class="bi bi-pencil"></i>
</button>

<a href="admin_dashboard.php?view=customers&delete_id=<?= $c['id'] ?>"
class="btn-action text-danger border-danger-subtle"
onclick="return confirm('Delete this customer?')">
<i class="bi bi-trash"></i>
</a>

</div>
</td>
                            </tr>
                            <?php endwhile; 
                        } ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="addDoorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" class="modal-content shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Add New Door Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label small fw-bold text-muted">Brand / Model Name</label><input type="text" name="door_brand" class="form-control" required></div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Material</label>
                        <select name="material" class="form-select" required>
                            <option value="" disabled selected>Select material...</option>
                            <option value="Aluminum">Aluminum (纯铝)</option>
                            <option value="Aluminum + Glass">Aluminum + Glass (铝合金+玻璃)</option>
                            <option value="Wood + Glass">Wood + Glass (木质+玻璃)</option>
                            <option value="Tempered Glass">Tempered Glass (钢化玻璃)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Design Type</label>
                        <select name="design_type" class="form-select" required>
                            <option value="" disabled selected>Select design type...</option>
                            <option value="Sliding Door">Sliding Door (推拉门)</option>
                            <option value="Swing Door">Swing Door (平开门)</option>
                            <option value="Folding Door">Folding Door (折叠门)</option>
                            <option value="Casement Door">Casement Door (吊趟门)</option>
                            <option value="Bifold Door">Bifold Door (双折门)</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label small fw-bold text-muted">Dimensions (e.g., 2100x900mm)</label><input type="text" name="dimensions" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label small fw-bold text-muted">Price (RM)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label small fw-bold text-muted">Stock Entry Date</label><input type="date" name="stock_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                </div>
            </div>
            <div class="modal-footer p-4 pt-0 border-0"><button type="submit" name="add_door" class="btn btn-dark px-4 py-2 w-100">Save Product</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editDoorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" class="modal-content shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Edit Door Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id" id="edit_door_id">
                <div class="row g-4">
                    <div class="col-md-6"><label class="form-label small fw-bold text-muted">Brand / Model Name</label><input type="text" name="door_brand" id="edit_door_brand" class="form-control" required></div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Material</label>
                        <select name="material" id="edit_door_material" class="form-select" required>
                            <option value="Aluminum">Aluminum (纯铝)</option>
                            <option value="Aluminum + Glass">Aluminum + Glass (铝合金+玻璃)</option>
                            <option value="Wood + Glass">Wood + Glass (木质+玻璃)</option>
                            <option value="Tempered Glass">Tempered Glass (钢化玻璃)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Design Type</label>
                        <select name="design_type" id="edit_door_design" class="form-select" required>
                            <option value="Sliding Door">Sliding Door (推拉门)</option>
                            <option value="Swing Door">Swing Door (平开门)</option>
                            <option value="Folding Door">Folding Door (折叠门)</option>
                            <option value="Casement Door">Casement Door (吊趟门)</option>
                            <option value="Bifold Door">Bifold Door (双折门)</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label small fw-bold text-muted">Dimensions</label><input type="text" name="dimensions" id="edit_door_dimen" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label small fw-bold text-muted">Price (RM)</label><input type="number" step="0.01" name="price" id="edit_door_price" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label small fw-bold text-muted">Stock Entry Date</label><input type="date" name="stock_date" id="edit_door_date" class="form-control" required></div>
                </div>
            </div>
            <div class="modal-footer p-4 pt-0 border-0"><button type="submit" name="edit_door" class="btn btn-dark px-4 py-2 w-100">Update Product Details</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="regStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow-lg">
            <div class="modal-header bg-light"><h5 class="modal-title fw-bold">Register New Staff</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Staff ID</label><input type="text" name="staff_id" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Full Name</label><input type="text" name="staff_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Email Address</label><input type="email" name="staff_email" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Phone Number</label><input type="text" name="staff_phone" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Temporary Password</label><input type="password" name="password" class="form-control" required></div>
            </div>
            <div class="modal-footer p-4 pt-0 border-0"><button type="submit" name="reg_staff" class="btn btn-dark px-4 py-2 w-100">Create Account</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow-lg">
            <div class="modal-header bg-light"><h5 class="modal-title fw-bold">Edit Staff Profile</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">
                <input type="hidden" name="id" id="edit_staff_id">
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Staff ID</label><input type="text" name="staff_id" id="edit_staff_username" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Full Name</label><input type="text" name="staff_name" id="edit_staff_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Email Address</label><input type="email" name="staff_email" id="edit_staff_email" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Phone Number</label><input type="text" name="staff_phone" id="edit_staff_phone" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small fw-bold text-muted">Reset Password (Optional)</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password"></div>
            </div>
            <div class="modal-footer p-4 pt-0 border-0"><button type="submit" name="edit_staff" class="btn btn-dark px-4 py-2 w-100">Save Changes</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow-lg">
            <div class="modal-header bg-light"><h5 class="modal-title fw-bold">Add New Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">

<div class="mb-3">
<label class="form-label small fw-bold text-muted">Customer Name</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label small fw-bold text-muted">Email Address</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label small fw-bold text-muted">Phone Number</label>
<input type="text" name="phone" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label small fw-bold text-muted">Password</label>
<input type="password" name="password" class="form-control" required>
</div>

</div>
            <div class="modal-footer p-4 pt-0 border-0"><button type="submit" name="add_customer" class="btn btn-dark px-4 py-2 w-100">Add Customer</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow-lg">
            <div class="modal-header bg-light"><h5 class="modal-title fw-bold">Edit Customer Profile</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-4">

<input type="hidden" name="id" id="edit_customer_id">

<div class="mb-3">
<label class="form-label small fw-bold text-muted">Customer Name</label>
<input type="text" name="name" id="edit_customer_name" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label small fw-bold text-muted">Email</label>
<input type="email" name="email" id="edit_customer_email" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label small fw-bold text-muted">Phone</label>
<input type="text" name="phone" id="edit_customer_phone" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label small fw-bold text-muted">New Password</label>
<input type="password" name="password" class="form-control">
<small class="text-muted">Leave blank if not changing password</small>
</div>

</div>
            <div class="modal-footer p-4 pt-0 border-0"><button type="submit" name="edit_customer" class="btn btn-dark px-4 py-2 w-100">Save Changes</button></div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editDoor(id, brand, mat, des, dim, price, date) {
        document.getElementById('edit_door_id').value = id;
        document.getElementById('edit_door_brand').value = brand;
        document.getElementById('edit_door_material').value = mat;
        document.getElementById('edit_door_design').value = des;
        document.getElementById('edit_door_dimen').value = dim;
        document.getElementById('edit_door_price').value = price;
        document.getElementById('edit_door_date').value = date;
    }

    function editStaff(id, staffId, name, email, phone) {
        document.getElementById('edit_staff_id').value = id;
        document.getElementById('edit_staff_username').value = staffId;
        document.getElementById('edit_staff_name').value = name;
        document.getElementById('edit_staff_email').value = email;
        document.getElementById('edit_staff_phone').value = phone;
    }

    function editCustomer(id, name, email, phone) {
        document.getElementById('edit_customer_db_id').value = id;
        document.getElementById('edit_customer_name').value = name;
        document.getElementById('edit_customer_email').value = email;
        document.getElementById('edit_customer_phone').value = phone;
    }
</script>
</body>
</html>