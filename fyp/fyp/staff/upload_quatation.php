<?php
session_start();
require '../includes/db.php'; 

if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (isset($_POST['upload_btn'])) {
    $customer_id  = intval($_POST['customer_id']);
    $staff_db_id  = $_SESSION['staff_id']; 
    $total_amount = floatval($_POST['total_amount']);
    
    $qtn_number = "QTN-" . date("Ymd") . "-" . rand(100, 999);

    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['pdf_file'];
        $file_name = $file['name'];
        $file_tmp  = $file['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext !== 'pdf') {
            $message = "<div class='alert alert-danger'>error: Only PDF files are allowed.</div>";
        } else {
            $upload_dir = "../uploads/quotations/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = $qtn_number . ".pdf";
            $dest_path    = $upload_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $dest_path)) {
                $db_path = "uploads/quotations/" . $new_filename;

                $sql = "INSERT INTO quotations (customer_id, staff_id, qtn_number, file_path, total_amount, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'Pending', NOW())";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "iissd", $customer_id, $staff_db_id, $qtn_number, $db_path, $total_amount);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "<div class='alert alert-success'>Quotation uploaded successfully! Quotation Number: $qtn_number</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Database error: " . mysqli_error($conn) . "</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Failed to move the uploaded file. Please check folder permissions.</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Please select a valid PDF file.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>YS Aluminium | Upload Quotation/title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f4f5; font-family: 'Inter', sans-serif; }
        .upload-container { max-width: 500px; margin: 80px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn-dark-custom { background-color: #1c1c1e; color: white; border: none; }
        .btn-dark-custom:hover { background-color: #27272a; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="upload-container">
        <h4 class="fw-bold mb-4 text-center">Upload New Quotation</h4>
        
        <?php echo $message; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label small fw-bold">Select Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- Please select a customer --</option>
                    <?php 
                    $cust_list = mysqli_query($conn, "SELECT customer_id, name FROM customers ORDER BY name ASC");
                    while($c = mysqli_fetch_assoc($cust_list)) {
                        echo "<option value='".$c['customer_id']."'>".$c['name']." (ID: ".$c['customer_id'].")</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Total Amount (RM)</label>
                <input type="number" step="0.01" name="total_amount" class="form-control" placeholder="0.00" required>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold">Quotation File (PDF only)</label>
                <input type="file" name="pdf_file" class="form-control" accept=".pdf" required>
            </div>

            <button type="submit" name="upload_btn" class="btn btn-dark-custom w-100 py-2 fw-bold">
                Confirm Upload Quotation
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="staff_dashboard.php" class="text-muted small text-decoration-none">← Return to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>