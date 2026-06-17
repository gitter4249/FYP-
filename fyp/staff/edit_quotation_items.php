<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}
$staff_id = intval($_SESSION['staff_id']);
$staff_name = $_SESSION['staff_name'] ?? "Staff";

$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($edit_id <= 0) die("Invalid quotation ID.");

$qtn_query = "SELECT q.*, c.name as customer_name, c.customer_id, c.phone, c.email, c.address 
              FROM quotations q LEFT JOIN customers c ON q.customer_id = c.customer_id WHERE q.qtn_id = ?";
$stmt = mysqli_prepare($conn, $qtn_query);
mysqli_stmt_bind_param($stmt, "i", $edit_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($res);
if (!$quotation) die("Quotation not found.");

$prod_res = mysqli_query($conn, "SELECT product_id, door_brand, material, design_type FROM products WHERE status = 1 ORDER BY door_brand ASC");
$products = [];
while ($p = mysqli_fetch_assoc($prod_res)) $products[] = $p;

$items_query = "SELECT item_id, product_id, description, quantity, unit_price, discount FROM quotation_items WHERE qtn_id = ?";
$stmt_items = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt_items, "i", $edit_id);
mysqli_stmt_execute($stmt_items);
$items_res = mysqli_stmt_get_result($stmt_items);
$edit_items = [];
while ($row = mysqli_fetch_assoc($items_res)) {
    $row['product_id'] = is_null($row['product_id']) ? null : (int)$row['product_id'];
    $desc_lines = explode("\n", $row['description']);
    $product_name = array_shift($desc_lines);
    $row['product_name'] = $product_name;
    $row['details'] = $desc_lines;
    $edit_items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Quotation | YS Aluminium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px 20px; margin: 0; }
        .quotation-container { max-width: 1200px; width: 100%; margin: 0 auto; }
        .page-header { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
        .page-header h3 { margin: 0; font-weight: 700; color: #1c1c1e; }
        .btn-back { background: white; border: 1px solid #e4e4e7; border-radius: 10px; padding: 10px 20px; color: #3a3a3c; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-back:hover { background: #fafafa; transform: translateX(-3px); }
        .card-custom { background: white; border: 1px solid #e4e4e7; border-radius: 20px; box-shadow: 0 12px 30px -8px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 25px; }
        .card-header-custom { padding: 20px 28px; background: #fafafa; border-bottom: 1px solid #e4e4e7; font-weight: 700; font-size: 1.1rem; }
        .card-body-custom { padding: 28px; }
        .item-row { background: #f9f9fb; padding: 20px 18px; border-radius: 16px; margin-bottom: 15px; border: 1px solid #ededf0; }
        .total-amount { font-size: 1.5rem; font-weight: 700; color: #18181b; }
        .form-label { font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.3px; color: #52525b; margin-bottom: 6px; }
        .form-label .text-danger { font-weight: bold; }
        .form-control, .form-select { border-radius: 12px; border: 1px solid #e4e4e7; padding: 10px 15px; font-size: 0.95rem; }
        .btn-primary-custom { background: #1c1c1e; border: none; color: white; padding: 10px 18px; border-radius: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .btn-success-custom { background: #10b981; border: none; color: white; padding: 14px 28px; border-radius: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 8px 18px -6px rgba(16,185,129,0.3); }
        .btn-outline-danger-custom { background: transparent; border: 1px solid #fee2e2; color: #ef4444; padding: 8px 12px; border-radius: 10px; }
        .alert-custom { border-radius: 16px; padding: 16px 22px; font-weight: 500; border: none; }
        .customer-info-card { background: #f0f9ff; border-left: 4px solid #0284c7; padding: 15px; border-radius: 12px; margin-top: 15px; font-size: 0.9rem; }
        .customer-info-card i { width: 24px; color: #0284c7; }
        .descriptions-container { margin-top: 10px; padding-left: 15px; border-left: 2px dashed #ccc; }
        .desc-item { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .desc-label { font-weight: 600; width: 30px; }
        .desc-input { flex: 1; }
    </style>
</head>
<body>
<div class="quotation-container">
    <div class="page-header">
        <a href="staff_dashboard.php?active_section=quotation" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        <h3><i class="bi bi-pencil-square me-2"></i>Edit Quotation: <?php echo htmlspecialchars($quotation['qtn_number']); ?></h3>
    </div>

    <form id="editQuotationForm">
        <input type="hidden" name="qtn_id" value="<?php echo $edit_id; ?>">

        <div class="card-custom">
            <div class="card-header-custom"><i class="bi bi-person-badge me-2"></i> Customer Information</div>
            <div class="card-body-custom">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Customer</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($quotation['customer_name'] . ' (CUST-' . $quotation['customer_id'] . ')'); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quotation Date</label>
                        <input type="text" class="form-control" value="<?php echo date('d/m/Y', strtotime($quotation['created_at'])); ?>" readonly>
                    </div>
                </div>
                <div class="customer-info-card">
                    <div class="row">
                        <div class="col-md-6"><i class="bi bi-telephone"></i> <strong>Phone:</strong> <?php echo htmlspecialchars($quotation['phone'] ?? '-'); ?></div>
                        <div class="col-md-6"><i class="bi bi-envelope"></i> <strong>Email:</strong> <?php echo htmlspecialchars($quotation['email'] ?? '-'); ?></div>
                        <div class="col-12 mt-2"><i class="bi bi-geo-alt"></i> <strong>Address:</strong> <?php echo htmlspecialchars($quotation['address'] ?? '-'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i> Items</span>
                <button type="button" class="btn-primary-custom" id="addItemBtn"><i class="bi bi-plus-circle"></i> Add Item</button>
            </div>
            <div class="card-body-custom">
                <div id="itemsContainer"></div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div><span class="text-muted small text-uppercase fw-semibold">Total Amount</span><div class="total-amount">RM <span id="grandTotal">0.00</span></div></div>
            <button type="submit" class="btn-success-custom" id="submitBtn"><i class="bi bi-file-pdf"></i> Update & Regenerate PDF</button>
        </div>
        <div id="resultMessage" class="alert-custom mt-4" style="display:none;"></div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const productList = <?php echo json_encode($products); ?>;
    const existingItems = <?php echo json_encode($edit_items); ?>;
    let itemIndex = existingItems.length;

    console.log('Existing Items:', existingItems);

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'})[m]);
    }

    function generateDescriptionInputs(container, qty, existingDetails = []) {
        container.innerHTML = '';
        for (let i = 0; i < qty; i++) {
            const label = String.fromCharCode(97 + i) + ')';
            const value = (existingDetails && existingDetails[i]) ? existingDetails[i] : '';
            const div = document.createElement('div');
            div.className = 'desc-item';
            div.innerHTML = `<span class="desc-label">${label}</span><input type="text" class="form-control desc-input" placeholder="e.g. 3'0&quot; x 7'0&quot; (H)" value="${escapeHtml(value)}">`;
            container.appendChild(div);
        }
    }

    function createItemRow(index, data = null) {
        const div = document.createElement('div');
        div.classList.add('item-row');
        div.setAttribute('data-item-index', index);
        let productId = (data && data.product_id !== null && data.product_id !== undefined) ? data.product_id : '';
        if (typeof productId === 'string') productId = parseInt(productId);
        const qty = data ? parseInt(data.quantity) : 1;
        const price = data ? parseFloat(data.unit_price).toFixed(2) : '0.00';
        const disc = data ? parseFloat(data.discount).toFixed(2) : '0.00';
        const details = data ? (data.details || []) : [];

        let productSelectHtml = `<select name="items[${index}][product_id]" class="form-select product-select" required>`;
        productSelectHtml += `<option value="">-- Select Product --</option>`;
        productList.forEach(p => {
            const selected = (productId !== '' && productId == p.product_id) ? 'selected' : '';
            productSelectHtml += `<option value="${p.product_id}" ${selected}>${escapeHtml(p.door_brand + ' (' + p.material + ')')}</option>`;
        });
        productSelectHtml += `</select>`;

        div.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Product <span class="text-danger">*</span></label>${productSelectHtml}</div>
                <div class="col-md-2"><label class="form-label">Quantity <span class="text-danger">*</span></label><input type="number" min="1" step="1" name="items[${index}][quantity]" class="form-control qty" value="${qty}" required></div>
                <div class="col-md-2"><label class="form-label">Unit Price (RM) <span class="text-danger">*</span></label><input type="number" min="0" step="0.01" name="items[${index}][unit_price]" class="form-control price" value="${price}" required></div>
                <div class="col-md-2"><label class="form-label">Discount (RM)</label><input type="number" min="0" step="0.01" name="items[${index}][discount]" class="form-control disc" value="${disc}"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn-outline-danger-custom remove-item"><i class="bi bi-trash"></i></button></div>
            </div>
            <div class="descriptions-container" data-desc-container="${index}"></div>
        `;
        const descContainer = div.querySelector('.descriptions-container');
        generateDescriptionInputs(descContainer, qty, details);
        return div;
    }

    function renderExistingItems() {
        const container = document.getElementById('itemsContainer');
        container.innerHTML = '';
        if (existingItems.length === 0) addNewRow();
        else existingItems.forEach((item, idx) => container.appendChild(createItemRow(idx, item)));
        bindRemoveButtons();
        bindQuantityEvents();
        calculateTotal();
    }

    function bindQuantityEvents() {
        document.querySelectorAll('.item-row').forEach(row => {
            const qtyInput = row.querySelector('.qty');
            if (!qtyInput) return;
            qtyInput.removeEventListener('input', handleQuantityChange);
            qtyInput.addEventListener('input', handleQuantityChange);
        });
    }

    function handleQuantityChange(e) {
        const row = e.target.closest('.item-row');
        const newQty = parseInt(e.target.value) || 1;
        const descContainer = row.querySelector('.descriptions-container');
        const currentDescInputs = descContainer.querySelectorAll('.desc-input');
        const existingDetails = Array.from(currentDescInputs).map(inp => inp.value);
        generateDescriptionInputs(descContainer, newQty, existingDetails);
        calculateTotal();
    }

    function addNewRow() {
        const container = document.getElementById('itemsContainer');
        container.appendChild(createItemRow(itemIndex, null));
        itemIndex++;
        bindRemoveButtons();
        bindQuantityEvents();
        calculateTotal();
    }

    document.getElementById('addItemBtn').addEventListener('click', addNewRow);

    function bindRemoveButtons() {
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.removeEventListener('click', removeHandler);
            btn.addEventListener('click', removeHandler);
        });
    }

    function removeHandler(e) {
        e.target.closest('.item-row').remove();
        calculateTotal();
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

    document.addEventListener('input', calculateTotal);

    document.getElementById('editQuotationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        document.querySelectorAll('.item-row').forEach(row => {
            const idx = row.getAttribute('data-item-index');
            const productSelect = row.querySelector('.product-select');
            const productId = productSelect.value;
            let productName = '';
            if (productId) {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                productName = selectedOption.text.split(' (')[0];
            }
            const descInputs = row.querySelectorAll('.desc-input');
            const details = Array.from(descInputs).map(inp => inp.value.trim()).filter(v => v !== '');
            let description = productName;
            if (details.length > 0) description += '\n' + details.join('\n');
            const hiddenDesc = document.createElement('input');
            hiddenDesc.type = 'hidden';
            hiddenDesc.name = `items[${idx}][description]`;
            hiddenDesc.value = description;
            row.appendChild(hiddenDesc);
        });
        const formData = new FormData(this);
        const submitBtn = document.getElementById('submitBtn');
        const resultDiv = document.getElementById('resultMessage');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
        resultDiv.style.display = 'none';
        fetch('update_quotation.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.classList.add('alert-success');
                    resultDiv.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> Quotation updated successfully! <a href="../${data.pdf}" target="_blank" class="fw-bold text-dark">View PDF</a>`;
                    resultDiv.style.display = 'block';
                    setTimeout(() => window.location.href = 'staff_dashboard.php?active_section=quotation', 2000);
                } else {
                    resultDiv.classList.add('alert-danger');
                    resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Error: ' + (data.message || 'Unknown error');
                    resultDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-file-pdf me-2"></i>Update & Regenerate PDF';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.classList.add('alert-danger');
                resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Server error. Please try again.';
                resultDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-file-pdf me-2"></i>Update & Regenerate PDF';
            });
    });

    renderExistingItems();
</script>
</body>
</html>