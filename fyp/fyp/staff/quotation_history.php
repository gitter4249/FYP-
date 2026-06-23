<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit;
}

$staff_id = intval($_SESSION['staff_id']);
$qtn_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$qtn_id) {
    die("Invalid quotation ID.");
}

$qtn_query = "SELECT qtn_number, customer_id FROM quotations WHERE qtn_id = $qtn_id";
$qtn_res = mysqli_query($conn, $qtn_query);
$qtn = mysqli_fetch_assoc($qtn_res);
if (!$qtn) {
    die("Quotation not found.");
}
$qtn_number = htmlspecialchars($qtn['qtn_number']);
$customer_id = $qtn['customer_id'];

$cust_query = "SELECT name, email, phone FROM customers WHERE customer_id = $customer_id";
$cust_res = mysqli_query($conn, $cust_query);
$customer = mysqli_fetch_assoc($cust_res);
$customer_name = htmlspecialchars($customer['name'] ?? 'Unknown');

$sql = "SELECT h.*, s.staff_name, s.profile_image, s.email as staff_email 
        FROM quotation_history h
        LEFT JOIN staff s ON h.staff_id = s.id
        WHERE h.qtn_id = $qtn_id
        ORDER BY h.created_at ASC";
$result = mysqli_query($conn, $sql);
$history = [];
while ($row = mysqli_fetch_assoc($result)) {
    $history[] = $row;
}

function getStaffAvatar($profile_image, $name) {
    if (!empty($profile_image)) {
        $avatar = $profile_image;
        if (strpos($avatar, '/') === 0) {
            $avatar = '../' . ltrim($avatar, '/');
        } elseif (strpos($avatar, '../') !== 0 && strpos($avatar, 'http') !== 0) {
            $avatar = '../' . $avatar;
        }
        if (file_exists($avatar)) {
            return $avatar;
        }
    }
    $initials = strtoupper(substr($name, 0, 2));
    return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=0d6efd&color=fff&size=40&rounded=true&bold=true";
}

$page_title = "History for Quotation #{$qtn_number} - {$customer_name}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .container-custom {
            max-width: 1400px;
            margin: 0 auto;
        }
        .card-custom {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .card-header-custom {
            background: white;
            padding: 20px 24px;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-back {
            background: #f0f2f5;
            border: none;
            padding: 8px 16px;
            border-radius: 40px;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #e2e8f0;
            text-decoration: none;
        }
        .table-history {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-history th {
            background: #f8fafc;
            padding: 14px 16px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-history td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #edf2f7;
        }
        .avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .badge-status {
            background: #e2e8f0;
            color: #1e293b;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .badge-arrow {
            font-size: 0.8rem;
            color: #94a3b8;
            margin: 0 6px;
        }
        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            padding: 5px 12px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.8rem;
            color: #0f172a;
            transition: 0.2s;
        }
        .file-link:hover {
            background: #e2e8f0;
            color: #0d6efd;
        }
        .action-badge {
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 30px;
            padding: 4px 12px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
        }
        @media (max-width: 768px) {
            .table-history th, .table-history td {
                padding: 12px 8px;
                font-size: 0.75rem;
            }
            .avatar-sm {
                width: 32px;
                height: 32px;
            }
        }
    </style>
</head>
<body>
<div class="container-custom">
    <div class="card-custom">
        <div class="card-header-custom">
            <div>
                <a href="staff_dashboard.php?active_section=quotation" class="btn-back">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <h4 class="mt-3 mb-1 fw-bold">History for Quotation <code><?php echo $qtn_number; ?></code></h4>
                <p class="text-muted mb-0">Customer: <strong><?php echo $customer_name; ?></strong></p>
            </div>
        </div>
        <div class="p-0">
            <div class="table-responsive">
                <table class="table-history">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Staff</th>
                            <th>Action</th>
                            <th>Status Change</th>
                            <th>Rejection Reason</th>
                            <th>Document</th>
                            <th>Amount (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No history records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($history as $rec): 
                                $staff_name = htmlspecialchars($rec['staff_name'] ?? 'System');
                                $staff_avatar = getStaffAvatar($rec['profile_image'] ?? '', $staff_name);
                                $action = $rec['action'];
                                $action_label = '';
                                if ($action == 'created') $action_label = 'Created';
                                elseif ($action == 'updated') $action_label = 'Updated';
                                elseif ($action == 'status_change') $action_label = 'Status Change';
                                elseif ($action == 'pdf_regenerated') $action_label = 'PDF Regenerated';
                                else $action_label = ucfirst($action);

                                $old_status = $rec['old_status'] ?: '-';
                                $new_status = $rec['new_status'] ?: '-';
                                $status_change_html = '';
                                if ($old_status != '-' && $new_status != '-') {
                                    $status_change_html = "<span class='badge-status'>{$old_status}</span> <i class='bi bi-arrow-right-short badge-arrow'></i> <span class='badge-status'>{$new_status}</span>";
                                } elseif ($new_status != '-') {
                                    $status_change_html = "<span class='badge-status'>{$new_status}</span>";
                                } else {
                                    $status_change_html = '—';
                                }

                                $reject_reason = !empty($rec['rejection_reason']) ? htmlspecialchars($rec['rejection_reason']) : '—';
                                $file_path = $rec['file_path'];
                                $pdf_link = '';
                                if (!empty($file_path) && file_exists("../" . $file_path)) {
                                    $pdf_link = "<a href='../{$file_path}' target='_blank' class='file-link'><i class='bi bi-file-pdf-fill'></i> View PDF</a>";
                                } else {
                                    $pdf_link = '<span class="text-muted small">No file</span>';
                                }

                                $amount = number_format(floatval($rec['total_amount'] ?? 0), 2);
                                $datetime = date('Y-m-d H:i:s', strtotime($rec['created_at']));
                            ?>
                            <tr>
                                <td class="text-nowrap"><small><?php echo $datetime; ?></small></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?php echo $staff_avatar; ?>" class="avatar-sm" onerror="this.src='https://ui-avatars.com/api/?name=U&background=0d6efd&color=fff&size=40&rounded=true'">
                                        <span class="fw-semibold"><?php echo $staff_name; ?></span>
                                    </div>
                                </td>
                                <td><span class="action-badge"><?php echo $action_label; ?></span></td>
                                <td><?php echo $status_change_html; ?></td>
                                <td><small><?php echo $reject_reason; ?></small></td>
                                <td><?php echo $pdf_link; ?></td>
                                <td class="fw-semibold"><?php echo $amount; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>