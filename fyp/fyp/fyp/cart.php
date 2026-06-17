<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['remove_id'])) {
    $removeId = $_GET['remove_id'];
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
    }
    
    header("Location: cart.php");
    exit();
}

include('includes/header.php'); 
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

$productsMaster = [
    101 => ['name' => 'Aluminium Door #1', 'price' => 3.69, 'img' => 'images/project7.jpg'],
    102 => ['name' => 'Aluminium Door #2', 'price' => 5.50, 'img' => 'images/project8.jpg']
];
?>

<style>
.cart-page-wrapper { background-color: #fdfdfd; padding: 40px 0 100px; font-family: Arial, sans-serif; }
.cart-container { max-width: 1100px; margin: 0 auto; padding: 0 15px; }
.cart-title { font-size: 28px; font-weight: bold; color: #5a9b9a; margin-bottom: 30px; }
.cart-flex-layout { display: flex; gap: 30px; align-items: flex-start; }
.cart-items-list { flex: 2; }

.cart-card {
    background: #fff; border: 1px solid #eee; padding: 30px;
    margin-bottom: 20px; display: flex; flex-direction: column; 
    align-items: center; text-align: center; border-radius: 8px;
    position: relative;
}

.cart-card-img { width: 180px; height: 180px; margin-bottom: 20px; }
.cart-card-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; }

.btn-remove {
    position: absolute;
    top: 15px;
    right: 15px;
    color: #ff4d4d;
    text-decoration: none;
    font-size: 20px;
    font-weight: bold;
    width: 30px;
    height: 30px;
    line-height: 28px;
    border: 1px solid #ff4d4d;
    border-radius: 50%;
    transition: 0.3s;
}
.btn-remove:hover { background: #ff4d4d; color: white; }

.price-box b { color: #ff7e67; font-size: 20px; }
.cart-sidebar { flex: 1; background: #fff; border: 1px solid #eee; padding: 25px; border-radius: 8px; position: sticky; top: 20px; }
.btn-checkout { width: 100%; padding: 15px; background: #ff7e67; color: white; border: none; font-weight: bold; border-radius: 4px; cursor: pointer; }
.btn-continue { display: inline-block; margin-top: 20px; text-decoration: none; color: #5a9b9a; font-weight: bold; }

@media (max-width: 850px) { .cart-flex-layout { flex-direction: column; } }
</style>

<div class="cart-page-wrapper">
    <div class="cart-container">
        <h1 class="cart-title">Shopping Cart</h1>

        <?php if(empty($cart)): ?>
            <div style="text-align:center; padding: 60px; background:#fff; border:1px solid #eee;">
                <p style="color:#999;">Your cart is empty.</p>
                <a href="product.php" class="btn-continue">Go to Products</a>
            </div>
        <?php else: ?>
            <div class="cart-flex-layout">
                <div class="cart-items-list">
                    <?php 
                    $grandTotal = 0;
                    foreach($cart as $id => $qty): 
                        $item = isset($productsMaster[$id]) ? $productsMaster[$id] : ['name' => 'Unknown', 'price' => 0, 'img' => 'images/placeholder.jpg'];
                        $subtotal = $item['price'] * $qty;
                        $grandTotal += $subtotal;
                    ?>
                    <div class="cart-card">
                        <a href="cart.php?remove_id=<?php echo $id; ?>" class="btn-remove" title="Remove Item" onclick="return confirm('Confirm remove this item?')">×</a>
                        
                        <div class="cart-card-img">
                            <img src="<?php echo $item['img']; ?>">
                        </div>
                        <div class="cart-card-info">
                            <h3><?php echo $item['name']; ?></h3>
                            <p style="color:#666; margin-bottom:10px;">Quantity: <b><?php echo $qty; ?></b></p>
                            <div class="price-box">
                                <p style="font-size:14px; color:#999;">Subtotal</p>
                                <b>$<?php echo number_format($subtotal, 2); ?></b>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <a href="product.php" class="btn-continue">← Continue Shopping</a>
                </div>

                <div class="cart-sidebar">
                    <h2 style="font-size: 18px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Summary</h2>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 20px; font-weight: bold;">
                        <span>Total:</span>
                        <span style="color: #ff7e67;">$<?php echo number_format($grandTotal, 2); ?></span>
                    </div>
                    <button class="btn-checkout">PROCEED TO CHECKOUT</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>