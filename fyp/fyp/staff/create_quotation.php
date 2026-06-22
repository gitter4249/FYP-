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
    echo json_encode(['success' => true, 'customer' => $customer]);
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
            left: 12px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #71717a; f
            ont-size: 14px; 
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
            width: 24px; color: #0284c7; 
        }
        .subitems-container { 
            margin-top: 20px;
            padding-left: 10px; 
            border-left: 2px dashed #d4d4d8; 
        }
        .subitem-row { 
            background: white; 
            padding: 15px 15px 10px; 
            border-radius: 12px; 
            margin-bottom: 15px; 
            border: 1px solid #e4e4e7; 
        }
        .subitem-label { 
            font-weight: 700; 
            font-size: 1rem; 
            color: #3f3f46; 
            line-height: 38px; 
        }
        .subitem-row .form-control { 
            height: 38px; 
            padding: 6px 10px; 
            font-size: 0.9rem; 
        }
        .subitem-row .remove-subitem { 
            padding: 4px 8px; 
        }
        .subitem-row .col-auto { 
            display: flex; 
            align-items: center; 
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
                                <input type="number" min="1" step="1" name="items[0][quantity]" class="form-control qty" value="1">
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

                        <div class="subitems-container" data-subitem-count="1">
                            <div class="subitem-row" data-subitem-index="0">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label">WIDTH (MM) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control width-mm" name="items[0][subitems][0][width]" value="900">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">HEIGHT (MM) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control height-mm" name="items[0][subitems][0][height]" value="2100">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">AREA (SQFT)</label>
                                        <input type="text" class="form-control area-display" name="items[0][subitems][0][area]" readonly>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn-outline-danger-custom remove-subitem" style="display:none;"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-auto"><span class="subitem-label"> <span class="text-danger">*</span>a) </span></div>
                                    <div class="col"><input type="text" class="form-control desc-input" name="items[0][subitems][0][desc]" placeholder="e.g. 3'0&quot; × 7'0&quot; (H)" required></div>
                                </div>
                            </div>
                        </div>
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

    $(document).ready(function() {
        $('.select2-customer').select2({ placeholder: '-- Choose Customer --', allowClear: true, width: '100%' });
        $('.select2-customer').on('change', function() {
            const customerId = $(this).val();
            if (customerId) fetchCustomerData(customerId);
            else {
                $('#customerInfoContainer').hide().empty();
            }
        });
        document.querySelectorAll('.item-row').forEach(row => bindItemEvents(row));
        document.querySelectorAll('.subitem-row').forEach(sub => {
            bindSubitemEvents(sub);
            const widthInput = sub.querySelector('.width-mm');
            const heightInput = sub.querySelector('.height-mm');
            if (widthInput && heightInput) {
                widthInput.dispatchEvent(new Event('input'));
            }
        });
        calculateTotal();
    });

    function fetchCustomerData(customerId) {
        fetch(`create_quotation.php?action=get_customer_data&id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('customerInfoContainer');
                    const cust = data.customer;
                    container.innerHTML = `
                        <div class="row">
                            <div class="col-md-6"><i class="bi bi-person"></i> <strong>Name:</strong> ${escapeHtml(cust.name)}</div>
                            <div class="col-md-6"><i class="bi bi-envelope"></i> <strong>Email:</strong> ${escapeHtml(cust.email)}</div>
                            <div class="col-md-6 mt-2"><i class="bi bi-telephone"></i> <strong>Phone:</strong> ${escapeHtml(cust.phone)}</div>
                            <div class="col-md-6 mt-2"><i class="bi bi-gender-ambiguous"></i> <strong>Gender/Race:</strong> ${escapeHtml(cust.gender)} / ${escapeHtml(cust.race)}</div>
                            <div class="col-12 mt-2"><i class="bi bi-geo-alt"></i> <strong>Address:</strong> ${escapeHtml(cust.address)}</div>
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

    function toFeetInches(mm) {
        const totalInches = mm / 25.4;
        const roundedInches = Math.round(totalInches / 6) * 6;
        const feet = Math.floor(roundedInches / 12);
        const inches = Math.round(roundedInches % 12);
        return { feet, inches };
    }

    function bindSubitemEvents(subitemRow) {
        const widthInput = subitemRow.querySelector('.width-mm');
        const heightInput = subitemRow.querySelector('.height-mm');
        const areaDisplay = subitemRow.querySelector('.area-display');
        const descInput = subitemRow.querySelector('.desc-input');

        function updateDescAndArea() {
            const width = parseFloat(widthInput.value) || 0;
            const height = parseFloat(heightInput.value) || 0;
            if (width > 0 && height > 0) {
                const wRounded = toFeetInches(width);
                const hRounded = toFeetInches(height);
                const wMm = (wRounded.feet * 304.8) + (wRounded.inches * 25.4);
                const hMm = (hRounded.feet * 304.8) + (hRounded.inches * 25.4);
                const area = (wMm * hMm) / (304.8 * 304.8);
                areaDisplay.value = area.toFixed(2);
                const dimsDesc = `${wRounded.feet}'${wRounded.inches}" (W) × ${hRounded.feet}'${hRounded.inches}" (H)`;
                descInput.value = dimsDesc;
            } else {
                areaDisplay.value = '';
                descInput.value = '';
            }
            calculateTotal();
        }

        widthInput.addEventListener('input', updateDescAndArea);
        heightInput.addEventListener('input', updateDescAndArea);

        subitemRow.querySelector('.remove-subitem').addEventListener('click', function(e) {
            const itemRow = subitemRow.closest('.item-row');
            const container = itemRow.querySelector('.subitems-container');
            if (container.children.length <= 1) return;
            subitemRow.remove();
            const qtyInput = itemRow.querySelector('.qty');
            let newQty = parseInt(qtyInput.value) - 1;
            if (newQty < 1) newQty = 1;
            qtyInput.value = newQty;
            updateSubitemIndices(itemRow);
            calculateTotal();
        });
    }

    function updateSubitemIndices(itemRow) {
        const container = itemRow.querySelector('.subitems-container');
        const subitems = container.querySelectorAll('.subitem-row');
        const itemIdx = itemRow.dataset.itemIndex;
        subitems.forEach((sub, idx) => {
            sub.dataset.subitemIndex = idx;
            const label = sub.querySelector('.subitem-label');
            if (label) {
                const letter = String.fromCharCode(97 + idx);
                label.textContent = letter + ')';
            }
            sub.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.name;
                if (name) {
                    const newName = name.replace(/items\[\d+\]\[subitems\]\[\d+\]/, `items[${itemIdx}][subitems][${idx}]`);
                    input.name = newName;
                }
            });
            const removeBtn = sub.querySelector('.remove-subitem');
            if (removeBtn) {
                removeBtn.style.display = (subitems.length > 1) ? '' : 'none';
            }
        });
        container.dataset.subitemCount = subitems.length;
    }

    function bindItemEvents(itemRow) {
        const qtyInput = itemRow.querySelector('.qty');
        qtyInput.addEventListener('change', function() {
            const targetQty = parseInt(this.value) || 1;
            if (targetQty < 1) { this.value = 1; return; }
            const container = itemRow.querySelector('.subitems-container');
            let currentCount = container.querySelectorAll('.subitem-row').length;
            const itemIdx = itemRow.dataset.itemIndex;

            if (targetQty > currentCount) {
                for (let i = currentCount; i < targetQty; i++) {
                    const lastSub = container.querySelector('.subitem-row:last-child');
                    const newSub = createSubitemRow(itemIdx, i);
                    if (lastSub) {
                        newSub.querySelector('.width-mm').value = lastSub.querySelector('.width-mm').value || '900';
                        newSub.querySelector('.height-mm').value = lastSub.querySelector('.height-mm').value || '2100';
                    }
                    container.appendChild(newSub);
                    bindSubitemEvents(newSub);
                    const wInput = newSub.querySelector('.width-mm');
                    const hInput = newSub.querySelector('.height-mm');
                    if (wInput && hInput) {
                        wInput.dispatchEvent(new Event('input'));
                    }
                }
            } else if (targetQty < currentCount) {
                const subitems = container.querySelectorAll('.subitem-row');
                for (let i = subitems.length - 1; i >= targetQty; i--) {
                    subitems[i].remove();
                }
            }
            updateSubitemIndices(itemRow);
            calculateTotal();
        });

        itemRow.querySelector('.remove-item').addEventListener('click', function() {
            const container = document.getElementById('itemsContainer');
            if (container.children.length <= 1) {
                alert('You must keep at least one item.');
                return;
            }
            itemRow.remove();
            reindexAllItems();
            calculateTotal();
        });

        itemRow.querySelector('.product-select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const priceInput = this.closest('.row').querySelector('.price');
            if (priceInput) priceInput.value = price.toFixed(2);
            calculateTotal();
        });

        itemRow.querySelectorAll('.price, .disc').forEach(inp => inp.addEventListener('input', calculateTotal));
    }

    function createSubitemRow(itemIdx, subIdx) {
        const letter = String.fromCharCode(97 + subIdx);
        const div = document.createElement('div');
        div.className = 'subitem-row';
        div.dataset.subitemIndex = subIdx;
        div.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">WIDTH (MM) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control width-mm" name="items[${itemIdx}][subitems][${subIdx}][width]" value="900">
                </div>
                <div class="col-md-3">
                    <label class="form-label">HEIGHT (MM) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control height-mm" name="items[${itemIdx}][subitems][${subIdx}][height]" value="2100">
                </div>
                <div class="col-md-2">
                    <label class="form-label">AREA (SQFT)</label>
                    <input type="text" class="form-control area-display" name="items[${itemIdx}][subitems][${subIdx}][area]" readonly>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn-outline-danger-custom remove-subitem"><i class="bi bi-trash"></i></button>
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-auto"><span class="subitem-label">${letter})</span></div>
                <div class="col"><input type="text" class="form-control desc-input" name="items[${itemIdx}][subitems][${subIdx}][desc]" placeholder="e.g. 3'0&quot; × 7'0&quot; (H)" required></div>
            </div>
        `;
        return div;
    }

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const idx = itemIndex;
        const newRow = document.createElement('div');
        newRow.className = 'item-row';
        newRow.dataset.itemIndex = idx;

        let selectHtml = '<select name="items[' + idx + '][product_id]" class="form-select product-select" required>';
        selectHtml += '<option value="">-- Select Product --</option>';
        productList.forEach(p => selectHtml += `<option value="${p.product_id}" data-price="${p.price_per_sqft}">${escapeHtml(p.door_brand + ' (' + p.material + ')')}</option>`);
        selectHtml += '</select>';

        newRow.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Product <span class="text-danger">*</span></label>${selectHtml}</div>
                <div class="col-md-2"><label class="form-label">Quantity</label><input type="number" min="1" step="1" name="items[${idx}][quantity]" class="form-control qty" value="1"></div>
                <div class="col-md-2"><label class="form-label">Unit Price (RM) <span class="text-danger">*</span></label><input type="number" min="0" step="0.01" name="items[${idx}][unit_price]" class="form-control price" value="0.00" required></div>
                <div class="col-md-2"><label class="form-label">Discount (RM)</label><input type="number" min="0" step="0.01" name="items[${idx}][discount]" class="form-control disc" value="0.00"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn-outline-danger-custom remove-item"><i class="bi bi-trash"></i></button></div>
            </div>
            <div class="subitems-container" data-subitem-count="1">
                ${createSubitemRow(idx, 0).outerHTML}
            </div>
        `;
        container.appendChild(newRow);
        bindItemEvents(newRow);
        const subitem = newRow.querySelector('.subitem-row');
        bindSubitemEvents(subitem);
        subitem.querySelector('.remove-subitem').style.display = 'none';
        const wInput = subitem.querySelector('.width-mm');
        const hInput = subitem.querySelector('.height-mm');
        if (wInput && hInput) {
            wInput.dispatchEvent(new Event('input'));
        }
        itemIndex++;
        calculateTotal();
    });

    function reindexAllItems() {
        const items = document.querySelectorAll('.item-row');
        items.forEach((row, idx) => {
            row.dataset.itemIndex = idx;
            row.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.name;
                if (name) {
                    const newName = name.replace(/items\[\d+\]/, `items[${idx}]`);
                    input.name = newName;
                }
            });
            updateSubitemIndices(row);
        });
    }

    function calculateTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(itemRow => {
            const price = parseFloat(itemRow.querySelector('.price').value) || 0;
            const discount = parseFloat(itemRow.querySelector('.disc').value) || 0;
            let totalArea = 0;
            itemRow.querySelectorAll('.subitem-row .area-display').forEach(areaInput => {
                totalArea += parseFloat(areaInput.value) || 0;
            });
            grandTotal += (totalArea * price) - discount;
        });
        document.getElementById('grandTotal').innerText = grandTotal.toFixed(2);
        return grandTotal;
    }

    document.getElementById('quotationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('submitBtn');
        const resultDiv = document.getElementById('resultMessage');

        const total = calculateTotal();
        if (total <= 0) {
            alert('Total amount must be greater than 0. Please add items or adjust prices.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
        resultDiv.style.display = 'none';

        const formData = new FormData(this);
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
</script>
</body>
</html>