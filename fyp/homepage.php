<?php 
session_start(); 
// 获取 Session 中的客户姓名
$customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YS Aluminium</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f6f6f6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ================= NAVBAR ================= */
        .navbar {
            background: white;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .navbar-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo-img {
            width: 140px;
            height: auto;
            object-fit: contain;
            transition: 0.3s;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: black;
            font-size: 14px;
            position: relative;
            padding-bottom: 5px;
            transition: 0.3s;
        }

        /* 登录后的用户区域样式 */
        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .username-text {
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }

        .account-btn {
            color: #555 !important;
            border: 1px solid #ddd;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 12px !important;
        }

        .account-btn:hover {
            background: #f9f9f9;
            border-color: #999;
        }

        .logout-link {
            color: #ff4d4d !important;
            font-size: 12px !important;
            margin-left: 5px;
        }

        /* 登录按钮样式 */
        .nav-login-btn {
            background: black;
            color: white !important;
            padding: 8px 18px;
            border-radius: 25px;
            transition: 0.3s;
            cursor: pointer;
        }

        .nav-login-btn:hover {
            background: #8d8a8a;
        }

        /* 原有动画和 Modal 样式保持不变... */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 2000;
            backdrop-filter: blur(5px);
        }

        .login-modal {
            position: fixed;
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            background: white; padding: 40px; border-radius: 20px;
            width: 90%; max-width: 400px; text-align: center;
        }

        .close-modal { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #999; }
        .modal-btn-group { display: flex; flex-direction: column; gap: 15px; margin-top: 25px; }
        .modal-btn { padding: 15px; border-radius: 12px; text-decoration: none; font-size: 16px; font-weight: bold; transition: 0.3s; border: 1px solid #eee; color: black; }
        .modal-btn:hover { background: #f6f6f6; border-color: black; }
        .btn-admin { background: #1e293b; color: white; border: none; }

        .hero { padding: 100px 0; }
        .hero h1 { font-size: 48px; margin-bottom: 25px; }
        .hero p { max-width: 650px; line-height: 1.7; color: #555; margin-bottom: 40px; text-align: justify; }
        .buttons { display: flex; gap: 20px; }
        .btn { padding: 12px 25px; border-radius: 25px; text-decoration: none; font-size: 14px; }
        .primary { background: black; color: white; }
        .secondary { border: 1px solid black; color: black; }
        .hero-image { margin-top: 60px; width: 100%; height: 450px; background: url('https://images.unsplash.com/photo-1492724441997-5dc865305da7') center/cover no-repeat; border-radius: 12px; }
        
        .projects { padding: 50px 0; }
        .projects h2 { font-size: 36px; margin-bottom: 20px; }
        .project-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }
        .project-item img { width: 100%; height: 250px; object-fit: cover; border-radius: 12px; transition: 0.4s; }
        .project-item img:hover { transform: scale(1.08); }
    </style>
</head>
<body>

    <?php if (isset($_GET['login']) && $_GET['login'] == 'success'): ?>
        <script>alert('Welcome back, <?php echo htmlspecialchars($customer_name); ?>! Login successful.');</script>
    <?php endif; ?>

    <div class="navbar">
        <div class="container navbar-inner">
            <div class="logo">
                <img src="images/ys aluminium.jpg" alt="YS Aluminium Logo" class="logo-img">
            </div>

            <div class="nav-links">
                <a href="homepage.php" class="active">Home</a>
                <a href="aboutus.php">About Us</a>
                <a href="product.php">Products</a>

                <?php if ($customer_name): ?>
                    <div class="user-section">
                        <span class="username-text">Hi, <?php echo htmlspecialchars($customer_name); ?></span>
                        <a href="customer/customer_dashboard.php" class="account-btn">Account Details</a>
                        <a href="customer/logout.php" class="logout-link">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="javascript:void(0)" class="nav-login-btn" onclick="toggleModal(true)">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalOverlay" onclick="toggleModal(false)">
        <div class="login-modal" onclick="event.stopPropagation()">
            <span class="close-modal" onclick="toggleModal(false)">&times;</span>
            <h3>Login As</h3>
            <div class="modal-btn-group">
                <a href="customer/customer_login.php" class="modal-btn">Customer</a>
                <a href="staff/login.php" class="modal-btn">Staff</a>
                <a href="admin/admin_login.php" class="modal-btn btn-admin">Admin</a>
            </div>
        </div>
    </div>

    <div class="container hero">
        <h1>Welcome to YS Aluminium</h1>
        <p>We specialize in providing tailored aluminium and glass installations...</p>
        <div class="buttons">
            <a href="aboutus.php" class="btn primary">About Us</a>
            <a href="product.php" class="btn secondary">View Products</a>
        </div>
        <div class="hero-image"></div>
    </div>

    <script>
        function toggleModal(show) {
            const modal = document.getElementById('modalOverlay');
            modal.style.display = show ? 'block' : 'none';
            document.body.style.overflow = show ? 'hidden' : 'auto';
        }
    </script>
</body>
</html>