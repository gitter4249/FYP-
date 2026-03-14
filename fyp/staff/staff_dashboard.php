<?php
session_start();
require '../includes/db.php'; 

// 安全检查
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php"); 
    exit;
}

// --- 核心逻辑 1: 自动删除 30 天前标记为 Inactive 的客户 ---
$cleanup_sql = "DELETE FROM customers WHERE status = 'inactive' AND deleted_at <= DATE_SUB(NOW(), INTERVAL 30 DAY)";
mysqli_query($conn, $cleanup_sql);

// --- 核心逻辑 2: 处理手动永久删除 ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM customers WHERE customer_id = $del_id";
    if (mysqli_query($conn, $delete_query)) {
        header("Location: staff_dashboard.php?msg=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
    exit;
}

// --- 核心逻辑 3: 处理状态切换 (Active/Inactive) ---
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $cust_id = intval($_GET['id']);
    $new_status = ($_GET['toggle_status'] == 'active') ? 'active' : 'inactive';
    $del_time = ($new_status == 'inactive') ? "NOW()" : "NULL";
    
    $update_status_sql = "UPDATE customers SET status = '$new_status', deleted_at = $del_time WHERE customer_id = $cust_id";
    mysqli_query($conn, $update_status_sql);
    header("Location: staff_dashboard.php");
    exit;
}

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
        :root { --sidebar-bg: #000000; --main-bg: #f8f9fa; }
        body { font-family: 'Inter', sans-serif; background-color: var(--main-bg); margin: 0; }
        .sidebar { height: 100vh; width: 260px; position: fixed; top: 0; left: 0; background-color: var(--sidebar-bg); color: white; padding-top: 20px; z-index: 1000; overflow-y: auto; }
        .sidebar-brand { padding: 10px 25px 30px; display: flex; align-items: center; gap: 12px; }
        .sidebar-brand img { width: 60px; height: auto; background: white; border-radius: 4px; padding: 2px; }
        .sidebar-brand span { font-weight: 700; font-size: 1.1rem; letter-spacing: 0.5px; }
        .sidebar a { padding: 12px 25px; text-decoration: none; color: #94a3b8; display: flex; align-items: center; font-size: 0.9rem; transition: all 0.3s; margin: 2px 15px; border-radius: 8px; }
        .sidebar a i { font-size: 1.1rem; margin-right: 12px; }
        .sidebar a:hover { color: white; background-color: rgba(255, 255, 255, 0.1); }
        .sidebar a.active { background-color: white; color: black; font-weight: 600; }
        .sidebar .logout-link { margin-top: 20px; margin-bottom: 30px; color: #ef4444; }
        
        .main-content { margin-left: 260px; padding: 40px; }
        .mgmt-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; }
        .card-header-custom { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        .btn-register { background-color: #334155; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; }
        
        .btn-status-active { color: #10b981; background: #ecfdf5; border: 1px solid #d1fae5; font-size: 0.75rem; padding: 2px 8px; border-radius: 20px; text-decoration: none; }
        .btn-status-inactive { color: #ef4444; background: #fef2f2; border: 1px solid #fee2e2; font-size: 0.75rem; padding: 2px 8px; border-radius: 20px; text-decoration: none; }
        .action-edit { color: #3b82f6; background: #eff6ff; border: 1px solid #dbeafe; padding: 6px 10px; border-radius: 6px; }
        .action-delete { color: #ef4444; background: #fef2f2; border: 1px solid #fee2e2; padding: 6px 10px; border-radius: 6px; margin-left: 5px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <img src="../images/ys.jpg" alt="YS Aluminum Logo"> 
        <span>YS ALUMINIUM</span>
    </div>
    <a href="staff_dashboard.php" class="active"><i class="bi bi-people"></i> Customer Management</a>
    <a href="appointment.php"><i class="bi bi-calendar-check"></i> Appointment</a>
    <a href="quotation.php"><i class="bi bi-file-earmark-text"></i> Quotation</a>
    <a href="invoice_purchase.php"><i class="bi bi-receipt"></i> Invoice / Purchase</a>
    <a href="product.php"><i class="bi bi-box-seam"></i> Product</a> 
    <a href="payment.php"><i class="bi bi-credit-card"></i> Payment</a>
    <a href="progress.php"><i class="bi bi-bar-chart-steps"></i> Progress</a>
    <a href="logout.php" class="logout-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
    <div class="page-header mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($staff_name); ?></h2>
        <p class="text-muted small">Manage and register your company customers here. <br>
        <span class="text-danger">* Inactive customers will be permanently deleted after 30 days.</span></p>
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
                        <th>Contact Info</th>
                        <th>Race/Gender</th>
                        <th style="width: 200px;">Address</th> 
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM customers ORDER BY customer_id DESC";
                    $result = mysqli_query($conn, $query);

                    while ($row = mysqli_fetch_assoc($result)) {
                        $status = $row['status'];
                        ?>
                        <tr>
                            <td><span class="badge bg-light text-dark">#<?php echo $row['customer_id']; ?></span></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td>
                                <div class="small"><?php echo htmlspecialchars($row['email']); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($row['phone'] ?? 'No Phone'); ?></div>
                            </td>
                            <td class="small"><?php echo htmlspecialchars($row['race'] . " / " . $row['gender']); ?></td>
                            <td>
                                <div class="small text-muted" style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                                    <?php echo htmlspecialchars($row['address'] ?? 'No Address'); ?>
                                </div>
                            </td>
                            <td>
                                <?php if($status == 'active'): ?>
                                    <a href="?toggle_status=inactive&id=<?php echo $row['customer_id']; ?>" class="btn-status-active" onclick="return confirm('Set to Inactive? (Will be deleted in 30 days)')">● Active</a>
                                <?php else: ?>
                                    <a href="?toggle_status=active&id=<?php echo $row['customer_id']; ?>" class="btn-status-inactive">○ Inactive</a>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="action-edit border-0 edit-btn" 
                                    data-id="<?php echo $row['customer_id']; ?>"
                                    data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                    data-phone="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>"
                                    data-gender="<?php echo $row['gender']; ?>"
                                    data-race="<?php echo $row['race']; ?>"
                                    data-address="<?php echo htmlspecialchars($row['address'] ?? ''); ?>"
                                    data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="?delete_id=<?php echo $row['customer_id']; ?>" 
                                   class="action-delete text-decoration-none" 
                                   onclick="return confirm('Are you sure you want to PERMANENTLY delete this customer?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="registerCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5>Register New Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form action="customer_register.php" method="POST">
                    <div class="mb-3"><label class="form-label small fw-bold">Full Name</label><input type="text" name="cust_name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">Phone Number</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Race</label>
                            <select name="race" class="form-select" required>
                                <option value="Malay">Malay</option>
                                <option value="Chinese">Chinese</option>
                                <option value="Indian">Indian</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label small fw-bold">Address</label><textarea name="address" class="form-control" rows="2" required></textarea></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label small fw-bold">Password</label><input type="password" name="password" class="form-control" required minlength="6"></div>
                        <div class="col-6 mb-3"><label class="form-label small fw-bold">Confirm Password</label><input type="password" name="confirm_password" class="form-control" required minlength="6"></div>
                    </div>
                    <button type="submit" name="register_customer" class="btn btn-dark w-100">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5>Edit Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form action="customer_edit.php" method="POST">
                    <input type="hidden" name="customer_id" id="edit_id">
                    <div class="mb-3"><label class="form-label small fw-bold">Full Name</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">Phone Number</label><input type="text" name="phone" id="edit_phone" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Gender</label>
                            <select name="gender" id="edit_gender" class="form-select"><option value="Male">Male</option><option value="Female">Female</option></select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Race</label>
                            <select name="race" id="edit_race" class="form-select"><option value="Malay">Malay</option><option value="Chinese">Chinese</option><option value="Indian">Indian</option><option value="Other">Other</option></select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label small fw-bold">Address</label><textarea name="address" id="edit_address" class="form-control" rows="2" required></textarea></div>
                    <button type="submit" name="update_customer" class="btn btn-primary w-100">Update Information</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_email').value = this.dataset.email;
            document.getElementById('edit_phone').value = this.dataset.phone;
            document.getElementById('edit_gender').value = this.dataset.gender;
            document.getElementById('edit_race').value = this.dataset.race;
            document.getElementById('edit_address').value = this.dataset.address;
        });
    });
</script>
</body>
</html>