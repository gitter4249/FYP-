<?php
session_start();
// 验证管理员登录状态
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-bg: #000000;
            --main-bg: #f8f9fa;
            --accent-red: #ef4444;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--main-bg); 
            margin: 0;
            color: #1e293b;
        }

        /* --- 侧边栏 (与截图完全匹配) --- */
        .sidebar {
            height: 100vh;
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--sidebar-bg);
            color: white;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand img {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 8px;
            padding: 3px;
            object-fit: contain;
        }

        .sidebar-brand span {
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        .nav-section-title {
            font-size: 0.65rem;
            font-weight: 700;
            color: #64748b;
            padding: 20px 25px 8px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .nav-menu a {
            padding: 12px 25px;
            text-decoration: none;
            color: #94a3b8;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            transition: all 0.2s;
            margin: 2px 15px;
            border-radius: 8px;
        }

        .nav-menu a i {
            font-size: 1.2rem;
            margin-right: 12px;
        }

        .nav-menu a:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-menu a.active {
            background-color: white;
            color: black;
            font-weight: 600;
        }

        .logout-link {
            margin-top: auto;
            margin-bottom: 25px !important;
            color: var(--accent-red) !important;
        }

        .logout-link:hover {
            background-color: rgba(239, 68, 68, 0.1) !important;
        }

        /* --- 主内容区 --- */
        .main-content {
            margin-left: 260px;
            padding: 40px;
        }

        .welcome-header {
            margin-bottom: 30px;
        }

        .welcome-header h3 {
            font-weight: 700;
            margin-bottom: 5px;
        }

        /* --- 表格卡片样式 --- */
        .card-custom {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header-custom {
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            padding: 15px 25px;
            border: none;
        }

        .table tbody td {
            padding: 18px 25px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        code {
            background-color: #fff1f2;
            color: #e11d48;
            padding: 3px 8px;
            border-radius: 5px;
            font-family: inherit;
            font-weight: 500;
        }

        .btn-delete {
            color: #ef4444;
            background: #fff;
            border: 1px solid #fee2e2;
            padding: 6px 10px;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: white;
        }
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
        <a href="admin_dashboard.php?view=doors" class="<?= $view == 'doors' ? 'active' : '' ?>">
            <i class="bi bi-door-open"></i> Door Inventory
        </a>
        <a href="admin_dashboard.php?view=staff" class="<?= $view == 'staff' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Staff Management
        </a>

        <div class="nav-section-title">Business Overview</div>
        <a href="#" style="opacity: 0.6; cursor: not-allowed;">
            <i class="bi bi-calendar-check"></i> Appointment
        </a>
        <a href="#" style="opacity: 0.6; cursor: not-allowed;">
            <i class="bi bi-file-earmark-text"></i> Quotation
        </a>
        <a href="#" style="opacity: 0.6; cursor: not-allowed;">
            <i class="bi bi-receipt"></i> Invoice / Purchase
        </a>
        <a href="#" style="opacity: 0.6; cursor: not-allowed;">
            <i class="bi bi-credit-card"></i> Payment
        </a>
        <a href="#" style="opacity: 0.6; cursor: not-allowed;">
            <i class="bi bi-bar-chart-steps"></i> Progress
        </a>

        <a href="../homepage.php" style="margin-top: 20px;">
            <i class="bi bi-house"></i> Back to Home
        </a>
        <a href="logout.php" class="logout-link">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </nav>
</div>

<div class="main-content">
    <div class="welcome-header">
        <h3>Welcome, Admin</h3>
        <p class="text-muted small">Manage your inventory and staff accounts from this central panel.</p>
    </div>

    <div class="card-custom">
        <?php if($view == 'doors'): ?>
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold">Door Product Management</h5>
                <button class="btn btn-dark btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addDoorModal">
                    <i class="bi bi-plus-lg me-1"></i> Add New Door
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
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
                                <a href="?delete_door=<?= $d['id'] ?>" class="btn-delete" onclick="return confirm('Delete this product?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif($view == 'staff'): ?>
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold">Staff Account Management</h5>
                <button class="btn btn-dark btn-sm px-3" data-bs-toggle="modal" data-bs-target="#regStaffModal">
                    <i class="bi bi-person-plus me-1"></i> Register New Staff
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Staff ID</th><th>Name</th><th>Role</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM staff ORDER BY id DESC");
                        while($s = $res->fetch_assoc()): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($s['staff_id']) ?></code></td>
                            <td class="fw-bold"><?= htmlspecialchars($s['staff_name']) ?></td>
                            <td><span class="badge bg-light text-dark border">Staff</span></td>
                            <td class="text-end">
                                <a href="?view=staff&delete_staff=<?= $s['id'] ?>" class="btn-delete" onclick="return confirm('Remove this staff account?')">
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
        <form method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Register Staff Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Staff ID (Username)</label>
                    <input type="text" name="staff_id" class="form-control bg-light border-0" placeholder="e.g. staff_001" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="staff_name" class="form-control bg-light border-0" placeholder="Enter staff full name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Initial Password</label>
                    <input type="password" name="password" class="form-control bg-light border-0" placeholder="Minimum 6 characters" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="reg_staff" class="btn btn-dark px-4">Create Account</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="addDoorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Add New Door Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Door Brand/Model</label>
                        <input type="text" name="door_brand" class="form-control bg-light border-0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Material</label>
                        <select name="material" class="form-select bg-light border-0" required>
                            <option value="Aluminum">Aluminum</option>
                            <option value="Aluminum + Glass">Aluminum + Glass</option>
                            <option value="Wood + Glass">Wood + Glass</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Design Type</label>
                        <input type="text" name="design_type" class="form-control bg-light border-0" placeholder="e.g. Modern" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Dimensions</label>
                        <input type="text" name="dimensions" class="form-control bg-light border-0" placeholder="900mm x 2100mm" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Unit Price (RM)</label>
                        <input type="number" step="0.01" name="price" class="form-control bg-light border-0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Stock In Date</label>
                        <input type="date" name="stock_date" class="form-control bg-light border-0" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_door" class="btn btn-dark px-4">Add Product</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
