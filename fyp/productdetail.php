<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('includes/db.php'); 

if (!isset($_GET['id'])) {
    header("Location: product.php");
    exit;
}

$productId = mysqli_real_escape_string($conn, $_GET['id']);

$sql = "SELECT * FROM products WHERE product_id = '$productId' LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);
    $productName = $product['door_brand'];
    $productDesc = $product['description']; 
    $productMaterial = $product['material'] ?? 'Aluminium'; 

    $images = [];
    $img_sql = "SELECT image_path FROM product_images WHERE product_id = '$productId' ORDER BY sort_order, id ASC";
    $img_result = mysqli_query($conn, $img_sql);
    if ($img_result && mysqli_num_rows($img_result) > 0) {
        while ($img_row = mysqli_fetch_assoc($img_result)) {
            $images[] = "images/" . $img_row['image_path'];
        }
    } else {
        if (!empty($product['image'])) {
            $images[] = "images/" . $product['image'];
        } else {
            $images[] = "images/default-product.jpg";
        }
    }

} else {
    echo "<script>alert('Product not found!'); window.location.href='product.php';</script>";
    exit;
}

include('includes/header.php'); 
?>

<style>
.slider {
    width: 100%;
    height: 600px;
    position: relative;
    overflow: hidden;
    background: #0b0b0b;
}
.slides {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.6s ease;
}
.slide {
    width: 100%;
    flex-shrink: 0;
    display: flex;
    justify-content: center;
    align-items: center;
}
.slide img {
    height: 100%;
    object-fit: contain;
}
.dots {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
}
.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: white;
    opacity: 0.6;
    cursor: pointer;
    transition: 0.3s;
}
.dot.active {
    opacity: 1;
    background: white;
}

.breadcrumb {
    padding: 30px 0 10px;
    font-size: 13px;
    color: #777;
}
.breadcrumb a {
    text-decoration: none;
    color: #777;
    transition: 0.3s;
}
.breadcrumb a:hover {
    color: #333;
}
.breadcrumb span {
    color: #000000;
    font-weight: bold;
}
.product-detail-section {
    padding: 20px 0 100px;
}
.product-flex {
    display: flex;
    gap: 60px;
    align-items: flex-start;
}
.product-main-image {
    flex: 1;
    max-width: 500px;
    width: 100%;
    aspect-ratio: 1 / 1;
    background: #fcfcfc;
    border: 1px solid #eee;
    overflow: hidden;
    position: relative;
}
.product-slider-wrapper {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}
.product-slides {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.4s ease-in-out;
}
.product-slide {
    min-width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}
.product-slide img, 
.product-main-image > img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.slide-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.4);
    color: white;
    border: none;
    padding: 12px 15px;
    cursor: pointer;
    font-size: 18px;
    border-radius: 4px;
    transition: 0.3s;
    z-index: 5;
}
.slide-arrow:hover {
    background: rgba(0, 0, 0, 0.7);
}
.prev-arrow {
    left: 10px;
}
.next-arrow {
    right: 10px;
}
.product-dots {
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 8px;
    z-index: 5;
}
.product-dot {
    width: 10px;
    height: 10px;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    cursor: pointer;
    transition: 0.3s;
}
.product-dot.active {
    background: #ffd453;
    transform: scale(1.2);
}
.product-main-info {
    flex: 1.2;
}
.product-main-info h1 {
    font-size: 32px;
    color: #1a202c;
    margin-bottom: 15px;
    font-weight: 600;
}
.product-category {
    font-size: 14px;
    color: #718096;
    margin-bottom: 30px;
}
.btn-group {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}
.btn-inquire-now {
    background: #20304a;
    color: white;
    padding: 12px 35px;
    border: 2px solid #20304a;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: 0.3s;
}
.btn-inquire-now:hover {
    background: white;
    color: #20304a;
}
.tabs-section {
    margin-top: 40px;
    border-top: 2px solid #edf2f7;
}
.tab-item {
    background: #ffd453;
    color: #20304a;
    padding: 12px 25px;
    font-size: 14px;
    font-weight: bold;
    border-radius: 4px 4px 0 0;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.tab-content {
    background: white;
    padding: 40px;
    border-radius: 0 8px 8px 8px;
    color: #4a5568;
    line-height: 1.8;
    box-shadow: 0 5px 20px rgba(0,0,0,0.03);
}
.inquiry-section {
    margin-top: 40px;
    background: white;
    padding: 30px;
    border-radius: 8px;
    border: 1px solid #eee;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.inquiry-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
}
.inquiry-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.form-group {
    margin-bottom: 15px;
}
.form-group.full {
    grid-column: span 2;
}
.form-group label {
    display: block;
    font-size: 14px;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
}
.form-group input, 
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    outline: none;
    transition: 0.3s;
}
.form-group input:focus {
    border-color: #ffd453;
}
.dimension-group {
    display: flex;
    align-items: center;
    gap: 10px;
}
.dimension-group input {
    flex: 1;
    text-align: center;
}
.btn-send {
    background: #ffd453;
    color: #20304a;
    padding: 12px 30px;
    border: 2px solid #ffd453;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.btn-send:hover {
    background: white;
    color: #20304a;
    border: 2px solid #ffd453;
}
@media (max-width: 900px) {
    .product-flex, 
    .inquiry-grid {
        display: block;
    }
    .product-main-image {
        max-width: 100%;
        margin-bottom: 20px;
    }
    .slider {
        height: 350px;
    }
}
</style>

<?php include('includes/chatbot.php'); ?>
<?php include('includes/slide.php'); ?>

<body>
<div class="container">
    <div class="breadcrumb">
        <a href="homepage.php">Home</a> > <a href="product.php">Our Products</a> > <span><?= htmlspecialchars($productName) ?></span>
    </div>

    <section class="product-detail-section">
        <div class="product-flex">
            
            <div class="product-main-image">
                <?php if (count($images) > 1): ?>
                    <div class="product-slider-wrapper">
                        <div class="product-slides" id="productSlides">
                            <?php foreach ($images as $img): ?>
                                <div class="product-slide">
                                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($productName) ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" class="slide-arrow prev-arrow" onclick="moveProdSlide(-1)">&#10094;</button>
                        <button type="button" class="slide-arrow next-arrow" onclick="moveProdSlide(1)">&#10095;</button>
                        
                        <div class="product-dots">
                            <?php foreach ($images as $index => $img): ?>
                                <span class="product-dot <?= $index === 0 ? 'active' : '' ?>" onclick="currentProdSlide(<?= $index ?>)"></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <img src="<?= $images[0] ?>" alt="<?= htmlspecialchars($productName) ?>">
                <?php endif; ?>
            </div>

            <div class="product-main-info">
                <h1><?= htmlspecialchars($productName) ?></h1>
                <p class="product-category">Category: <span><?= htmlspecialchars($productMaterial) ?></span></p>
                <div class="btn-group">
                    <a href="#inquiry-form" class="btn-inquire-now">✉️ Inquire Now</a>
                </div>
            </div>
        </div>

        <div class="tabs-section">
            <div class="tab-item">📋 Description</div>
            <div class="tab-content">
                <p><?= nl2br(htmlspecialchars($productDesc)) ?></p>
            </div>
        </div>

        <div id="inquiry-form" class="inquiry-section">
            <div class="inquiry-title"><span>✉️</span> Inquiry - <?= htmlspecialchars($productName) ?></div>
            <form id="whatsappForm">
                <div class="inquiry-grid">
                    <div class="form-group">
                        <label>Name*</label>
                        <input type="text" id="cust_name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label>Email (optional)</label>
                        <input type="email" id="cust_email" placeholder="Enter your email">
                    </div>  
                    <div class="form-group full">
                        <label>Message (optional)</label>
                        <textarea id="cust_message" rows="4" placeholder="Anything else you want to tell us?"></textarea>
                    </div>
                </div>
                <button type="button" onclick="sendToWhatsApp()" class="btn-send">Send WhatsApp Message</button>
            </form>
        </div>
    </section>
</div>
</body>

<script>

let prodCurrentIndex = 0;
const prodTotalSlides = <?= count($images) ?>;

function showProduct(index) {
    const slidesContainer = document.getElementById('productSlides');
    const dots = document.querySelectorAll('.product-dot');
    
    if (!slidesContainer) return;

    if (index >= prodTotalSlides) {
        prodCurrentIndex = 0;
    } else if (index < 0) {
        prodCurrentIndex = prodTotalSlides - 1;
    } else {
        prodCurrentIndex = index;
    }
    slidesContainer.style.transform = `translateX(-${prodCurrentIndex * 100}%)`;

    dots.forEach((dot, idx) => {
        if (idx === prodCurrentIndex) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

function moveProdSlide(direction) {
    showProduct(prodCurrentIndex + direction);
}

function currentProdSlide(index) {
    showProduct(index);
}

function sendToWhatsApp() {
    const phoneNumber = "60143877388"; 
    const name = document.getElementById('cust_name').value;
    const message = document.getElementById('cust_message').value;
    const product = "<?= addslashes($productName) ?>";

    if (name.trim() === "") {
        alert("Please enter your name.");
        return;
    }

    const text = `Hi, I am interested in your product.\n---\nProduct: ${product}\nCustomer Name: ${name}\nMessage: ${message || "No additional message."}\n---`;

    const wpUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(text)}`;
    window.open(wpUrl, '_blank');
}
</script>

<?php include('includes/footer.php'); ?>