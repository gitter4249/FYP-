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

$prod_res = mysqli_query($conn, "SELECT product_id, door_brand, material, design_type, price_per_sqft FROM products WHERE status = 1 ORDER BY door_brand ASC");
$products = [];
while ($p = mysqli_fetch_assoc($prod_res)) $products[] = $p;

$items_query = "SELECT item_id, product_id, description, quantity, area, width_mm, height_mm, unit_price, discount FROM quotation_items WHERE qtn_id = ?";
$stmt_items = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt_items, "i", $edit_id);
mysqli_stmt_execute($stmt_items);
$items_res = mysqli_stmt_get_result($stmt_items);

$grouped = [];
while ($row = mysqli_fetch_assoc($items_res)) {
    $pid = $row['product_id'] ?? 0;
    if (!isset($grouped[$pid])) {
        $grouped[$pid] = [
            'product_id' => $pid,
            'unit_price' => floatval($row['unit_price']),
            'discount' => floatval($row['discount']),
            'subitems' => []
        ];
    }
    $desc = trim((string)$row['description']);
    if (empty($desc) && intval($row['width_mm']) > 0 && intval($row['height_mm']) > 0) {
        $w = intval($row['width_mm']);
        $h = intval($row['height_mm']);
        $wi = round($w / 25.4 / 6) * 6;
        $hi = round($h / 25.4 / 6) * 6;
        $wf = floor($wi / 12);
        $win = round($wi % 12);
        $hf = floor($hi / 12);
        $hin = round($hi % 12);
        $desc = "{$wf}'{$win}\" (W) × {$hf}'{$hin}\" (H)";
    }
    $grouped[$pid]['subitems'][] = [
        'desc' => $desc,
        'area' => floatval($row['area']),
        'width' => intval($row['width_mm']),
        'height' => intval($row['height_mm']),
        'quantity' => floatval($row['quantity']),
    ];
}

$edit_items = [];
foreach ($grouped as $pid => $group) {
    if (empty($group['subitems'])) continue;
    $total_qty = count($group['subitems']);
    $edit_items[] = [
        'product_id' => $group['product_id'],
        'unit_price' => $group['unit_price'],
        'discount' => $group['discount'],
        'subitems' => $group['subitems'],
        'quantity' => $total_qty 
    ];
}

if (empty($edit_items)) {
    $edit_items[] = [
        'product_id' => null,
        'unit_price' => 0,
        'discount' => 0,
        'subitems' => [['desc' => '', 'area' => 0, 'width' => 900, 'height' => 2100]],
        'quantity' => 1
    ];
}

file_put_contents(__DIR__ . '/edit_debug.log', date('Y-m-d H:i:s') . " - edit_items: " . print_r($edit_items, true) . "\n", FILE_APPEND);
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
            font-weight: 700; font-size: 1.1rem; 
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
            border: none; 
            color: white; 
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
                <div id="itemsContainer">
                </div>
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

    function toFeetInches(mm) {
        const totalInches = mm / 25.4;
        const roundedInches = Math.round(totalInches / 6) * 6;
        const feet = Math.floor(roundedInches / 12);
        const inches = Math.round(roundedInches % 12);
        return { feet, inches };
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>"]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            if (m === '"') return '&quot;';
            return m;
        });
    }

    function bindSubitemEvents(subRow, itemIdx) {
        const widthInput = subRow.querySelector('.width-mm');
        const heightInput = subRow.querySelector('.height-mm');
        const areaDisplay = subRow.querySelector('.area-display');

        function updateAreaOnly() {
            const width = parseFloat(widthInput.value) || 0;
            const height = parseFloat(heightInput.value) || 0;
            if (width > 0 && height > 0) {
                const wRounded = toFeetInches(width);
                const hRounded = toFeetInches(height);
                const wMm = (wRounded.feet * 304.8) + (wRounded.inches * 25.4);
                const hMm = (hRounded.feet * 304.8) + (hRounded.inches * 25.4);
                const area = (wMm * hMm) / (304.8 * 304.8);
                areaDisplay.value = area.toFixed(2);
            } else {
                areaDisplay.value = '';
            }
            calculateTotal();
        }

        widthInput.addEventListener('input', updateAreaOnly);
        heightInput.addEventListener('input', updateAreaOnly);

        subRow.querySelector('.remove-subitem').addEventListener('click', function(e) {
            const itemRow = subRow.closest('.item-row');
            const container = itemRow.querySelector('.subitems-container');
            if (container.children.length <= 1) return;
            subRow.remove();
            const qtyInput = itemRow.querySelector('.qty');
            let newQty = parseInt(qtyInput.value) - 1;
            if (newQty < 1) newQty = 1;
            qtyInput.value = newQty;
            updateSubitemIndices(itemRow);
            calculateTotal();
        });
    }

    function createSubitemRow(itemIdx, subIdx, data = null) {
        const letter = String.fromCharCode(97 + subIdx);
        let width = data && data.width ? data.width : 900;
        let height = data && data.height ? data.height : 2100;
        let area = data && data.area ? parseFloat(data.area).toFixed(2) : '0.00';
        let desc = data && data.desc ? data.desc : '';

        console.log('Subitem desc:', desc);

        return `
            <div class="subitem-row" data-subitem-index="${subIdx}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">WIDTH (MM) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control width-mm" name="items[${itemIdx}][subitems][${subIdx}][width]" value="${width}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">HEIGHT (MM) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control height-mm" name="items[${itemIdx}][subitems][${subIdx}][height]" value="${height}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">AREA (SQFT)</label>
                        <input type="text" class="form-control area-display" name="items[${itemIdx}][subitems][${subIdx}][area]" value="${area}" readonly>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn-outline-danger-custom remove-subitem"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-auto"><span class="subitem-label">${subIdx === 0 ? '<span class="text-danger">*</span>' : ''}${letter})</span></div>
                    <div class="col"><input type="text" class="form-control desc-input" name="items[${itemIdx}][subitems][${subIdx}][desc]" placeholder="e.g. 3'0&quot; × 7'0&quot; (H) - (optional note)" value="${escapeHtml(desc)}" required></div>
                </div>
            </div>
        `;
    }

    function createItemRow(index, data = null) {
        const div = document.createElement('div');
        div.className = 'item-row';
        div.dataset.itemIndex = index;

        const productId = data && data.product_id ? data.product_id : '';
        const unitPrice = data ? parseFloat(data.unit_price).toFixed(2) : '0.00';
        const discount = data ? parseFloat(data.discount).toFixed(2) : '0.00';
        const subitems = data && data.subitems ? data.subitems : [{ desc: '', area: 0, width: 900, height: 2100 }];
        const quantity = subitems.length;

        let selectHtml = `<select name="items[${index}][product_id]" class="form-select product-select" required>`;
        selectHtml += `<option value="">-- Select Product --</option>`;
        productList.forEach(p => {
            const selected = (productId && productId == p.product_id) ? 'selected' : '';
            selectHtml += `<option value="${p.product_id}" ${selected} data-price="${p.price_per_sqft}">${escapeHtml(p.door_brand + ' (' + p.material + ')')}</option>`;
        });
        selectHtml += `</select>`;

        div.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Product <span class="text-danger">*</span></label>${selectHtml}</div>
                <div class="col-md-2"><label class="form-label">Quantity</label><input type="number" min="1" step="1" name="items[${index}][quantity]" class="form-control qty" value="${quantity}"></div>
                <div class="col-md-2"><label class="form-label">Unit Price (RM) <span class="text-danger">*</span></label><input type="number" min="0" step="0.01" name="items[${index}][unit_price]" class="form-control price" value="${unitPrice}" required></div>
                <div class="col-md-2"><label class="form-label">Discount (RM)</label><input type="number" min="0" step="0.01" name="items[${index}][discount]" class="form-control disc" value="${discount}"></div>
                <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn-outline-danger-custom remove-item"><i class="bi bi-trash"></i></button></div>
            </div>
            <div class="subitems-container" data-subitem-count="${quantity}">
                ${subitems.map((sub, idx) => createSubitemRow(index, idx, sub)).join('')}
            </div>
        `;

        const container = div.querySelector('.subitems-container');
        container.querySelectorAll('.subitem-row').forEach(subRow => bindSubitemEvents(subRow, index));

        return div;
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

    function renderItems() {
        console.log('existingItems:', existingItems);
        const container = document.getElementById('itemsContainer');
        container.innerHTML = '';
        if (existingItems.length === 0) {
            const defaultItem = { product_id: '', unit_price: 0, discount: 0, subitems: [{ desc: '', area: 0, width: 900, height: 2100 }], quantity: 1 };
            existingItems.push(defaultItem);
        }
        existingItems.forEach((item, idx) => {
            const row = createItemRow(idx, item);
            container.appendChild(row);
            const subRows = row.querySelectorAll('.subitem-row');
            subRows.forEach(sub => {
                bindSubitemEvents(sub, idx);
                const widthInput = sub.querySelector('.width-mm');
                const heightInput = sub.querySelector('.height-mm');
                if (widthInput && heightInput) {
                    widthInput.dispatchEvent(new Event('input'));
                }
            });
            const qtyInput = row.querySelector('.qty');
            qtyInput.addEventListener('change', function() {
                const targetQty = parseInt(this.value) || 1;
                if (targetQty < 1) { this.value = 1; return; }
                const containerSub = row.querySelector('.subitems-container');
                let currentCount = containerSub.querySelectorAll('.subitem-row').length;
                const idx2 = row.dataset.itemIndex;
                if (targetQty > currentCount) {
                    for (let i = currentCount; i < targetQty; i++) {
                        const lastSub = containerSub.querySelector('.subitem-row:last-child');
                        const newSubHtml = createSubitemRow(idx2, i, null);
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = newSubHtml;
                        const newSub = tempDiv.firstElementChild;
                        if (lastSub) {
                            newSub.querySelector('.width-mm').value = lastSub.querySelector('.width-mm').value || '900';
                            newSub.querySelector('.height-mm').value = lastSub.querySelector('.height-mm').value || '2100';
                        }
                        containerSub.appendChild(newSub);
                        bindSubitemEvents(newSub, idx2);
                        const wInput = newSub.querySelector('.width-mm');
                        const hInput = newSub.querySelector('.height-mm');
                        if (wInput && hInput) {
                            wInput.dispatchEvent(new Event('input'));
                        }
                    }
                } else if (targetQty < currentCount) {
                    const subitems = containerSub.querySelectorAll('.subitem-row');
                    for (let i = subitems.length - 1; i >= targetQty; i--) {
                        subitems[i].remove();
                    }
                }
                updateSubitemIndices(row);
                calculateTotal();
            });
            row.querySelector('.product-select').addEventListener('change', function() {
                const price = parseFloat(this.options[this.selectedIndex]?.getAttribute('data-price')) || 0;
                row.querySelector('.price').value = price.toFixed(2);
                calculateTotal();
            });
            row.querySelectorAll('.price, .disc').forEach(inp => inp.addEventListener('input', calculateTotal));
        });
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('.item-row');
                if (document.querySelectorAll('.item-row').length <= 1) {
                    alert('You must keep at least one item.');
                    return;
                }
                row.remove();
                calculateTotal();
            });
        });
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const discount = parseFloat(row.querySelector('.disc').value) || 0;
            let totalArea = 0;
            row.querySelectorAll('.subitem-row .area-display').forEach(areaInput => {
                totalArea += parseFloat(areaInput.value) || 0;
            });
            total += (totalArea * price) - discount;
        });
        document.getElementById('grandTotal').innerText = total.toFixed(2);
        return total;
    }

    document.getElementById('addItemBtn').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const idx = itemIndex;
        const newItem = { product_id: '', unit_price: 0, discount: 0, subitems: [{ desc: '', area: 0, width: 900, height: 2100 }], quantity: 1 };
        const row = createItemRow(idx, newItem);
        container.appendChild(row);
        const subRows = row.querySelectorAll('.subitem-row');
        subRows.forEach(sub => {
            bindSubitemEvents(sub, idx);
            const wInput = sub.querySelector('.width-mm');
            const hInput = sub.querySelector('.height-mm');
            if (wInput && hInput) {
                wInput.dispatchEvent(new Event('input'));
            }
        });
        const qtyInput = row.querySelector('.qty');
        qtyInput.addEventListener('change', function() {
            const targetQty = parseInt(this.value) || 1;
            if (targetQty < 1) { this.value = 1; return; }
            const containerSub = row.querySelector('.subitems-container');
            let currentCount = containerSub.querySelectorAll('.subitem-row').length;
            const idx2 = row.dataset.itemIndex;
            if (targetQty > currentCount) {
                for (let i = currentCount; i < targetQty; i++) {
                    const lastSub = containerSub.querySelector('.subitem-row:last-child');
                    const newSubHtml = createSubitemRow(idx2, i, null);
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newSubHtml;
                    const newSub = tempDiv.firstElementChild;
                    if (lastSub) {
                        newSub.querySelector('.width-mm').value = lastSub.querySelector('.width-mm').value || '900';
                        newSub.querySelector('.height-mm').value = lastSub.querySelector('.height-mm').value || '2100';
                    }
                    containerSub.appendChild(newSub);
                    bindSubitemEvents(newSub, idx2);
                    const wInput2 = newSub.querySelector('.width-mm');
                    const hInput2 = newSub.querySelector('.height-mm');
                    if (wInput2 && hInput2) {
                        wInput2.dispatchEvent(new Event('input'));
                    }
                }
            } else if (targetQty < currentCount) {
                const subitems = containerSub.querySelectorAll('.subitem-row');
                for (let i = subitems.length - 1; i >= targetQty; i--) {
                    subitems[i].remove();
                }
            }
            updateSubitemIndices(row);
            calculateTotal();
        });
        row.querySelector('.product-select').addEventListener('change', function() {
            const price = parseFloat(this.options[this.selectedIndex]?.getAttribute('data-price')) || 0;
            row.querySelector('.price').value = price.toFixed(2);
            calculateTotal();
        });
        row.querySelectorAll('.price, .disc').forEach(inp => inp.addEventListener('input', calculateTotal));
        row.querySelector('.remove-item').addEventListener('click', function() {
            const row2 = this.closest('.item-row');
            if (document.querySelectorAll('.item-row').length <= 1) { alert('You must keep at least one item.'); return; }
            row2.remove();
            calculateTotal();
        });
        itemIndex++;
        calculateTotal();
    });

    document.getElementById('editQuotationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('submitBtn');
        const resultDiv = document.getElementById('resultMessage');

        const total = calculateTotal();
        if (total <= 0) {
            alert('Total amount must be greater than 0. Please add items or adjust prices.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
        resultDiv.style.display = 'none';

        const formData = new FormData(this);
        fetch('update_quotation.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'alert alert-success alert-custom';
                    resultDiv.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> Quotation updated successfully! <a href="../${data.pdf}" target="_blank" class="fw-bold text-dark">View PDF</a>`;
                    resultDiv.style.display = 'block';
                    setTimeout(() => window.location.href = 'staff_dashboard.php?active_section=quotation', 2000);
                } else {
                    resultDiv.className = 'alert alert-danger alert-custom';
                    resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Error: ' + (data.message || 'Unknown error');
                    resultDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-file-pdf me-2"></i>Update & Regenerate PDF';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.className = 'alert alert-danger alert-custom';
                resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Server error. Please try again.';
                resultDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-file-pdf me-2"></i>Update & Regenerate PDF';
            });
    });

renderItems();
</script>
</body>
</html>