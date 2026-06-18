<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}
$staff_id = intval($_SESSION['staff_id']);
$staff_name = $_SESSION['staff_name'] ?? "Staff";
if (isset($_GET['action']) && $_GET['action'] == 'get_customer_data') {
    header('Content-Type: application/json');
    $customer_id = intval($_GET['id']);
    if ($customer_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
        exit;
    }
    $cust_sql = "SELECT name, email, phone, gender, race, address FROM customers WHERE customer_id = $customer_id";
    $cust_res = mysqli_query($conn, $cust_sql);
    $customer = mysqli_fetch_assoc($cust_res);
    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit;
    }
    $prod_sql = "SELECT p.product_id, p.door_brand, p.price_per_sqft, p.material, p.design_type 
                 FROM customer_selected_products csp
                 JOIN products p ON csp.product_id = p.product_id
                 WHERE csp.customer_id = $customer_id AND p.status = 1
                 ORDER BY p.door_brand ASC";
    $prod_res = mysqli_query($conn, $prod_sql);
    $products = [];
    while ($p = mysqli_fetch_assoc($prod_res)) {
        $products[] = $p;
    }
    
    echo json_encode([
        'success' => true,
        'customer' => $customer,
        'products' => $products
    ]);
    exit;
}

$cust_res = mysqli_query($conn, "SELECT customer_id, name FROM customers WHERE status = 1 ORDER BY name ASC");
$prod_res = mysqli_query($conn, "SELECT product_id, door_brand, material, design_type, price_per_sqft FROM products WHERE status = 1 ORDER BY door_brand ASC");
$products = [];
while ($p = mysqli_fetch_assoc($prod_res)) {
    $products[] = $p;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Quotation | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f4f4f5; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 30px 20px; 
            margin: 0; 
        }
        .quotation-container { 
            max-width: 1200px; 
            width: 100%; 
            margin: 0 auto; 
        }
        .page-header { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            margin-bottom: 30px; 
        }
        .page-header h3 { 
            margin: 0; 
            font-weight: 700; 
            color: #1c1c1e; 
        }
        .btn-back { 
            background: white; 
            border: 1px solid #e4e4e7; 
            border-radius: 10px;
            padding: 10px 20px; 
            color: #3a3a3c; 
            font-weight: 500; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
        }
        .btn-back:hover { 
            background: #fafafa; 
            transform: translateX(-3px); 
        }
        .card-custom { 
            background: white;
            border: 1px solid #e4e4e7; 
            border-radius: 20px; 
            box-shadow: 0 12px 30px -8px rgba(0,0,0,0.08); 
            overflow: hidden; 
            margin-bottom: 25px; 
        }
        .card-header-custom { 
            padding: 20px 28px; 
            background: #fafafa; 
            border-bottom: 1px solid #e4e4e7; 
            font-weight: 700; 
            font-size: 1.1rem; 
        }
        .card-body-custom { 
            padding: 28px; 
        }
        .item-row { 
            background: #f9f9fb; 
            padding: 20px 18px; 
            border-radius: 16px; 
            margin-bottom: 15px; 
            border: 1px solid #ededf0; 
        }
        .total-amount { 
            font-size: 1.5rem; 
            font-weight: 700; 
            color: #18181b; 
        }
        .form-label { 
            font-weight: 600; 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            letter-spacing: 0.3px; 
            color: #52525b; 
            margin-bottom: 6px; 
        }
        .form-label .text-danger { 
            font-weight: bold; 
        }
        .form-control, .form-select {
            border-radius: 12px; 
            border: 1px solid #e4e4e7; 
            padding: 10px 15px; 
            font-size: 0.95rem; 
        }
        .btn-primary-custom { 
            background: #1c1c1e; 
            border: none; color: white; 
            padding: 10px 18px; 
            border-radius: 12px; 
            font-weight: 600; 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
        }
        .btn-success-custom { 
            background: #10b981; 
            border: none; 
            color: white; 
            padding: 14px 28px; 
            border-radius: 14px; 
            font-weight: 700; 
            display: inline-flex; 
            align-items: center; 
            gap: 10px; 
            box-shadow: 0 8px 18px -6px rgba(16,185,129,0.3); 
        }
        .btn-outline-danger-custom { 
            background: transparent; 
            border: 1px solid #fee2e2; 
            color: #ef4444; 
            padding: 8px 12px; 
            border-radius: 10px;
        }
        .alert-custom {
            border-radius: 16px; 
            padding: 16px 22px; 
            font-weight: 500; 
            border: none; 
        }
        .select2-container--default .select2-selection--single { 
            border-radius: 12px !important; 
            border: 1px solid #e4e4e7 !important;
            height: 46px !important; 
            padding: 8px 15px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered { 
            padding-left: 30px !important; 
        }
        .select2-container--default .select2-selection--single::before { 
            content: "\F52A"; 
            font-family: "bootstrap-icons"; 
            position: absolute; 
            left: 12px; top: 50%; 
            transform: translateY(-50%);
            color: #71717a; 
            font-size: 14px; 
            z-index: 2; 
            pointer-events: none;
        }
        .customer-info-card {
            background: #f0f9ff; 
            border-left: 4px solid #0284c7; 
            padding: 15px; 
            border-radius: 12px;
            margin-top: 15px; 
            font-size: 0.9rem; 
        }
        .customer-info-card i { 
            width: 24px; 
            color: #0284c7; 
        }
        .customer-products-tag {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #d4d4d8;
        }
        .customer-products-tag .badge {
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.8rem;
            padding: 6px 14px;
            border-radius: 20px;
        }
        .customer-products-tag .badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
            background-color: #0a58ca;
        }
        .descriptions-container { 
            margin-top: 10px; 
            padding-left: 15px; 
            border-left: 2px dashed #ccc; 
        }
        .desc-item { 
            display: flex; 
            align-items: center;
            gap: 10px; 
            margin-bottom: 8px; 
        }
        .desc-label { 
            font-weight: 600; 
            width: 30px; 
        }
        .desc-input { 
            flex: 1; 
        }
        .quick-add-hint {
            font-size: 0.75rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
<div class="quotation-container">
    <div class="page-header">
        <a href="staff_dashboard.php?active_section=quotation" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <h3><i class="bi bi-file-earmark-text me-2"></i>Create New Quotation</h3>
    </div>

    <form id="quotationForm">
        <div class="card-custom">
            <div class="card-header-custom"><i class="bi bi-person-badge me-2"></i> Customer Information</div>
            <div class="card-body-custom">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select select2-customer" required>
                            <option value="">-- Choose Customer --</option>
                            <?php while($c = mysqli_fetch_assoc($cust_res)): ?>
                                <option value="<?php echo $c['customer_id']; ?>"><?php echo htmlspecialchars($c['name'] . ' (CUST-' . $c['customer_id'] . ')'); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quotation Date</label>
                        <input type="text" class="form-control" value="<?php echo date('d/m/Y'); ?>" disabled>
                    </div>
                </div>
                <div id="customerInfoContainer" class="customer-info-card" style="display:none;"></div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i> Items</span>
                <button type="button" class="btn-primary-custom" id="addItemBtn"><i class="bi bi-plus-circle"></i> Add Item</button>
            </div>
            <div class="card-body-custom">
                <div id="itemsContainer">
                    <div class="item-row" data-item-index="0">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Product <span class="text-danger">*</span></label>
                                <select name="items[0][product_id]" class="form-select product-select" required>
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['product_id']; ?>" data-price="<?php echo $p['price_per_sqft']; ?>"><?php echo htmlspecialchars($p['door_brand'] . ' (' . $p['material'] . ')'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" min="1" step="1" name="items[0][quantity]" class="form-control qty" value="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Unit Price (RM) <span class="text-danger">*</span></label>
                                <input type="number" min="0" step="0.01" name="items[0][unit_price]" class="form-control price" value="0.00" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Discount (RM)</label>
                                <input type="number" min="0" step="0.01" name="items[0][discount]" class="form-control disc" value="0.00">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn-outline-danger-custom remove-item"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        <div class="descriptions-container" data-desc-container="0"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div><span class="text-muted small text-uppercase fw-semibold">Total Amount</span><div class="total-amount">RM <span id="grandTotal">0.00</span></div></div>
            <button type="submit" class="btn-success-custom" id="submitBtn"><i class="bi bi-file-pdf"></i> Generate PDF & Save</button>
        </div>
        <div id="resultMessage" class="alert-custom mt-4" style="display:none;"></div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const productList = <?php echo json_encode($products); ?>;
    let itemIndex = 1;
    let currentCustomerProducts = [];

    $(document).ready(function() {
        $('.select2-customer').select2({ placeholder: '-- Choose Customer --', allowClear: true, width: '100%' });
        $('.select2-customer').on('change', function() {
            const customerId = $(this).val();
            if (customerId) fetchCustomerData(customerId);
            else {
                $('#customerInfoContainer').hide().empty();
                currentCustomerProducts = [];
            }
        });
        generateDescriptionInputs(0, 1);
    });

    function fetchCustomerData(customerId) {
        fetch(`create_quotation.php?action=get_customer_data&id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('customerInfoContainer');
                    const cust = data.customer;
                    const products = data.products || [];
                    currentCustomerProducts = products;
                    
                    let productsHtml = '';
                    if (products.length > 0) {
                        productsHtml = `
                            <div class="customer-products-tag">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-bold small text-uppercase text-muted me-2">Interested Products:</span>
                                    ${products.map(p => `
                                    <span class="badge bg-primary product-tag" 
                                        data-product-id="${p.product_id}" 
                                        data-product-name="${escapeHtml(p.door_brand)}" 
                                        data-product-price="${p.price_per_sqft}"
                                        onclick="addProductFromTag(this)">
                                        ${escapeHtml(p.door_brand)}
                                    </span>
                                    `).join('')}
                                    <span class="quick-add-hint ms-2"><i class="bi bi-hand-index"></i> Click to add</span>
                                </div>
                            </div>
                        `;
                    } else {
                        productsHtml = `
                            <div class="customer-products-tag">
                                <span class="text-muted small"><i class="bi bi-info-circle"></i> No products selected by this customer.</span>
                            </div>
                        `;
                    }
                    
                    container.innerHTML = `
                        <div class="row">
                            <div class="col-md-6"><i class="bi bi-person"></i> <strong>Name:</strong> ${escapeHtml(cust.name)}</div>
                            <div class="col-md-6"><i class="bi bi-envelope"></i> <strong>Email:</strong> ${escapeHtml(cust.email)}</div>
                            <div class="col-md-6 mt-2"><i class="bi bi-telephone"></i> <strong>Phone:</strong> ${escapeHtml(cust.phone)}</div>
                            <div class="col-md-6 mt-2"><i class="bi bi-gender-ambiguous"></i> <strong>Gender/Race:</strong> ${escapeHtml(cust.gender)} / ${escapeHtml(cust.race)}</div>
                            <div class="col-12 mt-2"><i class="bi bi-geo-alt"></i> <strong>Address:</strong> ${escapeHtml(cust.address)}</div>
                            ${productsHtml}
                        </div>
                    `;
                    container.style.display = 'block';
                }
            }).catch(error => console.error('Error:', error));
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'})[m]);
    }

    function generateDescriptionInputs(rowIdx, qty) {
        const container = document.querySelector(`.item-row[data-item-index="${rowIdx}"] .descriptions-container`);
        if (!container) return;
        container.innerHTML = '';
        for (let i = 0; i < qty; i++) {
            const label = String.fromCharCode(97 + i) + ')';
            const div = document.createElement('div');
            div.className = 'desc-item';
            div.innerHTML = `
                <span class="desc-label">${label}</span>
                <input type="text" name="items[${rowIdx}][details][]" class="form-control desc-input" placeholder="e.g. 3'0&quot; x 7'0&quot; (H)" required>
            `;
            container.appendChild(div);
        }
    }

    function bindQuantityEvents(row) {
        const qtyInput = row.querySelector('.qty');
        if (!qtyInput) return;
        const rowIdx = row.getAttribute('data-item-index');
        qtyInput.addEventListener('input', function() {
            let qty = parseInt(this.value) || 0;
            if (qty < 0) qty = 0;
            generateDescriptionInputs(rowIdx, qty);
            calculateTotal();
        });
    }

    function addProductFromTag(element) {
        const productId = element.getAttribute('data-product-id');
        const productName = element.getAttribute('data-product-name');
        const productPrice = 0;
        
        const product = productList.find(p => p.product_id == productId);
        if (!product) {
            alert('Product details not found.');
            return;
        }
        
        const container = document.getElementById('itemsContainer');
        const existingRows = container.querySelectorAll('.item-row');
        
        let targetRow = null;
        for (let row of existingRows) {
            const select = row.querySelector('.product-select');
            if (select && select.value === '') {
                targetRow = row;
                break;
            }
        }
        
        let rowIndex;
        if (targetRow) {
            rowIndex = targetRow.getAttribute('data-item-index');
            const select = targetRow.querySelector('.product-select');
            select.value = productId;
            const priceInput = targetRow.querySelector('.price');
            if (priceInput) priceInput.value = productPrice.toFixed(2);
            const detailsContainer = targetRow.querySelector('.descriptions-container');
            if (detailsContainer) {
                const descInputs = detailsContainer.querySelectorAll('.desc-input');
                descInputs.forEach(inp => inp.value = '');
            }
            calculateTotal();
        } else {
            addItemRow(productId, productPrice);
        }
    }

    function addItemRow(productId, productPrice) {
        const container = document.getElementById('itemsContainer');
        const newRow = document.createElement('div');
        const idx = itemIndex;
        newRow.classList.add('item-row');
        newRow.setAttribute('data-item-index', idx);
        
        let selectHtml = '<select name="items[' + idx + '][product_id]" class="form-select product-select" required>';
        selectHtml += '<option value="">-- Select Product --</option>';
        productList.forEach(p => {
            const selected = (p.product_id == productId) ? 'selected' : '';
            selectHtml += `<option value="${p.product_id}" data-price="${p.price_per_sqft}" ${selected}>${escapeHtml(p.door_brand + ' (' + p.material + ')')}</option>`;
        });
        selectHtml += '</select>';
        
        newRow.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Product <span class="text-danger">*</span></label>${selectHtml}</div>
                <div class="col-md-2"><label class="form-label">Quantity <span class="text-danger">*</span></label><input type="number" min="1" step="1" name="items[${idx}][quantity]" class="form-control qty" value="1" required></div>
                <div class="col-md-2"><label class="form-label">Unit Price (RM) <span class="text-danger">*</span></label><input type="number" min="0" step="0.01" name="items[${idx}][unit_price]" class="form-control price" value="${productPrice.toFixed(2)}" required></div>
                <div class="col-md-2"><label class="form-label">Discount (RM)</label><input type="number" min="0" step="0.01" name="items[${idx}][discount]" class="form-control disc" value="0.00"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn-outline-danger-custom remove-item"><i class="bi bi-trash"></i></button></div>
            </div>
            <div class="descriptions-container" data-desc-container="${idx}"></div>
        `;
        container.appendChild(newRow);
        
        newRow.querySelector('.remove-item').addEventListener('click', function(e) {
            e.target.closest('.item-row').remove();
            calculateTotal();
        });
        generateDescriptionInputs(idx, 1);
        bindQuantityEvents(newRow);
        newRow.querySelectorAll('.price, .disc').forEach(inp => inp.addEventListener('input', calculateTotal));
        
        newRow.querySelector('.product-select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const priceInput = this.closest('.row').querySelector('.price');
            if (priceInput) priceInput.value = '0.00';
            calculateTotal();
        });
        
        itemIndex++;
        calculateTotal();
    }

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const newRow = document.createElement('div');
        const idx = itemIndex;
        newRow.classList.add('item-row');
        newRow.setAttribute('data-item-index', idx);
        let selectHtml = '<select name="items[' + idx + '][product_id]" class="form-select product-select" required>';
        selectHtml += '<option value="">-- Select Product --</option>';
        productList.forEach(p => selectHtml += `<option value="${p.product_id}" data-price="${p.price_per_sqft}">${escapeHtml(p.door_brand + ' (' + p.material + ')')}</option>`);
        selectHtml += '</select>';
        newRow.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Product <span class="text-danger">*</span></label>${selectHtml}</div>
                <div class="col-md-2"><label class="form-label">Quantity <span class="text-danger">*</span></label><input type="number" min="1" step="1" name="items[${idx}][quantity]" class="form-control qty" value="1" required></div>
                <div class="col-md-2"><label class="form-label">Unit Price (RM) <span class="text-danger">*</span></label><input type="number" min="0" step="0.01" name="items[${idx}][unit_price]" class="form-control price" value="0.00" required></div>
                <div class="col-md-2"><label class="form-label">Discount (RM)</label><input type="number" min="0" step="0.01" name="items[${idx}][discount]" class="form-control disc" value="0.00"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn-outline-danger-custom remove-item"><i class="bi bi-trash"></i></button></div>
            </div>
            <div class="descriptions-container" data-desc-container="${idx}"></div>
        `;
        container.appendChild(newRow);
        newRow.querySelector('.remove-item').addEventListener('click', function(e) {
            e.target.closest('.item-row').remove();
            calculateTotal();
        });
        generateDescriptionInputs(idx, 1);
        bindQuantityEvents(newRow);
        newRow.querySelectorAll('.price, .disc').forEach(inp => inp.addEventListener('input', calculateTotal));
        newRow.querySelector('.product-select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const priceInput = this.closest('.row').querySelector('.price');
            if (priceInput) priceInput.value = price.toFixed(2);
            calculateTotal();
        });
        itemIndex++;
        calculateTotal();
    });

    function initRows() {
        document.querySelectorAll('.item-row').forEach(row => {
            if (!row.getAttribute('data-item-index')) row.setAttribute('data-item-index', itemIndex++);
            bindQuantityEvents(row);
            row.querySelectorAll('.price, .disc').forEach(inp => inp.addEventListener('input', calculateTotal));
            const qty = parseInt(row.querySelector('.qty')?.value) || 1;
            generateDescriptionInputs(row.getAttribute('data-item-index'), qty);
            row.querySelector('.product-select').addEventListener('change', function() {
                const priceInput = this.closest('.row').querySelector('.price');
                if (priceInput) priceInput.value = '0.00';
                calculateTotal();
            });
        });
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.price')?.value) || 0;
            const disc = parseFloat(row.querySelector('.disc')?.value) || 0;
            total += (qty * price) - disc;
        });
        document.getElementById('grandTotal').innerText = total.toFixed(2);
    }

    document.getElementById('quotationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        document.querySelectorAll('.item-row').forEach(row => {
            const detailsInputs = row.querySelectorAll('.desc-input');
            const detailsArray = Array.from(detailsInputs).map(inp => inp.value.trim()).filter(v => v !== '');
            const hiddenDetail = document.createElement('input');
            hiddenDetail.type = 'hidden';
            hiddenDetail.name = `items[${row.getAttribute('data-item-index')}][detail]`;
            hiddenDetail.value = detailsArray.join('\n');
            const existing = row.querySelector(`input[name="items[${row.getAttribute('data-item-index')}][detail]"]`);
            if (existing) existing.remove();
            row.appendChild(hiddenDetail);
        });
        const formData = new FormData(this);
        const submitBtn = document.getElementById('submitBtn');
        const resultDiv = document.getElementById('resultMessage');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
        resultDiv.style.display = 'none';
        fetch('generate_quotation_pdf.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'alert alert-success alert-custom';
                    resultDiv.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Quotation created successfully! Redirecting...';
                    resultDiv.style.display = 'block';
                    setTimeout(() => window.location.href = 'staff_dashboard.php?active_section=quotation&msg=quotation_success&qtn_id=' + data.qtn_id, 1200);
                } else {
                    resultDiv.className = 'alert alert-danger alert-custom';
                    resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Error: ' + (data.message || 'Unknown error');
                    resultDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-file-pdf me-2"></i>Generate PDF & Save';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.className = 'alert alert-danger alert-custom';
                resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Server error. Please try again.';
                resultDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-file-pdf me-2"></i>Generate PDF & Save';
            });
    });

    window.addEventListener('DOMContentLoaded', () => { initRows(); calculateTotal(); });
</script>
</body>
</html>
