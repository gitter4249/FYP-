<?php
session_start();

// 这里的变量名必须和 login.php 设置的一模一样
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    // 【关键点 2】确保这里的 login.php 路径是正确的
    // 如果你的登录页面叫 staff_login.php，请改成 staff_login.php
    header("Location: staff_login.php"); 
    exit;
}

$staff_name = $_SESSION['staff_name'] ?? "Staff Member";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Portal | Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        
        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #1e293b;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 12px 20px;
            text-decoration: none;
            color: #cbd5e1;
            display: block;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background-color: #334155;
            color: white;
        }
        .sidebar .active {
            background-color: #2563eb;
            color: white;
        }
        
        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="px-4 mb-4">
        <h4 class="fw-bold">CompanyBox</h4>
    </div>
    <a href="#" class="active"><i class="bi bi-house-door me-2"></i> Dashboard</a>
    <a href="#"><i class="bi bi-person-badge me-2"></i> My Profile</a>
    <a href="#"><i class="bi bi-calendar-event me-2"></i> Schedule</a>
    <a href="#"><i class="bi bi-file-earmark-text me-2"></i> Documents</a>
    <hr class="mx-3 border-secondary">
    <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-0">Welcome back, <?php echo htmlspecialchars($staff_name); ?>!</h2>
            <p class="text-muted">Here is what's happening today.</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-white border dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i> Account
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm p-4 bg-primary text-white">
                <p class="mb-1 opacity-75">Work Hours</p>
                <h3 class="fw-bold mb-0">38.5h</h3>
                <small>This week</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm p-4 bg-white">
                <p class="mb-1 text-muted">Pending Tasks</p>
                <h3 class="fw-bold mb-0">12</h3>
                <small class="text-warning">3 Due Today</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm p-4 bg-white">
                <p class="mb-1 text-muted">Company Notices</p>
                <h3 class="fw-bold mb-0">4 New</h3>
                <small class="text-success">Check Inbox</small>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4">Recent Projects</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Project Name</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">System Migration</td>
                            <td>Oct 24, 2023</td>
                            <td><span class="badge bg-success-subtle text-success">Active</span></td>
                            <td>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 75%"></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Q4 Staff Training</td>
                            <td>Nov 05, 2023</td>
                            <td><span class="badge bg-warning-subtle text-warning">Pending</span></td>
                            <td>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 30%"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>