<?php
session_start();
// --- 关键点 1: 引入数据库连接 ---
require '../includes/db.php'; 

// 安全检查：修正 Session 变量名以匹配你的登录逻辑
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php"); 
    exit;
}

// 获取会话中的员工信息
$staff_name = $_SESSION['staff_name'] ?? "Staff Member";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-bg: #000000;
            --main-bg: #f8f9fa;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--main-bg); 
            margin: 0;
        }
        
        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--sidebar-bg);
            color: white;
            padding-top: 20px;
            z-index: 1000;
            overflow-y: auto; /* 菜单多时可以滚动 */
        }

        .sidebar-brand {
            padding: 10px 25px 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand img {
            width: 60px;
            height: auto;
            background: white;
            border-radius: 4px;
            padding: 2px;
        }

        .sidebar-brand span {
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        .sidebar a {
            padding: 12px 25px;
            text-decoration: none;
            color: #94a3b8;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            transition: all 0.3s;
            margin: 2px 15px;
            border-radius: 8px;
        }

        .sidebar a i {
            font-size: 1.1rem;
            margin-right: 12px;
        }

        .sidebar a:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar a.active {
            background-color: white;
            color: black;
            font-weight: 600;
        }

        .sidebar .logout-link {
            margin-top: 20px;
            margin-bottom: 30px;
            color: #ef4444;
        }

        .sidebar .logout-link:hover {
            background-color: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }
        
        /* Main Content Styling */
        .main-content {
            margin-left: 260px;
            padding: 40px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.5rem;
        }

        .mgmt-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 0;
            overflow: hidden;
        }

        .card-header-custom {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header-custom h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .btn-register {
            background-color: #334155;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-register:hover {
            background-color: #1e293b;
            color: white;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 15px 25px;
            border-top: none;
        }

        .table tbody td {
            padding: 18px 25px;
            vertical-align: middle;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .badge-id {
            background-color: #fff1f2;
            color: #e11d48;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 600;
        }

        .action-btn {
            border: 1px solid #fee2e2;
            background: white;
            color: #ef4444;
            padding: 6px 10px;
            border-radius: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .action-btn:hover {
            background: #ef4444;
            color: white;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <img src="../images/ys.jpg" alt="YS Aluminum Logo"> 
        <span>YS ALUMINIUM</span>
    </div>

    <a href="staff_dashboard.php" class="active">
        <i class="bi bi-people"></i> Customer Management
    </a>
    
    <a href="appointment.php">
        <i class="bi bi-calendar-check"></i> Appointment
    </a>

    <a href="quotation.php">
        <i class="bi bi-file-earmark-text"></i> Quotation
    </a>

    <a href="invoice_purchase.php">
        <i class="bi bi-receipt"></i> Invoice / Purchase
    </a>

    <a href="payment.php">
        <i class="bi bi-credit-card"></i> Payment
    </a>

    <a href="progress.php">
        <i class="bi bi-bar-chart-steps"></i> Progress
    </a>

    <a href="logout.php" class="logout-link">
        <i class="bi bi-box-arrow-left"></i> Logout
    </a>
</div>

<div class="main-content">
    
    <div class="page-header">
        <div>
            <h2>Welcome, <?php echo htmlspecialchars($staff_name); ?></h2>
            <p class="text-muted small">Manage and register your company customers here.</p>
        </div>
    </div>

    <div class="mgmt-card">
        <div class="card-header-custom">
            <h5>Customer Management</h5>
            <button class="btn btn-register" data-bs-toggle="modal" data-bs-target="#registerCustomerModal">
                <i class="bi bi-person-plus-fill"></i> Register New Customer
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 从数据库抓取真实数据
                    $query = "SELECT * FROM customers ORDER BY id DESC";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td><span class="badge-id">#<?php echo $row['id']; ?></span></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="text-end">
                                    <a href="delete_customer.php?id=<?php echo $row['id']; ?>" 
                                       class="action-btn" 
                                       title="Delete Account"
                                       onclick="return confirm('Are you sure you want to delete this customer?')">
                                         <i class="bi bi-person-x"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No customers found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="registerCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Register New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="customer_register.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="cust_name" class="form-control" placeholder="Enter customer name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Set Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="register_customer" class="btn btn-dark w-100 py-2 fw-bold" style="border-radius: 8px;">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>