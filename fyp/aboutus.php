<?php 
session_start(); 
// 获取 Session 中的客户姓名
$customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - YS Aluminium</title>
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

        /* ================= NAVBAR (参考 Homepage 样式) ================= */
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

        /* ================= LOGIN MODAL ================= */
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 90%; max-width: 400px; text-align: center;
        }

        .close-modal { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #999; }
        .modal-btn-group { display: flex; flex-direction: column; gap: 15px; margin-top: 25px; }
        .modal-btn { padding: 15px; border-radius: 12px; text-decoration: none; font-size: 16px; font-weight: bold; transition: 0.3s; border: 1px solid #eee; color: black; }
        .modal-btn:hover { background: #f6f6f6; border-color: black; }
        .btn-admin { background: #1e293b; color: white; border: none; }
        .btn-admin:hover { background: #334155; }

        /* ================= ABOUT & CONTACT (保留你的原始内容样式) ================= */
        .about { padding: 100px 0 50px 0; }
        .about h1 { font-size: 42px; margin-bottom: 30px; }
        .about p { line-height: 1.8; color: #555; margin-bottom: 20px; max-width: 800px; }
        .contact { padding: 50px 0 100px 0; border-top: 1px solid #ddd; }
        .contact h2 { font-size: 36px; margin-bottom: 50px; }
        .contact-grid { display: flex; justify-content: space-between; gap: 80px; }
        .contact-left, .contact-right { flex: 1; }
        .contact h3 { font-size: 18px; margin-bottom: 15px; }
        .contact p { font-size: 14px; color: #555; line-height: 1.7; margin-bottom: 15px; }
        .btn-outline { display: inline-block; padding: 10px 22px; border: 1px solid black; border-radius: 25px; text-decoration: none; color: black; font-size: 13px; margin-top: 5px; margin-bottom: 5px; transition: 0.3s; }
        .btn-outline:hover { background: black; color: white; }
        .social-links a { color: black; text-decoration: none; font-size: 14px; }
        .social-links a:hover { text-decoration: underline; }

        @media(max-width:900px) {
            .contact-grid { flex-direction: column; gap: 40px; }
        }
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
                <a href="aboutus.php" class="active">About Us</a>
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

    <div class="container about">
        <h1>About YS Aluminium</h1>
        <p>
            YS Aluminium is a professional aluminium and glass specialist 
            providing high-quality installation services for residential 
            and commercial projects. With years of industry experience, 
            we focus on precision workmanship, durable materials and 
            customer satisfaction.
        </p>
        <p>
            Our services include aluminium doors, window frames, sliding systems, 
            glass panels and customised architectural aluminium works. 
            We are committed to delivering modern, reliable and long-lasting 
            solutions tailored to each client's needs.
        </p>
    </div>

    <div class="container contact">
        <h2>Contact Us</h2>
        <div class="contact-grid">
            <div class="contact-left">
                <h3>Send us a message</h3>
                <p>Monday - Sunday: 9:30am - 8:00pm</p>
                <a href="https://wa.me/60183665756" target="_blank" class="btn-outline">
                    Chat via WhatsApp
                </a>
                <br><br>
                <h3>Email</h3>
                <p>We’ll get back to you as soon as possible.</p>
                <a href="mailto:hengsijun00@gmail.com?subject=Inquiry about Aluminium Service" class="btn-outline">
                    Email us
                </a>
            </div>

            <div class="contact-right">
                <h3>Social Media</h3>
                <p>Follow us to see our latest projects and updates.</p>
                <div class="social-links">
                    <p>
                        <a href="https://www.facebook.com/p/Yong-Sheng-Alu-Enterprise-100054700453045/" target="_blank">Facebook</a> |
                        <a href="https://www.instagram.com/ys_aluminium_/" target="_blank">Instagram</a> |
                        <a href="https://www.xiaohongshu.com/user/profile/689eae2a000000001901d446?xsec_token=ABVcHPouxTcJElJySDZC16xCY1aAJuTXc_B6hgYm4vdoM%3D&xsec_source=pc_search" target="_blank">Xiaohongshu</a>
                    </p>
                </div>
                <br><br>
                <h3>Phone</h3>
                <p>Call: +60 18-3665756</p>
                <p>Monday - Sunday: 9:30am - 10:00pm</p>
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
