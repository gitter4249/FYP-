<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('includes/db.php'); 
include('includes/header.php'); 

$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$selected_sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
?>

<style>
.breadcrumb {
    padding: 30px 0 20px;
    font-size: 13px;
    color: #777;
    font-weight: 500;
}

.breadcrumb a {
    text-decoration: none;
    color: #777;
}

.breadcrumb span {
    color: #000;
    font-weight: bold;
}

.page-layout {
    display: flex;
    gap: 50px;
    align-items: flex-start;
}

.sidebar {
    width: 260px;
    flex-shrink: 0;
}

.sidebar-search {
    margin-bottom: 25px;
}

.sidebar-search .search-box {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #ccc;
    padding: 10px 15px;
    border-radius: 4px;
    width: 100%;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.sidebar-search input {
    border: none;
    background: transparent;
    outline: none;
    width: 100%;
    font-size: 14px;
    margin-left: 8px;
}

.sidebar h3 {
    font-size: 17px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #333;
    text-transform: uppercase;
}

.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-list li {
    margin-bottom: 15px;
}

.category-list a {
    text-decoration: none;
    color: #555;
    font-size: 15px;
    display: block;
    transition: 0.3s;
}

.category-list a:hover, 
.category-list a.active {
    color: #20304a;
    font-weight: bold;
}

.main-content {
    flex-grow: 1;
}

.filter-bar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
}

.sort-box select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
    font-size: 14px;
    outline: none;
    cursor: pointer;
}

.products-section {
    padding: 0 0 100px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 50px 35px;
}

.product-card {
    transition: 0.3s;
    display: block;
    text-decoration: none;
}

.product-image-box {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: #fff;
    border: 1px solid #eee;
    overflow: hidden;
    margin-bottom: 18px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.02);
}

.product-image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: 0.5s;
}

.product-card:hover .product-image-box img {
    transform: scale(1.06);
}

.product-info h3 {
    font-size: 16px;
    color: #222;
    font-weight: 600;
    min-height: 44px;
    margin-bottom: 10px;
}

.view-details-link {
    font-size: 13px;
    color: #555;
    font-weight: bold;
    text-decoration: none;
    border-bottom: 1px solid #ddd;
    padding-bottom: 2px;
}

.view-details-link:hover {
    color: #20304a;
    border-bottom: 1px solid #20304a;
}

.loading-overlay {
    opacity: 0.5;
    pointer-events: none;
}

@media (max-width: 1100px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-layout {
        flex-direction: column;
    }
    .sidebar {
        width: 100%;
    }
    .product-grid {
        grid-template-columns: repeat(1, 1fr);
    }
}

</style>

<?php include('includes/chatbot.php'); ?>
<?php include('includes/slide.php'); ?>

<body>
<div class="container">
    <div class="breadcrumb">
        <a href="homepage.php">Home</a> > <span>Our Products</span>
    </div>

    <div class="page-layout">
        <aside class="sidebar">
            <form method="GET" action="" id="productFilterForm">
                <div class="sidebar-search">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Search products..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>

                <h3>PRODUCT CATALOGUE</h3>
                
                <ul class="category-list">
                    <li><a href="?category=&search=<?= urlencode($search_query) ?>&sort=<?= $selected_sort ?>" class="<?= empty($selected_category) ? 'active' : '' ?>" data-category="">All Products</a></li>
                    <li><a href="?category=Folding Door&search=<?= urlencode($search_query) ?>&sort=<?= $selected_sort ?>" class="<?= $selected_category == 'Folding Door' ? 'active' : '' ?>" data-category="Folding Door">Folding Door</a></li>
                    <li><a href="?category=Sliding Door&search=<?= urlencode($search_query) ?>&sort=<?= $selected_sort ?>" class="<?= $selected_category == 'Sliding Door' ? 'active' : '' ?>" data-category="Sliding Door">Sliding Door</a></li>
                    <li><a href="?category=Swing Door&search=<?= urlencode($search_query) ?>&sort=<?= $selected_sort ?>" class="<?= $selected_category == 'Swing Door' ? 'active' : '' ?>" data-category="Swing Door">Swing Door</a></li>
                    <li><a href="?category=Hanging Door&search=<?= urlencode($search_query) ?>&sort=<?= $selected_sort ?>" class="<?= $selected_category == 'Hanging Door' ? 'active' : '' ?>" data-category="Hanging Door">Hanging Door</a></li>
                    <li><a href="?category=Window&search=<?= urlencode($search_query) ?>&sort=<?= $selected_sort ?>" class="<?= $selected_category == 'Window' ? 'active' : '' ?>" data-category="Window">Window</a></li>
                </ul>
            </form>
        </aside>

        <div class="main-content">
            <div class="filter-bar">
                <div class="sort-box">
                    <select id="sortSelect">
                        <option value="latest" <?= $selected_sort == 'latest' ? 'selected' : '' ?>>Sort by: Latest</option>
                        <option value="oldest" <?= $selected_sort == 'oldest' ? 'selected' : '' ?>>Sort by: Oldest</option>
                    </select>
                </div>
            </div>

            <div class="products-section" id="main-product-area">
                <div class="product-grid">
                    <?php
                    if (isset($conn)) {
                        $sql = "SELECT * FROM products WHERE status = 1";
                        if (!empty($selected_category)) {
                            $safe_category = mysqli_real_escape_string($conn, $selected_category);
                            $sql .= " AND design_type = '$safe_category'"; 
                        }
                        if (!empty($search_query)) {
                            $safe_search = mysqli_real_escape_string($conn, $search_query);
                            $sql .= " AND door_brand LIKE '%$safe_search%'"; 
                        }
                        $sql .= ($selected_sort === 'oldest') ? " ORDER BY product_id ASC" : " ORDER BY product_id DESC";

                        $result = mysqli_query($conn, $sql);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $imgPath = "images/" . (!empty($row['image']) ? $row['image'] : 'default-product.jpg');
                    ?>
                                <div class="product-card">
                                    <div class="product-image-box">
                                        <a href="productdetail.php?id=<?= $row['product_id'] ?>">
                                            <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($row['door_brand']) ?>">
                                        </a>
                                    </div>
                                    <div class="product-info">
                                        <h3><?= htmlspecialchars($row['door_brand']) ?></h3>
                                        <a href="productdetail.php?id=<?= $row['product_id'] ?>" class="view-details-link">🔍 View Details</a>
                                    </div>
                                </div>
                    <?php
                            }
                        } else {
                            echo "<p style='grid-column:1/-1; text-align:center; padding:50px; color:#999;'>No products found.</p>";
                        }
                    }
                    ?>
                </div> 
            </div> 
        </div>
    </div>
</div> 

<?php include('includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainArea = document.getElementById('main-product-area');

    function fetchProducts(url) {
        mainArea.classList.add('loading-overlay');
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('main-product-area').innerHTML;
                
                mainArea.innerHTML = newContent;
                
                history.pushState(null, '', url);
                
                const currentUrl = new URL(url, window.location.origin);
                const currentCat = currentUrl.searchParams.get('category') || '';
                document.querySelectorAll('.category-list a').forEach(a => {
                    if (a.getAttribute('data-category') === currentCat) {
                        a.classList.add('active');
                    } else {
                        a.classList.remove('active');
                    }
                });

                mainArea.classList.remove('loading-overlay');
            })
            .catch(error => console.error('Error:', error));
    }

    document.querySelector('.category-list').addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link) {
            e.preventDefault();
            fetchProducts(link.href);
        }
    });

    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('sort', this.value);
            fetchProducts(currentUrl.toString());
        });
    }

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('search', this.value);
                fetchProducts(currentUrl.toString());
            }
        });
    }
    window.addEventListener('popstate', () => location.reload());
});
</script>
</body>
</html>