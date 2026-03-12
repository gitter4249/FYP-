<?php 
session_start(); 
// 获取 Session 中的客户姓名
$customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - YS Aluminium</title>
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
            overflow-y: scroll; 
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ================= NAVBAR (已同步 Homepage/About 样式) ================= */
        .navbar {
            background: white;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            width: 100%;
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

        .nav-links a.active::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 2px;
            background: black;
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

        /* ================= MODAL & CONTENT ================= */
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

        .product-header { padding: 100px 20px 60px 20px; text-align: center; }
        .product-header h1 { font-size: 42px; margin-bottom: 20px; }
        .product-header p { max-width: 800px; margin: 0 auto; color: #555; line-height: 1.7; }

        .products { padding-bottom: 120px; }
        .product-grid { display: flex; justify-content: center; flex-wrap: wrap; gap: 40px; }
        .product-card { width: 100%; max-width: 380px; background: white; border-radius: 12px; overflow: hidden; transition: 0.3s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); }
        .product-image { width: 100%; height: 220px; background-size: cover; background-position: center; }
        .product-content { padding: 25px; }
        .product-content h3 { font-size: 18px; margin-bottom: 10px; }
        .product-content p { font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 20px; }
        .btn-outline { display: inline-block; padding: 8px 18px; border: 1px solid black; border-radius: 25px; text-decoration: none; color: black; font-size: 13px; transition: 0.3s; }
        .btn-outline:hover { background: black; color: white; }
    </style>
</head>

<body>

    <div class="navbar">
        <div class="container navbar-inner">
            <div class="logo">
                <img src="images/ys aluminium.jpg" alt="YS Aluminium Logo" class="logo-img">
            </div>

            <div class="nav-links">
                <a href="homepage.php">Home</a>
                <a href="aboutus.php">About Us</a>
                <a href="product.php" class="active">Products</a>
                
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

    <div class="container product-header">
        <h1>Our Products</h1>
        <p>
            We provide a wide range of aluminium and glass solutions designed for durability,
            safety and modern aesthetics. Explore our main product categories below.
        </p>
    </div>

    <div class="container products">
        <div class="product-grid">
            <div class="product-card">
                <div class="product-image" style="background-image:url('https://d21xn5q7qjmco5.cloudfront.net/images/doortype/thumb1754477466.jpg');"></div>
                <div class="product-content">
                    <h3>Aluminium Sliding Doors</h3>
                    <p>Smooth sliding system with durable aluminium frame and modern glass design.</p>
                    <a href="productdetail.php" class="btn-outline">View Details</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleModal(show) {
            const modal = document.getElementById('modalOverlay');
            modal.style.display = show ? 'block' : 'none';
            document.body.style.overflow = show ? 'hidden' : 'auto';
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') toggleModal(false);
        });
    </script>

</body>
</html>