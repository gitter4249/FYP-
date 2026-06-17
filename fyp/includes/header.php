<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
body{ background:#f6f6f6; color:#666; -webkit-font-smoothing: antialiased; }
.container{ max-width:1200px; margin:0 auto; padding:0 20px; }


.navbar {
    background: white;
    border-bottom: 1px solid #eee;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.navbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between; 
    padding: 20px 0;
    transition: padding 0.3s ease-out;
    position: relative; 
}

.logo-img {
    width: 150px;
    height: auto;
    object-fit: contain;
    transition: width 0.3s ease-out;
    will-change: width;
}

.navbar.shrink .navbar-inner {
    padding: 10px 0;
}

.navbar.shrink .logo-img {
    width: 100px;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 15px;
    position: absolute; 
    left: 50%;
    transform: translateX(-50%);
}

@media (max-width: 900px) {
    .nav-links { position: static; transform: none; margin: 0 auto; }
}

.nav-links a { text-decoration: none; color: black; font-size: 14px; }
.nav-links a.active { font-weight: bold; }
.divider { color: #999; font-size: 14px; }

.user-section { display: flex; align-items: center; gap: 15px; }
.username-text { font-weight: bold; color: #333; font-size: 14px; }
.account-btn { color: #555!important; border: 1px solid #ddd; padding: 5px 12px; border-radius: 4px; font-size: 12px!important; text-decoration: none; }
.nav-login-btn { background: black; color: white!important; padding: 8px 18px; border-radius: 25px; text-decoration: none; transition: 0.3s; }
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 2000; backdrop-filter: blur(5px); }
.login-modal { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px; border-radius: 20px; width: 90%; max-width: 400px; text-align: center; }
.close-modal { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #999; }
.modal-btn-group { display: flex; flex-direction: column; gap: 15px; margin-top: 25px; }
.modal-btn { padding: 15px; border-radius: 12px; text-decoration: none; font-size: 16px; font-weight: bold; border: 1px solid #eee; color: black; transition: 0.3s; }
.btn-admin { background: #1e293b; color: white; border: none; }
</style>

<?php if (isset($_GET['login']) && $_GET['login']=='success'): ?>
<script>
    alert('Welcome back, <?php echo htmlspecialchars($customer_name); ?>! Login successful.');
</script>
<?php endif; ?>

<div class="navbar" id="mainNavbar">
    <div class="container navbar-inner">
        <a href="homepage.php">
            <img src="images/ys aluminium.jpg" class="logo-img" alt="YS Aluminium Logo">
        </a>
        <div class="nav-links">
            <a href="homepage.php" class="<?= ($current_page=='homepage.php') ? 'active' : '' ?>">Home</a>
            <span class="divider">|</span>
            <a href="aboutus.php" class="<?= ($current_page=='aboutus.php') ? 'active' : '' ?>">About Us</a>
            <span class="divider">|</span>
            <a href="product.php" class="<?= ($current_page=='product.php') ? 'active' : '' ?>">Products</a>
            <span class="divider">|</span>
            <a href="gallery.php" class="<?= ($current_page=='gallery.php') ? 'active' : '' ?>">Gallery</a>
            <span class="divider">|</span>
            <a href="contactus.php" class="<?= ($current_page=='contactus.php') ? 'active' : '' ?>">Contact Us</a>
        </div>
        
        <?php if ($customer_name): ?>
        <div class="user-section">
            <span class="username-text">Hi, <?php echo htmlspecialchars($customer_name); ?></span>
            <a href="customer/customer_dashboard.php" class="account-btn">Account Details</a>
            <a href="customer/logout.php" style="text-decoration:none; color:inherit; font-size:14px; margin-left:10px;">Logout</a>
        </div>
        <?php else: ?>
        <a href="javascript:void(0)" class="nav-login-btn" onclick="toggleModal(true)">Login</a>
        <?php endif; ?>
    </div>
</div>

<div class="modal-overlay" id="modalOverlay" onclick="toggleModal(false)">
    <div class="login-modal" onclick="event.stopPropagation()">
        <span class="close-modal" onclick="toggleModal(false)">×</span>
        <h3>Login As</h3>
        <div class="modal-btn-group">
            <a href="customer/customer_login.php" class="modal-btn">Customer</a>
            <a href="staff/login.php" class="modal-btn">Staff</a>
            <a href="admin/admin_login.php" class="modal-btn btn-admin">Admin</a>
        </div>
    </div>
</div>

<script>
window.addEventListener("scroll", function() {
    const navbar = document.getElementById("mainNavbar");
    if (window.scrollY > 50) {
        navbar.classList.add("shrink");
    } else {
        navbar.classList.remove("shrink");
    }
});

function toggleModal(show) {
    const modal = document.getElementById('modalOverlay');
    if (modal) {
        modal.style.display = show ? 'block' : 'none';
        document.body.style.overflow = show ? 'hidden' : 'auto';
    }
}
</script>