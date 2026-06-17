<?php
session_start();
require '../includes/db.php';
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}
$staff_id = intval($_SESSION['staff_id']);
$staff_name = $_SESSION['staff_name'] ?? "Staff";

$from_qtn = isset($_GET['from_qtn']) ? intval($_GET['from_qtn']) : 0;
$stage = isset($_GET['stage']) ? $_GET['stage'] : '';        // deposit / progress / final
$percent = isset($_GET['percent']) ? intval($_GET['percent']) : 100; // 50 / 30 / 20

$prefill_customer_id = 0;
$prefill_items = [];
$prefill_customer_name = '';
$original_total = 0;
$stage_percent = 100;

if ($from_qtn > 0) {
    $qtn_query = "SELECT q.*, c.name FROM quotations q LEFT JOIN customers c ON q.customer_id = c.customer_id WHERE q.qtn_id = ?";
    $stmt = mysqli_prepare($conn, $qtn_query);
    mysqli_stmt_bind_param($stmt, "i", $from_qtn);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $qtn = mysqli_fetch_assoc($res);
    if ($qtn && $qtn['status'] == 'Accepted') {
        $prefill_customer_id = $qtn['customer_id'];
        $prefill_customer_name = $qtn['name'];
        $original_total = $qtn['total_amount'];

        if ($stage && in_array($percent, [50,30,20])) {
            $stage_percent = $percent;
        } else {
            $stage_percent = 100;
        }

        $items_query = "SELECT description, quantity, unit_price, discount FROM quotation_items WHERE qtn_id = ?";
        $stmt_items = mysqli_prepare($conn, $items_query);
        mysqli_stmt_bind_param($stmt_items, "i", $from_qtn);
        mysqli_stmt_execute($stmt_items);
        $items_res = mysqli_stmt_get_result($stmt_items);
        while ($row = mysqli_fetch_assoc($items_res)) {
            $row['unit_price'] = round($row['unit_price'] * $stage_percent / 100, 2);
            $row['discount'] = round($row['discount'] * $stage_percent / 100, 2);
            $prefill_items[] = $row;
        }
    }
}

$cust_res = mysqli_query($conn, "SELECT customer_id, name FROM customers WHERE status = 1 ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Invoice | YS Aluminium</title>
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
        .invoice-container {
            max-width: 1000px;
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
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .btn-back:hover {
            background: #fafafa;
            border-color: #d4d4d8;
            color: #18181b;
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
            color: #1c1c1e;
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
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid #e4e4e7;
            padding: 10px 15px;
            font-size: 0.95rem;
            background: white;
        }
        .btn-primary-custom {
            background: #1c1c1e;
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
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
            font-size: 1rem;
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
        .stage-badge {
            background-color: #eef2ff;
            color: #1e40af;
            border-radius: 30px;
            padding: 4px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <div class="page-header">
        <a href="staff_dashboard.php?active_section=invoice" class="btn-back">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <h3><i class="bi bi-receipt me-2" style="color:#27272a;"></i>Create New Invoice
            <?php if ($stage && $percent != 100): ?>
                <span class="stage-badge"><?php echo $percent; ?>% Stage Payment</span>
            <?php endif; ?>
        </h3>
    </div>

    <form id="invoiceForm">
        <?php if ($from_qtn): ?>
        <input type="hidden" name="qtn_id" value="<?php echo $from_qtn; ?>">
        <?php endif; ?>
        <?php if ($stage): ?>
        <input type="hidden" name="stage" value="<?php echo htmlspecialchars($stage); ?>">
        <input type="hidden" name="stage_percent" value="<?php echo $percent; ?>">
        <?php endif; ?>
        
        <div class="card-custom">
            <div class="card-header-custom">
                <i class="bi bi-person-badge me-2"></i> Customer & Invoice Details
            </div>
            <div class="card-body-custom">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Select Customer</label>
                        <select name="customer_id" class="form-select select2-customer" required>
                            <option value="">-- Choose Customer --</option>
                            <?php while($c = mysqli_fetch_assoc($cust_res)): ?>
                                <option value="<?php echo $c['customer_id']; ?>" <?php echo ($c['customer_id'] == $prefill_customer_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name'] . ' (CUST-' . $c['customer_id'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date<span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul me-2"></i> Items</span>
                <button type="button" class="btn-primary-custom" id="addItemBtn">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
            </div>
            <div class="card-body-custom">
                <div id="itemsContainer"></div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                <span class="text-muted small text-uppercase fw-semibold">Total Amount</span>
                <div class="total-amount">RM <span id="grandTotal">0.00</span></div>
                <?php if ($stage && $percent != 100 && $original_total > 0): ?>
                <div class="small text-muted mt-1">(Original total: RM <?php echo number_format($original_total, 2); ?> &times; <?php echo $percent; ?>%)</div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn-success-custom" id="submitBtn">
                <i class="bi bi-file-pdf"></i> Generate Invoice PDF
            </button>
        </div>
        <div id="resultMessage" class="alert-custom mt-4" style="display:none;"></div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-customer').select2({
            placeholder: '-- Choose Customer --',
            allowClear: true,
            width: '100%'
        });
    });

    const prefilledItems = <?php echo json_encode($prefill_items); ?>;
    let itemIndex = prefilledItems.length;

    function createItemRow(index, data = null) {
        const div = document.createElement('div');
        div.classList.add('item-row');
        const desc = data ? data.description.replace(/"/g, '&quot;') : '';
        const qty = data ? parseInt(data.quantity) : 1;
        const price = data ? parseFloat(data.unit_price).toFixed(2) : '';
        const disc = data ? parseFloat(data.discount).toFixed(2) : '0.00';
        div.innerHTML = `
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Description<span class="text-danger">*</span></label>
                    <textarea name="items[${index}][description]" class="form-control" rows="2" placeholder="Description" required>${desc}</textarea>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity<span class="text-danger">*</span></label>
                    <input type="number" step="1" name="items[${index}][quantity]" class="form-control qty" value="${qty}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit Price (RM)<span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control price" value="${price}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Discount</label>
                    <input type="number" step="0.01" name="items[${index}][discount]" class="form-control disc" value="${disc}">
                </div>
                <div class="col-md-1 d-flex align-items-center">
                    <button type="button" class="btn-outline-danger-custom remove-item"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
        return div;
    }

    function renderExistingItems() {
        const container = document.getElementById('itemsContainer');
        container.innerHTML = '';
        if (prefilledItems.length === 0) {
            addNewRow();
        } else {
            prefilledItems.forEach((item, idx) => {
                container.appendChild(createItemRow(idx, item));
            });
        }
        bindRemoveButtons();
        calculateTotal();
    }

    function addNewRow() {
        const container = document.getElementById('itemsContainer');
        container.appendChild(createItemRow(itemIndex));
        itemIndex++;
        bindRemoveButtons();
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
    renderExistingItems();

    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = document.getElementById('submitBtn');
        const resultDiv = document.getElementById('resultMessage');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
        resultDiv.style.display = 'none';

        fetch('generate_invoice_pdf.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.className = 'alert alert-success alert-custom';
                resultDiv.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Invoice created successfully! Redirecting...';
                resultDiv.style.display = 'block';
                setTimeout(() => {
                    window.location.href = 'staff_dashboard.php?active_section=invoice';
                }, 1500);
            } else {
                resultDiv.className = 'alert alert-danger alert-custom';
                resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Error: ' + (data.message || 'Unknown error');
                resultDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-file-pdf me-2"></i>Generate Invoice PDF';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.className = 'alert alert-danger alert-custom';
            resultDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Server error. Please try again.';
            resultDiv.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-file-pdf me-2"></i>Generate Invoice PDF';
        });
    });
</script>
</body>
</html>