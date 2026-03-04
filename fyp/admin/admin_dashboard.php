<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }

include "../includes/db.php"; 

// --- 逻辑处理：添加门 (Add Door) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_door'])) {
    $brand    = mysqli_real_escape_string($conn, $_POST['door_brand']);
    $material = mysqli_real_escape_string($conn, $_POST['material']);
    $design   = mysqli_real_escape_string($conn, $_POST['design_type']);
    $date     = $_POST['stock_date'];
    $dimen    = mysqli_real_escape_string($conn, $_POST['dimensions']);
    $price    = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO doors (door_brand, material, design_type, stock_date, dimensions, price, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssssd", $brand, $material, $design, $date, $dimen, $price);
    
    if($stmt->execute()) {
        echo "<script>alert('Door product added!'); window.location='admin_dashboard.php?view=doors';</script>";
    }
    exit;
}

// --- 逻辑处理：注册 Staff (Register Staff) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reg_staff'])) {
    $s_id   = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $s_name = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $s_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO staff (staff_id, staff_name, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $s_id, $s_name, $s_pass);
    
    if($stmt->execute()) {
        echo "<script>alert('Staff Registered!'); window.location='admin_dashboard.php?view=staff';</script>";
    } else {
        echo "<script>alert('Error: Staff ID might already exist.');</script>";
    }
    exit;
}

// --- 逻辑处理：删除 Staff ---
if (isset($_GET['delete_staff'])) {
    $id = $_GET['delete_staff'];
    $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=staff");
    exit;
}

// --- 逻辑处理：删除门 ---
if (isset($_GET['delete_door'])) {
    $id = $_GET['delete_door'];
    $stmt = $conn->prepare("DELETE FROM doors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_dashboard.php?view=doors");
    exit;
}

$view = $_GET['view'] ?? 'doors'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* --- 全局黑白美化 --- */
        :root {
            --bg-light: #f8f9fa;
            --sidebar-bg: #000000; /* 纯黑侧边栏 */
            --sidebar-text: #ffffff;
            --text-dark: #000000;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --card-bg: #ffffff;
        }

        body { 
            display: flex; 
            min-height: 100vh; 
            background-color: var(--bg-light); 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; /* 更具现代感的字体 */
            margin: 0; 
            color: var(--text-dark);
        }

        /* --- 侧边栏美化 --- */
        .sidebar { 
            width: 260px; 
            background-color: var(--sidebar-bg); 
            color: var(--sidebar-text); 
            position: fixed; 
            height: 100vh; 
            padding: 25px; 
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        /* Logo 区域调整 */
        .sidebar-logo-area {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px; /* 图片和文字的间距 */
            margin-bottom: 40px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1); /* 添加一条细黑线装饰 */
        }

        .logo-img {
            width: 35px; /* 图标大小 */
            height: 35px;
            border-radius: 6px; /* 稍微圆角 */
            object-fit: cover;
            background-color: #fff; /* 如果图片是透明的，加个白底 */
            padding: 2px;
        }

        .sidebar h4 {
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase; /* 大写显专业 */
            font-size: 1.1rem;
        }

        .nav-menu {
            flex-grow: 1;
        }

        .nav-link-custom { 
            display: flex; 
            align-items: center; 
            color: rgba(255,255,255,0.7); /* 非活动状态稍微变灰 */
            text-decoration: none; 
            padding: 12px 15px; 
            border-radius: 8px; 
            margin-bottom: 8px; 
            transition: all 0.3s ease; 
            font-size: 0.95rem;
        }

        .nav-link-custom i {
            font-size: 1.1rem;
            margin-right: 12px;
        }

        /* 活动/悬停状态：白底黑字 */
        .nav-link-custom:hover, .nav-link-custom.active { 
            background-color: #ffffff; 
            color: #000000; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .sidebar hr {
            border-color: rgba(255,255,255,0.1);
            margin: 20px 0;
        }

        .text-danger-custom {
            color: #ff6b6b !important; /* 稍微柔和一点的红色 */
        }
        .nav-link-custom.text-danger-custom:hover {
            background-color: #ff6b6b;
            color: #fff !important;
        }

        /* --- 主内容区美化 --- */
        .main-content { 
            flex: 1; 
            margin-left: 260px; 
            padding: 40px; 
        }

        .card-custom { 
            background-color: var(--card-bg); 
            border-radius: 12px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); 
            padding: 30px; 
            border: 1px solid var(--border-color);
        }

        h4.fw-bold {
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        /* --- 表格黑白美化 --- */
        .table {
            border-color: var(--border-color);
        }

        .table-light {
            --bs-table-bg: #f1f3f5;
            --bs-table-border-color: var(--border-color);
            color: var(--text-dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.02);
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .table td, .table th {
            padding: 15px;
        }

        /* --- 按钮黑白美化 --- */
        .btn {
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 16px;
            transition: all 0.2s;
        }

        /* 主按钮：全黑 */
        .btn-primary {
            background-color: #000;
            border-color: #000;
            color: #fff;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #333;
            border-color: #333;
            color: #fff;
        }

        /* 成功按钮（注册）：深灰 */
        .btn-success {
            background-color: #495057;
            border-color: #495057;
            color: #fff;
        }
        .btn-success:hover {
            background-color: #343a40;
            border-color: #343a40;
        }

        /* 删除按钮：描边红，悬停实心红 */
        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }
        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: #fff;
        }

        /* --- 其他组件 --- */
        .badge.bg-info {
            background-color: #e9ecef !important; /* 浅灰背景 */
            color: #495057 !important; /* 深灰文字 */
            font-weight: 600;
            border: 1px solid #dee2e6;
        }

        code {
            background-color: #f1f3f5;
            color: #e03131;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        /* --- Modal 美化 --- */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .modal-header {
            border-bottom: 1px solid var(--border-color);
            background-color: var(--bg-light);
            border-radius: 12px 12px 0 0;
        }
        .modal-footer {
            border-top: 1px solid var(--border-color);
        }
        .form-label {
            color: #495057;
        }
        .form-control, .form-select {
            border-radius: 6px;
            border-color: var(--border-color);
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.125);
        }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo-area">
        <img src="../images/ys aluminium.jpg "alt="YS Logo" class="logo-img">
        <h4 class="font-color=white; text-center" >YS Aluminium</h4>
    </div>
    
    <nav class="nav-menu">
        <a href="admin_dashboard.php?view=doors" class="nav-link-custom <?= $view == 'doors' ? 'active' : '' ?>">
            <i class="bi bi-door-open"></i> Door Inventory
        </a>
        <a href="admin_dashboard.php?view=staff" class="nav-link-custom <?= $view == 'staff' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Staff Management
        </a>
        <a href="../homepage.html" class="nav-link-custom">
            <i class="bi bi-house"></i> Back to Home
        </a>
        <hr>
        <a href="logout.php" class="nav-link-custom text-danger-custom">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </nav>
</div>

<div class="main-content">
    <div class="card-custom">
        
        <?php if($view == 'doors'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Door Product Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoorModal">
                    <i class="bi bi-plus-lg me-1"></i> Add New Door
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Brand</th><th>Material</th><th>Design</th><th>Dimensions</th><th>Price</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM doors ORDER BY id DESC");
                        while($d = $res->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($d['door_brand']) ?></td>
                            <td><?= htmlspecialchars($d['material']) ?></td>
                            <td><?= htmlspecialchars($d['design_type']) ?></td>
                            <td><code><?= htmlspecialchars($d['dimensions']) ?></code></td>
                            <td class="fw-bold">RM <?= number_format($d['price'], 2) ?></td>
                            <td class="text-end">
                                <a href="?delete_door=<?= $d['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif($view == 'staff'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Staff Management</h4>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#regStaffModal">
                    <i class="bi bi-person-plus me-1"></i> Register New Staff
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Staff ID</th><th>Name</th><th>Role</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM staff ORDER BY id DESC");
                        while($s = $res->fetch_assoc()): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($s['staff_id']) ?></code></td>
                            <td class="fw-bold"><?= htmlspecialchars($s['staff_name']) ?></td>
                            <td><span class="badge bg-info">Staff</span></td>
                            <td class="text-end">
                                <a href="?view=staff&delete_staff=<?= $s['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to remove this staff account?')">
                                    <i class="bi bi-person-x"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

<div class="modal fade" id="regStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Register Staff Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Staff ID (Username)</label>
                    <input type="text" name="staff_id" class="form-control" placeholder="e.g. staff_001" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="staff_name" class="form-control" placeholder="Enter staff full name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Initial Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="reg_staff" class="btn btn-success">Create Staff Account</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="addDoorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Door Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Door Brand/Model</label>
                        <input type="text" name="door_brand" class="form-control" placeholder="e.g. Premium Sliding A1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Material</label>
                        <select name="material" class="form-select" required>
                            <option value="" selected disabled>Select Material</option>
                            <option value="Aluminum">Aluminum</option>
                            <option value="Aluminum + Glass">Aluminum + Glass</option>
                            <option value="Wood + Glass">Wood + Glass</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Design Type</label>
                        <input type="text" name="design_type" class="form-control" placeholder="e.g. Modern, Minimalist" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Dimensions (W x H)</label>
                        <input type="text" name="dimensions" class="form-control" placeholder="e.g. 900mm x 2100mm" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Stock In Date</label>
                        <input type="date" name="stock_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Unit Price (RM)</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_door" class="btn btn-primary">Add Door Product</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>