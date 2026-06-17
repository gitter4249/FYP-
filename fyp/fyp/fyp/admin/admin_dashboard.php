<?php
session_start();
if (!isset($_SESSION['admin'])) { 
    header("Location: admin_login.php");
    exit;
}
include "../includes/db.php";

$admin_id = $_SESSION['admin_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['admin_avatar'])) {
    $target_dir = "../images/";
    $file_ext = strtolower(pathinfo($_FILES["admin_avatar"]["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($file_ext, $allowed_types)) {
        $new_filename = "admin_vatar_" . $admin_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["admin_avatar"]["tmp_name"], $target_file)) {
            $update_stmt = $conn->prepare("UPDATE admins SET profile_image = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_filename, $admin_id);
            $update_stmt->execute();

            header("Location: admin_dashboard.php?view=profile&upload=success");
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_admin_password'])) {
    header('Content-Type: application/json');

    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $pwd_query = $conn->query("SELECT password FROM admins WHERE id = $admin_id");

    if (!$pwd_query) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $pwd_row = $pwd_query->fetch_assoc();

    if (!password_verify($current, $pwd_row['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect!']);
        exit;
    }

    if ($new !== $confirm) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match!']);
        exit;
    }

    if (strlen($new) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters!']);
        exit;
    }

    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
    $update->bind_param("si", $hashed, $admin_id);

    if ($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_admin_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone     = mysqli_real_escape_string($conn, $_POST['phone']);
    $address   = mysqli_real_escape_string($conn, $_POST['address']);

    $stmt = $conn->prepare("UPDATE admins SET full_name = ?, phone_number = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $phone, $address, $admin_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?view=profile&update=success");
        exit;
    } else {
        echo "<script>alert('Update failed: " . $stmt->error . "'); window.history.back();</script>";
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'get_revenue_data') {
    header('Content-Type: application/json');

    $range = $_GET['range'] ?? 'monthly';
    $labels = [];
    $values = [];

    if ($range == 'weekly') {
        for ($i = 3; $i >= 0; $i--) {
            $start = date('Y-m-d', strtotime("-$i weeks"));
            $end = date('Y-m-d', strtotime("-$i weeks +6 days"));
            $labels[] = "Week " . (4 - $i) . " ($start - $end)";

            $sql = "
                SELECT COALESCE(SUM(
                    q.total_amount * CASE pr.stage
                        WHEN 'deposit' THEN 0.5
                        WHEN 'progress' THEN 0.3
                        WHEN 'final' THEN 0.2
                        ELSE 0
                    END
                ), 0) as total
                FROM payment_records pr
                JOIN quotations q ON pr.qtn_id = q.qtn_id
                WHERE pr.status = 'Verified'
                  AND DATE(pr.uploaded_at) BETWEEN '$start' AND '$end'
            ";

            $res = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($res);
            $values[] = $row['total'];
        }
    } elseif ($range == 'yearly') {
        for ($i = 4; $i >= 0; $i--) {
            $year = date('Y', strtotime("-$i years"));
            $labels[] = $year;

            $sql = "
                SELECT COALESCE(SUM(
                    q.total_amount * CASE pr.stage
                        WHEN 'deposit' THEN 0.5
                        WHEN 'progress' THEN 0.3
                        WHEN 'final' THEN 0.2
                        ELSE 0
                    END
                ), 0) as total
                FROM payment_records pr
                JOIN quotations q ON pr.qtn_id = q.qtn_id
                WHERE pr.status = 'Verified'
                  AND YEAR(pr.uploaded_at) = $year
            ";

            $res = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($res);
            $values[] = $row['total'];
        }
    } else {
        // monthly (default)
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $labels[] = date('M Y', strtotime("-$i months"));

            $sql = "
                SELECT COALESCE(SUM(
                    q.total_amount * CASE pr.stage
                        WHEN 'deposit' THEN 0.5
                        WHEN 'progress' THEN 0.3
                        WHEN 'final' THEN 0.2
                        ELSE 0
                    END
                ), 0) as total
                FROM payment_records pr
                JOIN quotations q ON pr.qtn_id = q.qtn_id
                WHERE pr.status = 'Verified'
                  AND DATE_FORMAT(pr.uploaded_at, '%Y-%m') = '$month'
            ";

            $res = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($res);
            $values[] = $row['total'];
        }
    }

    echo json_encode(['labels' => $labels, 'values' => $values, 'total' => array_sum($values)]);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'get_product_images') {
    header('Content-Type: application/json');

    $pid = intval($_GET['id']);
    $res = $conn->query("SELECT image_path FROM product_images WHERE product_id = $pid ORDER BY sort_order ASC");
    $imgs = [];

    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $imgs[] = $r['image_path'];
        }
    }

    echo json_encode(['images' => $imgs]);
    exit;
}

$admin_res = mysqli_query($conn, "SELECT * FROM admins WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_res);

$full_name = $admin_data['full_name'] ?? 'System Administrator';
$admin_email = $admin_data['email'] ?? '';
$admin_phone = $admin_data['phone_number'] ?? '';
$admin_address = $admin_data['address'] ?? '';
$admin_avatar = !empty($admin_data['profile_image']) ? $admin_data['profile_image'] : 'admin_default.png';
$admin_calendar = $admin_data['appointment_calendar'] ?? '';
$last_login = !empty($admin_data['last_login']) ? date("d M Y, H:i", strtotime($admin_data['last_login'])) : "Just Now";

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$view = $_GET['view'] ?? 'dashboard';

$product_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];

$all_products = [];
$prod_res = $conn->query("SELECT product_id, door_brand FROM products WHERE status = 1 ORDER BY door_brand ASC");

if ($prod_res) {
    while ($p = $prod_res->fetch_assoc()) {
        $all_products[] = $p;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_new_admin'])) {
    $admin_full  = $_POST['a_fullname'];
    $admin_email = $_POST['a_email'];
    $admin_phone = $_POST['a_phone'];
    $admin_addr  = $_POST['a_address'];
    $admin_pass  = $_POST['a_password'];
    $admin_confirm = $_POST['a_confirm_password'];

    if ($admin_pass !== $admin_confirm) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    $admin_pass_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
    $admin_cal = isset($_POST['a_calendar_url']) ? mysqli_real_escape_string($conn, $_POST['a_calendar_url']) : '';
    $admin_image = "admin_default.png";

    if (isset($_FILES['a_avatar']) && $_FILES['a_avatar']['error'] == 0 && $_FILES['a_avatar']['size'] > 0) {
        $file_ext = strtolower(pathinfo($_FILES["a_avatar"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_types)) {
            $admin_image = "admin_vatar_new_" . time() . "." . $file_ext;
            move_uploaded_file($_FILES["a_avatar"]["tmp_name"], "../images/" . $admin_image);
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO admins (full_name, email, phone_number, address, password, profile_image, appointment_calendar)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("sssssss", $admin_full, $admin_email, $admin_phone, $admin_addr, $admin_pass_hash, $admin_image, $admin_cal);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?view=admins&status=success");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_admin'])) {
    $edit_id   = intval($_POST['edit_admin_id']);
    $admin_full  = mysqli_real_escape_string($conn, $_POST['edit_fullname']);
    $admin_email = mysqli_real_escape_string($conn, $_POST['edit_email']);
    $admin_phone = mysqli_real_escape_string($conn, $_POST['edit_phone']);
    $admin_addr  = mysqli_real_escape_string($conn, $_POST['edit_address']);
    $admin_cal   = isset($_POST['edit_calendar_url']) ? mysqli_real_escape_string($conn, $_POST['edit_calendar_url']) : '';

    $pass_update = "";

    if (!empty($_POST['edit_password'])) {
        if ($_POST['edit_password'] !== $_POST['edit_confirm_password']) {
            echo "<script>alert('New passwords do not match!'); window.history.back();</script>";
            exit;
        }

        $new_pass = password_hash($_POST['edit_password'], PASSWORD_DEFAULT);
        $pass_update = ", password = '$new_pass'";
    }

    $avatar_update = "";

    if (isset($_FILES['edit_admin_avatar']) && $_FILES['edit_admin_avatar']['error'] == 0 && $_FILES['edit_admin_avatar']['size'] > 0) {
        $avatar_ext = strtolower(pathinfo($_FILES['edit_admin_avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($avatar_ext, $allowed)) {
            $upload_dir = "../images/";
            $avatar_name = "admin_avatar_" . $edit_id . "_" . time() . "." . $avatar_ext;

            if (move_uploaded_file($_FILES['edit_admin_avatar']['tmp_name'], $upload_dir . $avatar_name)) {
                $avatar_update = ", profile_image = '$avatar_name'";
            }
        }
    }

    $sql = "
        UPDATE admins
        SET full_name='$admin_full',
            email='$admin_email',
            phone_number='$admin_phone',
            address='$admin_addr',
            appointment_calendar='$admin_cal'
            $pass_update
            $avatar_update
        WHERE id=$edit_id
    ";

    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?view=admins");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($_GET['delete_admin'])) {
    $del_id = intval($_GET['delete_admin']);
    $conn->query("DELETE FROM admins WHERE id = $del_id AND id != '$admin_id'");

    header("Location: admin_dashboard.php?view=admins");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_door'])) {
    $brand    = mysqli_real_escape_string($conn, $_POST['door_brand']);
    $material = mysqli_real_escape_string($conn, $_POST['material']);
    $design   = mysqli_real_escape_string($conn, $_POST['design_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date     = $_POST['stock_date'];
    $price_per_sqft = floatval($_POST['price_per_sqft']);

    if ($price_per_sqft < 0) $price_per_sqft = 0;

    $stmt = $conn->prepare("
        INSERT INTO products (door_brand, material, design_type, description, stock_date, price_per_sqft, status)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");

    $stmt->bind_param("sssssd", $brand, $material, $design, $description, $date, $price_per_sqft);
    $stmt->execute();

    $new_product_id = $conn->insert_id;
    $main_image = null;

    if (isset($_FILES['door_images']) && !empty($_FILES['door_images']['name'][0])) {
        foreach ($_FILES['door_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['door_images']['error'][$key] == 0) {
                $ext = strtolower(pathinfo($_FILES['door_images']['name'][$key], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($ext, $allowed)) {
                    $img_name = "product_" . time() . "_$key." . $ext;

                    if (move_uploaded_file($tmp_name, "../images/" . $img_name)) {
                        $conn->query("INSERT INTO product_images (product_id, image_path, sort_order) VALUES ($new_product_id, '$img_name', $key)");

                        if ($main_image === null) {
                            $main_image = $img_name;
                            $conn->query("UPDATE products SET image = '$main_image' WHERE product_id = $new_product_id");
                        }
                    }
                }
            }
        }
    }

    header("Location: admin_dashboard.php?view=products");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_door'])) {
    $id       = $_POST['id'];
    $brand    = mysqli_real_escape_string($conn, $_POST['door_brand']);
    $material = mysqli_real_escape_string($conn, $_POST['material']);
    $design   = mysqli_real_escape_string($conn, $_POST['design_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date     = $_POST['stock_date'];
    $price_per_sqft = floatval($_POST['price_per_sqft']);

    if ($price_per_sqft < 0) $price_per_sqft = 0;

    $stmt = $conn->prepare("
        UPDATE products
        SET door_brand=?, material=?, design_type=?, description=?, stock_date=?, price_per_sqft=?
        WHERE product_id=?
    ");

    $stmt->bind_param("sssssdi", $brand, $material, $design, $description, $date, $price_per_sqft, $id);
    $stmt->execute();

    if (isset($_FILES['door_images']) && !empty($_FILES['door_images']['name'][0])) {
        $conn->query("DELETE FROM product_images WHERE product_id = $id");
        $main_image = null;

        foreach ($_FILES['door_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['door_images']['error'][$key] == 0) {
                $ext = strtolower(pathinfo($_FILES['door_images']['name'][$key], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($ext, $allowed)) {
                    $img_name = "product_" . time() . "_$key." . $ext;

                    if (move_uploaded_file($tmp_name, "../images/" . $img_name)) {
                        $conn->query("INSERT INTO product_images (product_id, image_path, sort_order) VALUES ($id, '$img_name', $key)");

                        if ($main_image === null) {
                            $main_image = $img_name;
                            $conn->query("UPDATE products SET image = '$main_image' WHERE product_id = $id");
                        }
                    }
                }
            }
        }
    }

    header("Location: admin_dashboard.php?view=products");
    exit;
}

if (isset($_GET['toggle_door'])) {
    $id = $_GET['toggle_door'];
    $new_status = $_GET['status'];

    $stmt = $conn->prepare("UPDATE products SET status = ? WHERE product_id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();

    header("Location: admin_dashboard.php?view=products");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reg_staff'])) {
    $s_name    = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $s_email   = mysqli_real_escape_string($conn, $_POST['staff_email']);
    $s_phone   = mysqli_real_escape_string($conn, $_POST['staff_phone']);
    $s_calendar_url = isset($_POST['staff_calendar_url']) ? mysqli_real_escape_string($conn, $_POST['staff_calendar_url']) : '';
    $s_pass = $_POST['password'];
    $s_confirm = $_POST['confirm_password'];

    if ($s_pass !== $s_confirm) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    if (strlen($s_pass) < 6) {
        echo "<script>alert('Password must be at least 6 characters!'); window.history.back();</script>";
        exit;
    }

    $s_pass_hash = password_hash($s_pass, PASSWORD_DEFAULT);

    $check = $conn->query("SELECT id FROM staff WHERE email = '$s_email'");

    if ($check && $check->num_rows > 0) {
        echo "<script>alert('Email already exists. Please use a different email address.'); window.history.back();</script>";
        exit;
    }

    $profile_image = '../images/default-avatar.png';

    if (isset($_FILES['staff_avatar']) && $_FILES['staff_avatar']['error'] == 0) {
        $avatar_ext = strtolower(pathinfo($_FILES['staff_avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($avatar_ext, $allowed)) {
            $upload_dir = "../uploads/staff_avatars/";

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $avatar_name = "staff_" . time() . "_" . rand(1000, 9999) . "." . $avatar_ext;
            $avatar_path = $upload_dir . $avatar_name;

            if (move_uploaded_file($_FILES['staff_avatar']['tmp_name'], $avatar_path)) {
                $profile_image = "uploads/staff_avatars/" . $avatar_name;
            }
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO staff (staff_name, email, phone, password, status, profile_image, appointment_calendar)
        VALUES (?, ?, ?, ?, 1, ?, ?)
    ");

    $stmt->bind_param("ssssss", $s_name, $s_email, $s_phone, $s_pass_hash, $profile_image, $s_calendar_url);
    $stmt->execute();

    header("Location: admin_dashboard.php?view=staff");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_staff'])) {
    $id      = $_POST['id'];
    $s_name  = mysqli_real_escape_string($conn, $_POST['staff_name']);
    $s_email = mysqli_real_escape_string($conn, $_POST['staff_email']);
    $s_phone = mysqli_real_escape_string($conn, $_POST['staff_phone']);
    $s_calendar_url = isset($_POST['staff_calendar_url']) ? mysqli_real_escape_string($conn, $_POST['staff_calendar_url']) : '';

    $check = $conn->query("SELECT id FROM staff WHERE email = '$s_email' AND id != $id");

    if ($check && $check->num_rows > 0) {
        echo "<script>alert('Email already exists. Please use a different email address.'); window.history.back();</script>";
        exit;
    }

    $avatar_update = "";
    $avatar_value = "";

    if (isset($_FILES['edit_staff_avatar']) && $_FILES['edit_staff_avatar']['error'] == 0) {
        $avatar_ext = strtolower(pathinfo($_FILES['edit_staff_avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($avatar_ext, $allowed)) {
            $upload_dir = "../uploads/staff_avatars/";

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $avatar_name = "staff_" . $id . "_" . time() . "." . $avatar_ext;
            $avatar_path = $upload_dir . $avatar_name;

            if (move_uploaded_file($_FILES['edit_staff_avatar']['tmp_name'], $avatar_path)) {
                $avatar_update = ", profile_image = ?";
                $avatar_value = "uploads/staff_avatars/" . $avatar_name;
            }
        }
    }

    if (!empty($_POST['password'])) {
        $s_pass = $_POST['password'];
        $s_confirm = $_POST['confirm_password'];

        if ($s_pass !== $s_confirm) {
            echo "<script>alert('New passwords do not match!'); window.history.back();</script>";
            exit;
        }

        $s_pass_hash = password_hash($s_pass, PASSWORD_DEFAULT);

        if ($avatar_update) {
            $stmt = $conn->prepare("
                UPDATE staff
                SET staff_name=?, email=?, phone=?, password=?, profile_image=?, appointment_calendar=?
                WHERE id=?
            ");

            $stmt->bind_param("ssssssi", $s_name, $s_email, $s_phone, $s_pass_hash, $avatar_value, $s_calendar_url, $id);
        } else {
            $stmt = $conn->prepare("
                UPDATE staff
                SET staff_name=?, email=?, phone=?, password=?, appointment_calendar=?
                WHERE id=?
            ");

            $stmt->bind_param("sssssi", $s_name, $s_email, $s_phone, $s_pass_hash, $s_calendar_url, $id);
        }
    } else {
        if ($avatar_update) {
            $stmt = $conn->prepare("
                UPDATE staff
                SET staff_name=?, email=?, phone=?, profile_image=?, appointment_calendar=?
                WHERE id=?
            ");

            $stmt->bind_param("sssssi", $s_name, $s_email, $s_phone, $avatar_value, $s_calendar_url, $id);
        } else {
            $stmt = $conn->prepare("
                UPDATE staff
                SET staff_name=?, email=?, phone=?, appointment_calendar=?
                WHERE id=?
            ");

            $stmt->bind_param("ssssi", $s_name, $s_email, $s_phone, $s_calendar_url, $id);
        }
    }

    $stmt->execute();

    header("Location: admin_dashboard.php?view=staff");
    exit;
}

if (isset($_GET['toggle_staff'])) {
    $id = $_GET['toggle_staff'];
    $new_status = $_GET['status'];

    $stmt = $conn->prepare("UPDATE staff SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();

    header("Location: admin_dashboard.php?view=staff");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    $c_name    = $_POST['name'];
    $c_email   = $_POST['email'];
    $c_phone   = $_POST['phone'];
    $c_address = $_POST['address'];
    $c_gender  = $_POST['gender'];
    $c_race    = $_POST['race'];
    $status    = 1;
    $c_pass = $_POST['password'];
    $c_confirm = $_POST['confirm_password'];

    if ($c_pass !== $c_confirm) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    if (strlen($c_pass) < 6) {
        echo "<script>alert('Password must be at least 6 characters!'); window.history.back();</script>";
        exit;
    }

    $check = $conn->query("SELECT customer_id FROM customers WHERE email = '$c_email'");

    if ($check && $check->num_rows > 0) {
        echo "<script>alert('Email already exists. Please use a different email address.'); window.history.back();</script>";
        exit;
    }

    $c_pass_hash = password_hash($c_pass, PASSWORD_DEFAULT);
    $c_image = "default.png";

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0 && $_FILES['profile_image']['size'] > 0) {
        $file_ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_types)) {
            $upload_dir = "../uploads/customer_avatars/";

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $filename = "cust_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $upload_dir . $filename)) {
                $c_image = "uploads/customer_avatars/" . $filename;
            }
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO customers (name, email, phone, address, gender, race, password, profile_image, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param("ssssssssi", $c_name, $c_email, $c_phone, $c_address, $c_gender, $c_race, $c_pass_hash, $c_image, $status);
    $stmt->execute();

    $new_cust_id = $stmt->insert_id;

    if (isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
        foreach ($_POST['selected_products'] as $pid) {
            $pid = intval($pid);
            $conn->query("INSERT INTO customer_selected_products (customer_id, product_id) VALUES ($new_cust_id, $pid)");
        }
    }

    header("Location: admin_dashboard.php?view=customers");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_customer'])) {
    $id       = $_POST['id'];
    $c_name   = $_POST['name'];
    $c_email  = $_POST['email'];
    $c_phone  = $_POST['phone'];
    $c_address = $_POST['address'];
    $c_gender  = $_POST['gender'];
    $c_race    = $_POST['race'];

    $check = $conn->query("SELECT customer_id FROM customers WHERE email = '$c_email' AND customer_id != $id");

    if ($check && $check->num_rows > 0) {
        echo "<script>alert('Email already exists. Please use a different email address.'); window.history.back();</script>";
        exit;
    }

    $pass_update = "";

    if (!empty($_POST['password'])) {
        $new_pass = $_POST['password'];
        $new_confirm = $_POST['confirm_password'];

        if ($new_pass !== $new_confirm) {
            echo "<script>alert('New passwords do not match!'); window.history.back();</script>";
            exit;
        }

        $new_pass_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $pass_update = ", password = '$new_pass_hash'";
    }

    $img_update = "";

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0 && $_FILES['profile_image']['size'] > 0) {
        $file_ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_types)) {
            $upload_dir = "../uploads/customer_avatars/";

            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $new_img_name = "cust_upd_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;

            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $upload_dir . $new_img_name)) {
                $img_update = ", profile_image = 'uploads/customer_avatars/$new_img_name'";
            }
        }
    }

    $sql = "
        UPDATE customers
        SET name = ?, email = ?, phone = ?, address = ?, gender = ?, race = ?
        $pass_update
        $img_update
        WHERE customer_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $c_name, $c_email, $c_phone, $c_address, $c_gender, $c_race, $id);
    $stmt->execute();

    // Handle Selected Products
    if (isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
        $conn->query("DELETE FROM customer_selected_products WHERE customer_id = $id");

        foreach ($_POST['selected_products'] as $pid) {
            $pid = intval($pid);
            $conn->query("INSERT INTO customer_selected_products (customer_id, product_id) VALUES ($id, $pid)");
        }
    } else {
        $conn->query("DELETE FROM customer_selected_products WHERE customer_id = $id");
    }

    header("Location: admin_dashboard.php?view=customers");
    exit;
}

if (isset($_GET['toggle_customer'])) {
    $id = $_GET['toggle_customer'];
    $new_status = $_GET['status'];

    $stmt = $conn->prepare("UPDATE customers SET status = ? WHERE customer_id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();

    header("Location: admin_dashboard.php?view=customers");
    exit;
}

if (isset($_GET['delete_id']) && isset($_GET['view'])) {
    $id = intval($_GET['delete_id']);
    $viewType = $_GET['view'];

    if ($viewType == "products") {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
    } elseif ($viewType == "staff") {
        $stmt = $conn->prepare("DELETE FROM staff WHERE id=?");
    } elseif ($viewType == "customers") {
        $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id=?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: admin_dashboard.php?view=" . $viewType);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_step') {
    header('Content-Type: application/json');

    $customer_id = intval($_POST['customer_id']);
    $staff_id    = intval($_POST['staff_id']);
    $step_order  = intval($_POST['step_order']);
    $status      = $_POST['status'];

    $stmt = $conn->prepare("UPDATE project_progress SET status = ? WHERE customer_id = ? AND staff_id = ? AND step_order = ?");
    $stmt->bind_param("siii", $status, $customer_id, $staff_id, $step_order);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }

    $stmt->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_progress') {
    header('Content-Type: application/json');

    $customer_id = intval($_POST['customer_id']);
    $staff_id    = intval($_POST['staff_id']);

    $stmt = $conn->prepare("DELETE FROM project_progress WHERE customer_id = ? AND staff_id = ?");
    $stmt->bind_param("ii", $customer_id, $staff_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }

    $stmt->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'mark_step_complete') {
    header('Content-Type: application/json');

    $cust_id = intval($_POST['customer_id']);
    $step_name = mysqli_real_escape_string($conn, $_POST['step_name']);
    $staff_id = intval($_POST['staff_id']);
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : '';

    $check = mysqli_query($conn, "
        SELECT id
        FROM project_progress
        WHERE customer_id = $cust_id
          AND staff_id = $staff_id
          AND progress_step = '$step_name'
    ");

    if (mysqli_num_rows($check) > 0) {
        $update = mysqli_query($conn, "
            UPDATE project_progress
            SET status = 'Completed',
                notes = CONCAT(IFNULL(notes, ''), IF(notes IS NOT NULL AND notes != '', CONCAT('\n', '$notes'), '$notes')),
                updated_at = NOW()
            WHERE customer_id = $cust_id
              AND staff_id = $staff_id
              AND progress_step = '$step_name'
        ");

        if ($update) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed: ' . mysqli_error($conn)]);
        }
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO project_progress (customer_id, staff_id, progress_step, status, notes, updated_at)
            VALUES ($cust_id, $staff_id, '$step_name', 'Completed', '$notes', NOW())
        ");

        if ($insert) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database insert failed: ' . mysqli_error($conn)]);
        }
    }

    exit;
}
$view = $_GET['view'] ?? 'dashboard';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | YS Aluminium</title>

    <!-- Scripts & Styles -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../css/mobile.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <img src="../images/ys.jpg" alt="Logo">
        <span>YS ALUMINIUM</span>
    </div>

    <nav class="nav-menu">
        <div class="nav-section-title">Administration</div>

        <a href="admin_dashboard.php?view=dashboard" class="<?= $view == 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="admin_dashboard.php?view=profile" class="<?= $view == 'profile' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i> Admin Profile
        </a>

        <a href="admin_dashboard.php?view=products" class="<?= $view == 'products' ? 'active' : '' ?>">
            <i class="bi bi-door-open"></i> Product inventory
        </a>

        <a href="admin_dashboard.php?view=customers" class="<?= $view == 'customers' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Customer Management
        </a>

        <a href="admin_dashboard.php?view=appointments" class="<?= $view == 'appointments' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check"></i> Appointment
        </a>

        <a href="admin_dashboard.php?view=quotations_invoices" class="<?= $view == 'quotations_invoices' ? 'active' : '' ?>">
            <i class="bi bi-file-text"></i> Quotation & Invoice
        </a>

        <a href="admin_dashboard.php?view=staff_progress" class="<?= $view == 'staff_progress' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-steps"></i> Staff Progress
        </a>

        <a href="admin_dashboard.php?view=staff" class="<?= $view == 'staff' ? 'active' : '' ?>">
            <i class="bi bi-person-badge"></i> Staff Management
        </a>

        <a href="admin_dashboard.php?view=admins" class="<?= $view == 'admins' ? 'active' : '' ?>">
            <i class="bi bi-shield-lock"></i> Admin Management
        </a>

        <a href="logout.php" class="logout-link">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </nav>
</div>

<div class="topbar">
    <div class="search-container" style="flex:1; display:flex; justify-content:center;">
        <?php if ($view != 'dashboard' && $view != 'profile' && $view != 'appointments'): ?>
            <div class="search-box">
                <input type="hidden" id="hiddenView" value="<?= $view ?>">

                <?php if (isset($_GET['staff_id'])): ?>
                    <input type="hidden" id="hiddenStaffId" value="<?= intval($_GET['staff_id']) ?>">
                <?php endif; ?>

                <?php if ($view == 'quotations_invoices' && isset($_GET['staff_filter'])): ?>
                    <input type="hidden" id="hiddenStaffFilter" value="<?= intval($_GET['staff_filter']) ?>">
                <?php endif; ?>

                <i class="bi bi-search"></i>
                <input type="text" name="search" id="globalSearchInput" placeholder="Search by name, email, phone, product, date..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
            </div>
        <?php else: ?>
            <div></div>
        <?php endif; ?>
    </div>

    <a href="?view=profile" class="admin-profile-top">
        <div class="text-end">
            <div class="fw-bold small"><?= htmlspecialchars($full_name) ?></div>
            <div class="text-muted" style="font-size: 0.7rem;">System Administrator</div>
        </div>

        <img src="../images/<?= $admin_avatar ?>" alt="Profile" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($full_name) ?>&background=3b82f6&color=fff&size=35&rounded=true'; this.onerror=null;">
    </a>
</div>

<div class="main-content">

    <?php if ($view == 'dashboard'): ?>
        <?php
            $ongoing_count = 0;
            $completed_count = 0;

            $cust_all = $conn->query("SELECT customer_id FROM customers WHERE status = 1");

            while ($c = $cust_all->fetch_assoc()) {
                $cid = $c['customer_id'];
                $check_completed = $conn->query("
                    SELECT id
                    FROM project_progress
                    WHERE customer_id = $cid
                      AND progress_step = '20% complete job'
                      AND status = 'Completed'
                ");

                if ($check_completed && $check_completed->num_rows > 0) {
                    $completed_count++;
                } else {
                    $ongoing_count++;
                }
            }

            $monthly_labels = [];
            $monthly_values = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                $monthly_labels[] = date('M Y', strtotime("-$i months"));

                $sql_monthly = "
                    SELECT COALESCE(SUM(
                        q.total_amount * CASE pr.stage
                            WHEN 'deposit' THEN 0.5
                            WHEN 'progress' THEN 0.3
                            WHEN 'final' THEN 0.2
                            ELSE 0
                        END
                    ), 0) as total
                    FROM payment_records pr
                    JOIN quotations q ON pr.qtn_id = q.qtn_id
                    WHERE pr.status = 'Verified'
                      AND DATE_FORMAT(pr.uploaded_at, '%Y-%m') = '$month'
                ";

                $monthly_res = mysqli_query($conn, $sql_monthly);
                $monthly_row = mysqli_fetch_assoc($monthly_res);
                $monthly_values[] = $monthly_row['total'];
            }

            $total_revenue = array_sum($monthly_values);

            $top_products = mysqli_query($conn, "
                SELECT product_id, door_brand, price_per_sqft, image
                FROM products
                WHERE status = 1
                ORDER BY price_per_sqft DESC
                LIMIT 5
            ");

            $recent_customers = mysqli_query($conn, "
                SELECT customer_id, name, email, created_at
                FROM customers
                ORDER BY created_at DESC
                LIMIT 5
            ");

            $recent_quotations = mysqli_query($conn, "
                SELECT q.qtn_number, q.total_amount, q.status, q.created_at, c.name as customer_name
                FROM quotations q
                LEFT JOIN customers c ON q.customer_id = c.customer_id
                ORDER BY q.created_at DESC
                LIMIT 5
            ");

            $today_date = date('Ymd');
            $staff_emails_dash = [];
            $staff_list_dash = $conn->query("SELECT email FROM staff WHERE status = 1 AND email IS NOT NULL AND email != ''");

            if ($staff_list_dash) {
                while ($se = $staff_list_dash->fetch_assoc()) {
                    $staff_emails_dash[] = urlencode($se['email']);
                }
            }

            $src_parts_dash = ["src=" . urlencode($admin_email)];

            foreach ($staff_emails_dash as $staff_email) {
                $src_parts_dash[] = "src=" . $staff_email;
            }

            $calendar_src_today = "https://calendar.google.com/calendar/embed?mode=DAY&date={$today_date}&ctz=Asia%2FKuala_Lumpur&" . implode("&", $src_parts_dash) . "&hl=en";
        ?>

        <div class="row g-4 mb-4">
            <div class="col-md-3 d-flex">
                <div class="stats-card w-100 h-100 d-flex flex-column justify-content-between" style="border-left-color: #0d6efd; cursor: pointer;" onclick="window.location.href='?view=customers'">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1">Ongoing Projects</h6>
                        <h2 class="fw-bold mb-0"><?= $ongoing_count ?></h2>
                        <small class="text-muted">Not yet settled</small>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 text-primary opacity-50 align-self-end"></i>
                </div>
            </div>

            <div class="col-md-3 d-flex">
                <div class="stats-card w-100 h-100 d-flex flex-column justify-content-between" style="border-left-color: #198754; cursor: pointer;" onclick="window.location.href='?view=customers'">
                    <div>
                        <h6 class="text-muted text-uppercase fw-semibold mb-1">Completed Projects</h6>
                        <h2 class="fw-bold mb-0"><?= $completed_count ?></h2>
                        <small class="text-muted">Settled</small>
                    </div>
                    <i class="bi bi-check2-circle fs-1 text-success opacity-50 align-self-end"></i>
                </div>
            </div>

            <div class="col-md-6 d-flex">
                <div class="card-custom w-100 h-100 d-flex flex-column m-0">
                    <div class="card-header-custom py-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Schedule</h6>
                        <a href="?view=appointments" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-box-arrow-up-right"></i> Open Calendar
                        </a>
                    </div>

                    <div class="card-body p-2 flex-grow-1 d-flex flex-column">
                        <?php if (!empty($admin_email)): ?>
                            <iframe src="<?= $calendar_src_today ?>" style="border: 0; width: 100%; height: 123px;" frameborder="0" scrolling="yes"></iframe>
                        <?php else: ?>
                            <div class="text-center text-muted py-4 flex-grow-1 d-flex flex-column justify-content-center">
                                <i class="bi bi-calendar-x fs-1"></i>
                                <p class="mt-2">Admin email not configured.<br>Unable to display calendar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card-custom-chart">
                    <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Revenue Trend</h5>

                        <div class="d-flex align-items-center gap-3">
                            <div class="fw-bold fs-6 text-dark" id="totalRevenueDisplay">Total: RM <?= number_format($total_revenue, 2) ?></div>

                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary revenue-range" data-range="weekly">Weekly</button>
                                <button type="button" class="btn btn-outline-secondary revenue-range active" data-range="monthly">Monthly</button>
                                <button type="button" class="btn btn-outline-secondary revenue-range" data-range="yearly">Yearly</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3" style="height: 380px; position: relative; min-height: 380px;">
                        <canvas id="revenueChart" style="width: 100%; height: 100%; display: block;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Premium Products (by Price)</h5>
                        <a href="?view=products" class="btn btn-sm btn-outline-secondary">View All</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Price per sqft (RM)</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $rank = 1; while ($p = mysqli_fetch_assoc($top_products)): ?>
                                    <tr>
                                        <td><span class="badge bg-dark rounded-pill">#<?= $rank++ ?></span></td>

                                        <td>
                                            <?php if (!empty($p['image'])): ?>
                                                <img src="../images/<?= $p['image'] ?>" class="product-thumb">
                                            <?php else: ?>
                                                <i class="bi bi-image fs-3 text-muted"></i>
                                            <?php endif; ?>
                                        </td>

                                        <td class="fw-bold"><?= htmlspecialchars($p['door_brand']) ?></td>
                                        <td class="fw-bold">RM <?= number_format($p['price_per_sqft'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>

                                <?php if (mysqli_num_rows($top_products) == 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No products available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <h6 class="mb-0"><i class="bi bi-people-fill me-2"></i>Recent Customers</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($rc = mysqli_fetch_assoc($recent_customers)): ?>
                                    <tr>
                                        <td><code>CUST-<?= str_pad($rc['customer_id'], 4, '0', STR_PAD_LEFT) ?></code></td>
                                        <td><?= htmlspecialchars($rc['name']) ?></td>
                                        <td><?= htmlspecialchars($rc['email']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($rc['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>

                                <?php if (mysqli_num_rows($recent_customers) == 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No customers yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-12">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Recent Quotations</h5>
                        <a href="?view=quotations_invoices" class="btn btn-sm btn-outline-secondary">View All</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Qtn Number</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($q = mysqli_fetch_assoc($recent_quotations)):
                                    $status_class = match($q['status']) {
                                        'Accepted' => 'status-active',
                                        'Rejected' => 'status-inactive',
                                        default => 'status-pending'
                                    };
                                ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($q['qtn_number']) ?></code></td>
                                        <td><?= htmlspecialchars($q['customer_name'] ?? 'Unknown') ?></td>
                                        <td>RM <?= number_format($q['total_amount'], 2) ?></td>
                                        <td><span class="status-badge <?= $status_class ?>"><?= $q['status'] ?></span></td>
                                        <td><small><?= date('d M Y', strtotime($q['created_at'])) ?></small></td>
                                    </tr>
                                <?php endwhile; ?>

                                <?php if (mysqli_num_rows($recent_quotations) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No quotations found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let revenueChart;

            function loadRevenueData(range) {
                const canvas = document.getElementById('revenueChart');

                if (!canvas) {
                    console.warn("Canvas element not found");
                    return;
                }

                const container = canvas.parentElement;

                if (container && container.clientHeight === 0) {
                    console.warn("Canvas parent height is 0, forcing min-height");
                    container.style.minHeight = "380px";
                    container.style.height = "380px";
                }

                fetch(`?action=get_revenue_data&range=${range}`)
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP error ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        if (!data.labels || !data.values) {
                            throw new Error('Invalid data format');
                        }

                        const ctx = canvas.getContext('2d');

                        if (revenueChart) revenueChart.destroy();

                        canvas.width = container.clientWidth;
                        canvas.height = container.clientHeight;

                        revenueChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: 'Revenue (RM)',
                                    data: data.values,
                                    borderColor: '#0d6efd',
                                    backgroundColor: 'rgba(13,110,253,0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.3,
                                    pointBackgroundColor: '#0d6efd',
                                    pointBorderColor: '#fff',
                                    pointRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => `RM ${ctx.raw.toFixed(2)}`
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: { display: true, text: 'Amount (RM)' }
                                    }
                                }
                            }
                        });

                        document.getElementById('totalRevenueDisplay').innerHTML = `Total: RM ${data.total.toFixed(2)}`;
                    })
                    .catch(err => {
                        console.error("Revenue data error:", err);

                        const container = canvas.parentElement;
                        container.innerHTML = '<div class="alert alert-warning text-center m-3">Unable to load revenue data. Please check database connection or verify payment records exist.</div>';
                    });
            }
            document.querySelectorAll('.revenue-range').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.revenue-range').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    loadRevenueData(this.dataset.range);
                });
            });

            window.addEventListener('load', () => {
                setTimeout(() => {
                    loadRevenueData('monthly');
                }, 100);

                let resizeTimer;

                window.addEventListener('resize', function() {
                    if (resizeTimer) clearTimeout(resizeTimer);

                    resizeTimer = setTimeout(() => {
                        const activeRange = document.querySelector('.revenue-range.active')?.dataset.range || 'monthly';
                        loadRevenueData(activeRange);
                    }, 200);
                });
            });

            if (typeof Chart === 'undefined') {
                console.error("Chart.js library not loaded");
                document.getElementById('revenueChart').parentElement.innerHTML = '<div class="alert alert-danger">Chart.js library failed to load. Please check your internet connection.</div>';
            }
        </script>

    <?php elseif ($view == 'profile'): ?>
        <?php
            $default_avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=f0f0f0&color=666666&size=150&bold=true";
            $avatar_file = "../images/" . $admin_avatar;

            if (!empty($admin_avatar) && file_exists($avatar_file) && $admin_avatar != 'admin_default.png') {
                $avatar_src = $avatar_file;
            } else {
                $avatar_src = $default_avatar_url;
            }
        ?>

        <div class="card-custom">
            <div class="profile-cover"></div>

            <div style="display: flex; align-items: flex-end; gap: 25px; padding: 0 30px 20px 30px;">
                <div class="profile-avatar">
                    <img id="adminAvatar" src="<?= htmlspecialchars($avatar_src) ?>" alt="Avatar" onerror="this.onerror=null; this.src='<?= $default_avatar_url ?>';">

                    <div class="camera-icon" onclick="document.getElementById('adminAvatarInput').click()">
                        <i class="bi bi-camera"></i>
                    </div>

                    <input type="file" id="adminAvatarInput" style="display:none" accept="image/*" onchange="uploadAdminAvatar(this)">
                </div>

                <div class="profile-info">
                    <h2 id="profileCardName"><?= htmlspecialchars($full_name) ?></h2>
                    <span class="badge"><i class="bi bi-shield-check"></i> System Administrator</span>
                </div>
            </div>

            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i> Account Details</h5>
            </div>

            <div class="p-4">
                <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                    <div class="form-group">
                        <label>Admin ID</label>
                        <input type="text" class="form-control" value="ADMIN-<?= str_pad($admin_id, 3, '0', STR_PAD_LEFT) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($admin_email) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($full_name) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($admin_phone) ?>">
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label>Office / Home Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($admin_address) ?></textarea>
                    </div>

                    <div style="grid-column: span 2; display: flex; justify-content: space-between;">
                        <button type="button" class="btn-outline-secondary" onclick="openChangePwdModal()">
                            <i class="bi bi-key me-2"></i>Change Password
                        </button>

                        <button type="submit" name="update_admin_profile" class="btn-dark-custom">Save Information</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="changePwdModal" class="modal-overlay">
            <div class="modal-box">
                <h3>Change Password</h3>

                <div class="mb-3">
                    <label>Current Password</label>
                    <input type="password" id="modal_current_pwd" class="form-control">
                </div>

                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" id="modal_new_pwd" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" id="modal_confirm_pwd" class="form-control">
                </div>

                <div id="modalPwdMessage" class="mb-3" style="display:none;"></div>

                <div style="text-align:right">
                    <button class="btn-action" onclick="closeChangePwdModal()">Cancel</button>
                    <button class="btn btn-dark" id="submitChangePwdBtn">Update Password</button>
                </div>
            </div>
        </div>

        <script>
            function uploadAdminAvatar(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        document.getElementById('adminAvatar').src = e.target.result;
                    };

                    reader.readAsDataURL(file);

                    const fd = new FormData();
                    fd.append('admin_avatar', file);

                    fetch('', { method: 'POST', body: fd })
                        .then(() => {
                            alert('Avatar updated!');
                            location.reload();
                        })
                        .catch(err => alert('Upload failed'));
                }
            }

            function openChangePwdModal() {
                document.getElementById('modal_current_pwd').value = '';
                document.getElementById('modal_new_pwd').value = '';
                document.getElementById('modal_confirm_pwd').value = '';
                document.getElementById('modalPwdMessage').style.display = 'none';
                document.getElementById('changePwdModal').style.display = 'flex';
            }

            function closeChangePwdModal() {
                document.getElementById('changePwdModal').style.display = 'none';
            }

            document.getElementById('submitChangePwdBtn').addEventListener('click', function() {
                const cur = document.getElementById('modal_current_pwd').value;
                const newp = document.getElementById('modal_new_pwd').value;
                const conf = document.getElementById('modal_confirm_pwd').value;
                const msgDiv = document.getElementById('modalPwdMessage');

                if (!cur || !newp || !conf) {
                    msgDiv.innerHTML = '<div class="alert alert-danger">All fields required.</div>';
                    msgDiv.style.display = 'block';
                    return;
                }

                if (newp !== conf) {
                    msgDiv.innerHTML = '<div class="alert alert-danger">Passwords do not match.</div>';
                    msgDiv.style.display = 'block';
                    return;
                }

                if (newp.length < 6) {
                    msgDiv.innerHTML = '<div class="alert alert-danger">Password must be at least 6 characters.</div>';
                    msgDiv.style.display = 'block';
                    return;
                }

                const fd = new FormData();
                fd.append('change_admin_password', 1);
                fd.append('current_password', cur);
                fd.append('new_password', newp);
                fd.append('confirm_password', conf);

                fetch('', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            closeChangePwdModal();
                            location.reload();
                        } else {
                            msgDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                            msgDiv.style.display = 'block';
                        }
                    })
                    .catch(err => {
                        msgDiv.innerHTML = '<div class="alert alert-danger">Server error.</div>';
                        msgDiv.style.display = 'block';
                    });
            });
        </script>

    <?php elseif ($view == 'products'): ?>
        <div class="card-custom" id="productsContainer">
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold">Product Inventory (<?= $product_total ?>)</h5>
                <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addDoorModal">
                    <i class="bi bi-plus-lg me-1"></i> Add New Product
                </button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle" id="productsTable">
                    <thead>
                        <tr>
                            <th>Images</th>
                            <th>Brand / Model</th>
                            <th>Material</th>
                            <th>Design Type</th>
                            <th>Entry Date</th>
                            <th>Price per sqft (RM)</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            $res = $conn->query($search ?
                                "SELECT * FROM products WHERE door_brand LIKE '%$search%' OR material LIKE '%$search%' ORDER BY product_id DESC" :
                                "SELECT * FROM products ORDER BY product_id DESC"
                            );

                            while ($d = $res->fetch_assoc()):
                                $isActive = ($d['status'] == 1);

                                $img_query = "SELECT image_path FROM product_images WHERE product_id = {$d['product_id']} ORDER BY sort_order ASC";
                                $img_res = $conn->query($img_query);
                                $images = [];

                                while ($img_row = $img_res->fetch_assoc()) {
                                    $images[] = $img_row['image_path'];
                                }

                                if (empty($images) && !empty($d['image'])) {
                                    $images[] = $d['image'];
                                }
                        ?>
                            <tr data-product-id="<?= $d['product_id'] ?>">
                                <td data-searchable="images">
                                    <div class="product-images-stack">
                                        <?php
                                            $dc = 0;

                                            foreach ($images as $idx => $img):
                                                if ($dc < 2):
                                        ?>
                                                    <img src="../images/<?= $img ?>" class="product-img-thumb" style="width:50px;height:50px;object-fit:cover;" class="img-thumbnail" onclick="showImagePreview('../images/<?= $img ?>', '<?= htmlspecialchars($d['door_brand']) ?>')">
                                                <?php
                                                    $dc++;
                                                else:
                                                    break;
                                                endif;
                                            endforeach;

                                            if (count($images) > 2) echo '<span class="more-badge">+' . (count($images) - 2) . '</span>';

                                            if (empty($images)) echo '<i class="bi bi-image fs-3 text-muted"></i>';
                                        ?>
                                    </div>
                                </td>

                                <td data-searchable="brand"><?= htmlspecialchars($d['door_brand']) ?></td>
                                <td data-searchable="material"><?= htmlspecialchars($d['material']) ?></td>
                                <td data-searchable="design"><?= htmlspecialchars($d['design_type']) ?></td>
                                <td data-searchable="date"><?= date("d M Y", strtotime($d['stock_date'])) ?></td>
                                <td data-searchable="price">RM <?= number_format($d['price_per_sqft'] ?? 75, 2) ?></td>

                                <td>
                                    <a href="?toggle_door=<?= $d['product_id'] ?>&status=<?= $isActive ? 0 : 1 ?>" class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                        <?= $isActive ? 'Active' : 'Inactive' ?>
                                    </a>
                                </td>

                                <td class="text-end">
                                    <div class="d-flex justify-content-end">
                                        <button class="btn-action" onclick="editDoor(<?= $d['product_id'] ?>,'<?= htmlspecialchars($d['door_brand']) ?>','<?= htmlspecialchars($d['material']) ?>','<?= htmlspecialchars($d['design_type']) ?>','<?= addslashes(htmlspecialchars($d['description'] ?? '')) ?>','<?= $d['price_per_sqft'] ?? 75 ?>','<?= $d['stock_date'] ?>')" data-bs-toggle="modal" data-bs-target="#editDoorModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <a href="admin_dashboard.php?view=products&delete_id=<?= $d['product_id'] ?>" class="btn-action text-danger" onclick="return confirm('Delete?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade image-preview-modal" id="imagePreviewModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="previewTitle">Product Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body text-center">
                        <img id="previewImage" src="" alt="Preview" style="max-width:100%; max-height:80vh;">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addDoorModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="POST" enctype="multipart/form-data" class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">Add New Door Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Brand / Model Name</label>
                                <input type="text" name="door_brand" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label>Material</label>
                                <select name="material" class="form-select" required>
                                    <option value="">Select...</option>
                                    <option value="Aluminum">Aluminum</option>
                                    <option value="Glass">Glass</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Design Type</label>
                                <select name="design_type" class="form-select" required>
                                    <option value="">Select...</option>
                                    <option value="Sliding Door">Sliding Door</option>
                                    <option value="Swing Door">Swing Door</option>
                                    <option value="Folding Door">Folding Door</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Price per sqft (RM)</label>
                                <input type="number" step="0.01" name="price_per_sqft" class="form-control" value="75.00" min="0" required>
                            </div>

                            <div class="col-12">
                                <label>Product Details</label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label>Stock Entry Date</label>
                                <input type="date" name="stock_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-12">
                                <label>Product Images (You can select multiple images)</label>
                                <input type="file" name="door_images[]" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Hold Ctrl to select multiple images at once.</small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_door" class="btn btn-dark">Save Product</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="editDoorModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="POST" enctype="multipart/form-data" class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">Edit Door Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <input type="hidden" name="id" id="edit_door_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Brand / Model Name</label>
                                <input type="text" name="door_brand" id="edit_door_brand" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label>Material</label>
                                <select name="material" id="edit_door_material" class="form-select" required>
                                    <option value="Aluminum">Aluminum</option>
                                    <option value="Glass">Glass</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Design Type</label>
                                <select name="design_type" id="edit_door_design" class="form-select" required>
                                    <option value="Sliding Door">Sliding Door</option>
                                    <option value="Swing Door">Swing Door</option>
                                    <option value="Folding Door">Folding Door</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Price per sqft (RM)</label>
                                <input type="number" step="0.01" name="price_per_sqft" id="edit_door_price" class="form-control" min="0" required>
                            </div>

                            <div class="col-md-6">
                                <label>Stock Entry Date</label>
                                <input type="date" name="stock_date" id="edit_door_date" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label>Product Details</label>
                                <textarea name="description" id="edit_door_description" class="form-control" rows="4"></textarea>
                            </div>

                            <div class="col-12" id="editDoorImagesContainer"></div>

                            <div class="col-12">
                                <label>New Product Images (Optional)</label>
                                <input type="file" name="door_images[]" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Upload new images to replace all existing images. Hold Ctrl to select multiple.</small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_door" class="btn btn-dark">Update Product</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function showImagePreview(src, title) {
                document.getElementById('previewImage').src = src;
                document.getElementById('previewTitle').innerText = title || 'Product Image';
                new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
            }

            function editDoor(id, brand, material, design, description, price, date) {
                document.querySelector('#editDoorModal [name="id"]').value = id;
                document.querySelector('#editDoorModal [name="door_brand"]').value = brand;
                document.querySelector('#editDoorModal [name="material"]').value = material;
                document.querySelector('#editDoorModal [name="design_type"]').value = design;
                document.querySelector('#editDoorModal [name="description"]').value = description;
                document.querySelector('#editDoorModal [name="price_per_sqft"]').value = price;
                document.querySelector('#editDoorModal [name="stock_date"]').value = date;

                fetch(`?action=get_product_images&id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        let container = document.getElementById('editDoorImagesContainer');

                        if (data.images && data.images.length) {
                            let html = '<label class="form-label fw-bold">Current Images</label><div class="d-flex flex-wrap gap-2">';

                            data.images.forEach(img => {
                                html += `<img src="../images/${img}" width="80" style="border-radius:8px; border:1px solid #ddd;">`;
                            });

                            html += '</div>';
                            container.innerHTML = html;
                        } else {
                            container.innerHTML = '<span class="text-muted">No current images.</span>';
                        }
                    });
            }
        </script>

    <?php elseif ($view == 'staff'): ?>
        <div class="card-custom" id="staffContainer">
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold">Staff Directory</h5>
                <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#regStaffModal">
                    <i class="bi bi-person-plus me-1"></i> Add Staff
                </button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle" id="staffTable">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Contact</th>
                            <th>Appointment Calendar</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            $res = $conn->query($search ?
                                "SELECT * FROM staff WHERE staff_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%' ORDER BY id DESC" :
                                "SELECT * FROM staff ORDER BY id DESC"
                            );

                            if (!$res) {
                                echo '<tr><td colspan="7" class="text-center text-danger">DB error</td></tr>';
                            } else {
                                while ($s = $res->fetch_assoc()):
                                    $isActive = ($s['status'] == 1);

                                    $avatar_path = !empty($s['profile_image']) ? $s['profile_image'] : '../images/default-avatar.png';

                                    if (strpos($avatar_path, '../') !== 0 && strpos($avatar_path, 'http') !== 0) {
                                        $avatar_path = '../' . $avatar_path;
                                    }

                                    if (!file_exists($avatar_path)) $avatar_path = '../images/default-avatar.png';
                        ?>
                                    <tr data-staff-id="<?= $s['id'] ?>">
                                        <td>
                                            <img src="<?= $avatar_path ?>" class="staff-avatar-table" onerror="this.src='../images/default-avatar.png'">
                                        </td>

                                        <td>
                                            <code>#<?= $s['id'] ?></code>
                                        </td>

                                        <td data-searchable="name">
                                            <?= htmlspecialchars($s['staff_name']) ?>
                                        </td>

                                        <td data-searchable="email">
                                            <div class="small text-muted mb-1">
                                                <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($s['email'] ?? 'N/A') ?>
                                            </div>

                                            <div class="small text-muted">
                                                <i class="bi bi-telephone me-2"></i><?= htmlspecialchars($s['phone'] ?? 'N/A') ?>
                                            </div>
                                        </td>

                                        <td>
                                            <?php if (!empty($s['appointment_calendar'])): ?>
                                                <a href="<?= htmlspecialchars($s['appointment_calendar']) ?>" target="_blank" class="btn-action btn-sm">
                                                    <i class="bi bi-calendar-check"></i> View Link
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <a href="?view=staff&toggle_staff=<?= $s['id'] ?>&status=<?= $isActive ? 0 : 1 ?>" class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                                                <?= $isActive ? 'Active' : 'Inactive' ?>
                                            </a>
                                        </td>

                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <button class="btn-action edit-staff-btn"
                                                        data-id="<?= $s['id'] ?>"
                                                        data-name="<?= htmlspecialchars($s['staff_name'], ENT_QUOTES) ?>"
                                                        data-email="<?= htmlspecialchars($s['email'] ?? '', ENT_QUOTES) ?>"
                                                        data-phone="<?= htmlspecialchars($s['phone'] ?? '', ENT_QUOTES) ?>"
                                                        data-calendar="<?= htmlspecialchars($s['appointment_calendar'] ?? '', ENT_QUOTES) ?>"
                                                        data-avatar="<?= $avatar_path ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editStaffModal">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <a href="admin_dashboard.php?view=staff&delete_id=<?= $s['id'] ?>" class="btn-action text-danger" onclick="return confirm('Delete this staff?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                        <?php
                                endwhile;
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Register Staff Modal -->
        <div class="modal fade" id="regStaffModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Register New Staff</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <form action="" method="POST" enctype="multipart/form-data" id="regStaffForm" onsubmit="return validateStaffPasswords()">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Full Name</label>
                                    <input type="text" name="staff_name" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="staff_email" class="form-control" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Phone</label>
                                    <input type="text" name="staff_phone" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Password</label>
                                    <input type="password" name="password" id="staff_password" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Confirm Password</label>
                                    <input type="password" name="confirm_password" id="staff_confirm_password" class="form-control" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label>Google Calendar Link</label>
                                    <input type="url" name="staff_calendar_url" class="form-control" placeholder="https://calendar.app.google/xxxxxx">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label>Profile Image</label>
                                    <input type="file" name="staff_avatar" class="form-control" accept="image/*">
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" name="reg_staff" class="btn btn-dark w-100">Create Account</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editStaffModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Staff Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <form action="" method="POST" enctype="multipart/form-data" id="editStaffForm" onsubmit="return validateEditStaffPasswords()">
                            <input type="hidden" name="id" id="edit_staff_id">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Full Name</label>
                                    <input type="text" name="staff_name" id="edit_staff_name" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="staff_email" id="edit_staff_email" class="form-control" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Phone</label>
                                    <input type="text" name="staff_phone" id="edit_staff_phone" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Reset Password (Optional)</label>
                                    <input type="password" name="password" id="edit_staff_password" class="form-control" placeholder="Leave blank to keep">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="edit_staff_confirm_password" class="form-control" placeholder="Leave blank">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label>Google Calendar Link</label>
                                    <input type="url" name="staff_calendar_url" id="edit_staff_calendar" class="form-control">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Profile Image</label>
                                    <div class="mb-2">
                                        <img id="edit_staff_avatar_preview" src="../images/default-avatar.png" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
                                    </div>

                                    <input type="file" name="edit_staff_avatar" class="form-control" accept="image/*" onchange="previewEditStaffAvatar(this)">
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" name="edit_staff" class="btn btn-dark w-100">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($view == 'admins'): ?>
        <div class="card-custom" id="adminsContainer">
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold">Admin Management</h5>
                <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="bi bi-person-plus me-1"></i> Add Admin
                </button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle" id="adminsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Avatar</th>
                            <th>Full Name</th>
                            <th>Contact Info</th>
                            <th>Appointment Calendar</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            $res = $conn->query($search ?
                                "SELECT * FROM admins WHERE full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone_number LIKE '%$search%' ORDER BY id DESC" :
                                "SELECT * FROM admins ORDER BY id DESC"
                            );

                            while ($a = $res->fetch_assoc()):
                                $admin_avatar_img = !empty($a['profile_image']) ? "../images/" . $a['profile_image'] : "../images/admin_default.png";
                                $avatar_fallback_url = "https://ui-avatars.com/api/?name=" . urlencode($a['full_name']) . "&background=3b82f6&color=fff&size=40&rounded=true";
                        ?>
                                <tr data-admin-id="<?= $a['id'] ?>">
                                    <td><code>#<?= $a['id'] ?></code></td>

                                    <td>
                                        <img src="<?= $admin_avatar_img ?>" width="40" height="40" style="border-radius:50%;object-fit:cover;" onerror="this.src='<?= $avatar_fallback_url ?>';">
                                    </td>

                                    <td data-searchable="name"><?= htmlspecialchars($a['full_name']) ?></td>

                                    <td data-searchable="email">
                                        <div class="small">
                                            <i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($a['email'] ?? '—') ?>
                                        </div>

                                        <div class="small text-muted">
                                            <i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($a['phone_number'] ?? '—') ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?php if (!empty($a['appointment_calendar'])): ?>
                                            <a href="<?= htmlspecialchars($a['appointment_calendar']) ?>" target="_blank" class="btn-action btn-sm">
                                                <i class="bi bi-calendar-check"></i> View Link
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn-action edit-admin-btn"
                                                    data-id="<?= $a['id'] ?>"
                                                    data-fullname="<?= htmlspecialchars($a['full_name'], ENT_QUOTES) ?>"
                                                    data-email="<?= htmlspecialchars($a['email'] ?? '', ENT_QUOTES) ?>"
                                                    data-phone="<?= htmlspecialchars($a['phone_number'] ?? '', ENT_QUOTES) ?>"
                                                    data-address="<?= htmlspecialchars($a['address'] ?? '', ENT_QUOTES) ?>"
                                                    data-calendar="<?= htmlspecialchars($a['appointment_calendar'] ?? '', ENT_QUOTES) ?>"
                                                    data-avatar="<?= $admin_avatar_img ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editAdminModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <?php if ($a['id'] != $admin_id): ?>
                                                <a href="admin_dashboard.php?view=admins&delete_admin=<?= $a['id'] ?>" class="btn-action text-danger" onclick="return confirm('Delete this admin?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="addAdminModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateAdminPasswords()">
                        <div class="modal-header">
                            <h5 class="modal-title">Create System Admin</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row">
                                <div class="col-md-12 mb-4 text-center">
                                    <label>Profile Photo</label>
                                    <input type="file" name="a_avatar" class="form-control w-50 mx-auto" accept="image/*">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Full Name</label>
                                    <input type="text" name="a_fullname" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="a_email" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Phone</label>
                                    <input type="text" name="a_phone" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Password</label>
                                    <input type="password" name="a_password" id="a_password" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Confirm Password</label>
                                    <input type="password" name="a_confirm_password" id="a_confirm_password" class="form-control" required>
                                </div>

                                <div class="col-12 mb-3">
                                    <label>Address</label>
                                    <textarea name="a_address" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label>Google Calendar Link</label>
                                    <input type="url" name="a_calendar_url" class="form-control" placeholder="https://calendar.app.google/xxxxxx">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" name="create_new_admin" class="btn btn-dark">Save Admin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editAdminModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Admin Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateEditAdminPasswords()">
                            <input type="hidden" name="edit_admin_id" id="edit_admin_id">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Full Name</label>
                                    <input type="text" name="edit_fullname" id="edit_admin_fullname" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="edit_email" id="edit_admin_email" class="form-control" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Phone</label>
                                    <input type="text" name="edit_phone" id="edit_admin_phone" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Address</label>
                                <textarea name="edit_address" id="edit_admin_address" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label>Google Calendar Link</label>
                                <input type="url" name="edit_calendar_url" id="edit_admin_calendar" class="form-control" placeholder="https://calendar.app.google/xxxxxx">
                            </div>

                            <div class="mb-3">
                                <label>New Password (Optional)</label>
                                <input type="password" name="edit_password" id="edit_admin_password" class="form-control" placeholder="Leave blank to keep">
                            </div>

                            <div class="mb-3">
                                <label>Confirm New Password</label>
                                <input type="password" name="edit_confirm_password" id="edit_admin_confirm_password" class="form-control" placeholder="Leave blank">
                            </div>

                            <div class="mb-3">
                                <label>Profile Image</label>
                                <input type="file" name="edit_admin_avatar" class="form-control" accept="image/*">
                            </div>

                            <div class="modal-footer">
                                <button type="submit" name="edit_admin" class="btn btn-dark w-100">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($view == 'appointments'): ?>
        <?php
            $staff_emails = [];
            $staff_list = $conn->query("SELECT email FROM staff WHERE status = 1 AND email IS NOT NULL AND email != ''");

            if ($staff_list) {
                while ($se = $staff_list->fetch_assoc()) {
                    $staff_emails[] = urlencode($se['email']);
                }
            }

            $src_parts = ["src=" . urlencode($admin_email)];

            foreach ($staff_emails as $staff_email) {
                $src_parts[] = "src=" . $staff_email;
            }

            $calendar_src = "https://calendar.google.com/calendar/embed?" . implode("&", $src_parts) . "&ctz=Asia%2FKuala_Lumpur" . "&hl=en";
        ?>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-calendar-check me-2"></i>Appointment (Admin)</h4>
                        <p class="text-muted mb-0">Manage your appointments.</p>
                    </div>

                    <a href="https://calendar.google.com/calendar/u/0/r/settings/schedules" target="_blank" class="btn btn-dark btn-sm">
                        <i class="bi bi-pencil-square"></i> Edit in Google Calendar
                    </a>
                </div>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-google me-2"></i>My Appointment Scheduler</h5>
            </div>

            <div class="card-body p-0">
                <?php if (!empty($admin_calendar)): ?>
                    <iframe src="<?= htmlspecialchars($admin_calendar) ?>?gv=true" style="border:0; width:100%; height:700px;" frameborder="0" scrolling="yes"></iframe>
                <?php else: ?>
                    <div class="alert alert-warning m-3">No appointment scheduler link configured.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card-custom">
                    <div class="card-header-custom">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2"></i>Team Calendar View</h5>
                    </div>

                    <div class="card-body p-0">
                        <iframe src="<?= $calendar_src ?>" style="border:0; width:100%; height:600px;" frameborder="0" scrolling="no"></iframe>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($view == 'quotations_invoices'): ?>
        <?php
            $staff_pending_counts = [];
            $all_staff = $conn->query("SELECT id, staff_name FROM staff WHERE status = 1");

            if ($all_staff) {
                while ($st = $all_staff->fetch_assoc()) {
                    $sid = $st['id'];

                    $pending_sql = "
                        SELECT COUNT(DISTINCT c.customer_id) as cnt
                        FROM customers c
                        WHERE c.status = 1
                          AND EXISTS (
                              SELECT 1 FROM quotations q WHERE q.customer_id = c.customer_id AND q.staff_id = $sid
                              UNION
                              SELECT 1 FROM invoices i WHERE i.customer_id = c.customer_id AND i.staff_id = $sid
                          )
                          AND NOT EXISTS (
                              SELECT 1 FROM project_progress p
                              WHERE p.customer_id = c.customer_id
                                AND p.staff_id = $sid
                                AND p.progress_step = '20% complete job'
                                AND p.status = 'Completed'
                          )
                    ";

                    $res = $conn->query($pending_sql);
                    $cnt = $res ? (int)$res->fetch_assoc()['cnt'] : 0;

                    $staff_pending_counts[$sid] = ['name' => $st['staff_name'], 'pending' => $cnt];
                }
            }

            uasort($staff_pending_counts, function($a, $b) {
                return $b['pending'] - $a['pending'];
            });

            $selected_staff = isset($_GET['staff_filter']) ? intval($_GET['staff_filter']) : 0;

            $sql = "
                SELECT DISTINCT
                    c.customer_id,
                    c.name,
                    c.email,
                    c.phone,
                    c.profile_image,
                    COALESCE(
                        (SELECT staff_id FROM quotations WHERE customer_id = c.customer_id ORDER BY created_at DESC LIMIT 1),
                        (SELECT staff_id FROM invoices WHERE customer_id = c.customer_id ORDER BY created_at DESC LIMIT 1),
                        0
                    ) AS staff_id,
                    s.staff_name
                FROM customers c
                LEFT JOIN staff s ON s.id = COALESCE(
                        (SELECT staff_id FROM quotations WHERE customer_id = c.customer_id ORDER BY created_at DESC LIMIT 1),
                        (SELECT staff_id FROM invoices WHERE customer_id = c.customer_id ORDER BY created_at DESC LIMIT 1),
                        0
                    )
                WHERE c.status = 1
                  AND (
                      EXISTS (SELECT 1 FROM quotations WHERE customer_id = c.customer_id)
                      OR EXISTS (SELECT 1 FROM invoices WHERE customer_id = c.customer_id)
                  )
            ";

            if ($selected_staff > 0) {
                $sql .= " AND COALESCE(
                            (SELECT staff_id FROM quotations WHERE customer_id = c.customer_id ORDER BY created_at DESC LIMIT 1),
                            (SELECT staff_id FROM invoices WHERE customer_id = c.customer_id ORDER BY created_at DESC LIMIT 1),
                            0
                        ) = $selected_staff";
            }

            $sql .= " ORDER BY c.name ASC";

            $cust_res = $conn->query($sql);

            if (!$cust_res) {
                echo "SQL Error: " . $conn->error;
                exit;
            }

            $customers = [];

            while ($row = $cust_res->fetch_assoc()) {
                $cid = $row['customer_id'];

                $prods = [];
                $p_res = $conn->query("SELECT p.door_brand FROM customer_selected_products csp JOIN products p ON csp.product_id = p.product_id WHERE csp.customer_id = $cid");

                while ($prow = $p_res->fetch_assoc()) {
                    $prods[] = $prow['door_brand'];
                }

                $row['selected_products'] = $prods;

                $comp_check = $conn->query("SELECT id FROM project_progress WHERE customer_id = $cid AND progress_step = '20% complete job' AND status = 'Completed' LIMIT 1");
                $row['is_completed'] = ($comp_check && $comp_check->num_rows > 0);

                $quotes = [];
                $q_res = $conn->query("SELECT qtn_number, file_path, total_amount, status, created_at FROM quotations WHERE customer_id = $cid ORDER BY created_at DESC");

                while ($q = $q_res->fetch_assoc()) {
                    $quotes[] = $q;
                }

                $row['quotations'] = $quotes;

                $invoices = [];
                $inv_res = $conn->query("SELECT invoice_number, final_amount, issue_date, due_date, file_path FROM invoices WHERE customer_id = $cid ORDER BY due_date ASC");

                while ($inv = $inv_res->fetch_assoc()) {
                    $invoices[] = $inv;
                }

                $row['invoices'] = $invoices;

                $customers[] = $row;
            }

            usort($customers, function($a, $b) {
                if ($a['is_completed'] != $b['is_completed']) {
                    return $a['is_completed'] ? 1 : -1;
                }

                return strcmp($a['name'], $b['name']);
            });
        ?>

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-1 gap-2">
            <h3 class="fw-bold mb-0"><i class="bi bi-file-text me-2"></i>Quotation & Invoice</h3>

            <div class="d-flex align-items-center gap-5 justify-content-center">
                <span class="fw-semibold">Filter by Staff:</span>

                <select id="staffFilterSelect" class="form-select w-auto" style="min-width: 280px; padding-right: 30px;">
                    <option value="0" <?= $selected_staff == 0 ? 'selected' : '' ?>>All Staff</option>

                    <?php foreach ($staff_pending_counts as $sid => $info): ?>
                        <option value="<?= $sid ?>" <?= $selected_staff == $sid ? 'selected' : '' ?>>
                            <?= htmlspecialchars($info['name']) ?> (<?= $info['pending'] ?> pending)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <script>
            document.getElementById('staffFilterSelect').addEventListener('change', function() {
                window.location.href = `?view=quotations_invoices&staff_filter=${this.value}`;
            });
        </script>

        <?php if (empty($customers)): ?>
            <div class="alert alert-info">No customers with quotations or invoices found.</div>
        <?php else: ?>
            <div class="card-custom" id="qiContainer">
                <div class="table-responsive">
                    <table class="table quote-invoice-table align-middle" id="qiTable" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th style="width: 20%">Customer</th>
                                <th style="width: 25%">Contact</th>
                                <th style="width: 10%">Staff</th>
                                <th style="width: 25%">Quotations</th>
                                <th style="width: 20%">Invoices</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($customers as $cust):
                                $avatar_img = $cust['profile_image'] ?? '';
                                $avatar_src = "../" . $avatar_img;

                                if (empty($avatar_img) || !file_exists($avatar_src)) {
                                    $avatar_src = "https://ui-avatars.com/api/?name=" . urlencode($cust['name']) . "&background=20304a&color=fff&size=40&rounded=true&bold=true";
                                }

                                $reason_text = '';

                                if (!$cust['is_completed']) {
                                    $pendingQuotes = count(array_filter($cust['quotations'], fn($q) => $q['status'] != 'Accepted'));
                                    $unpaidInvoices = count($cust['invoices']);

                                    if ($unpaidInvoices > 0 && $pendingQuotes > 0) {
                                        $reason_text = 'Unpaid invoices & pending quotations';
                                    } elseif ($unpaidInvoices > 0) {
                                        $reason_text = 'Unpaid invoice(s)';
                                    } elseif ($pendingQuotes > 0) {
                                        $reason_text = 'Pending quotation(s)';
                                    }
                                }
                            ?>
                                <tr data-customer-name="<?= htmlspecialchars($cust['name']) ?>"
                                    data-customer-email="<?= htmlspecialchars($cust['email']) ?>"
                                    data-customer-phone="<?= htmlspecialchars($cust['phone'] ?? '') ?>"
                                    data-products="<?= htmlspecialchars(implode(', ', $cust['selected_products'])) ?>">

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= $avatar_src ?>" width="40" height="40" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($cust['name']) ?>&background=20304a&color=fff&size=40&rounded=true&bold=true';">

                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($cust['name']) ?></div>

                                                <?php if (!$cust['is_completed']): ?>
                                                    <span class="badge bg-warning text-dark">Ongoing</span>

                                                    <?php if ($reason_text): ?>
                                                        <small class="text-muted ms-1">(<?= $reason_text ?>)</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($cust['email']) ?></div>
                                        <div><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($cust['phone'] ?? '—') ?></div>

                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-box me-1"></i> <?= empty($cust['selected_products']) ? 'None' : htmlspecialchars(implode(', ', $cust['selected_products'])) ?>
                                        </div>
                                    </td>

                                    <td><?= htmlspecialchars($cust['staff_name'] ?? 'Unassigned') ?></td>

                                    <td>
                                        <div class="doc-list-inline">
                                            <?php if (empty($cust['quotations'])): ?>
                                                <span class="text-muted">—</span>
                                            <?php else: foreach ($cust['quotations'] as $q):
                                                $pdf_exists = !empty($q['file_path']) && file_exists("../" . $q['file_path']);
                                            ?>
                                                <div class="doc-item-inline d-flex align-items-center justify-content-between gap-2 flex-nowrap py-1">
                                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                                        <code><?= htmlspecialchars($q['qtn_number']) ?></code>
                                                        <small class="text-nowrap">(RM <?= number_format($q['total_amount'], 2) ?>)</small>
                                                        <span class="badge <?= $q['status'] == 'Accepted' ? 'bg-success' : 'bg-secondary' ?> px-2 py-1" style="font-size: 0.7rem;"><?= $q['status'] ?></span>
                                                    </div>

                                                    <?php if ($pdf_exists): ?>
                                                        <a href="../<?= $q['file_path'] ?>" target="_blank" class="btn-action btn-sm text-nowrap">
                                                            <i class="bi bi-file-pdf"></i> PDF
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted text-nowrap">—</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; endif; ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="doc-list-inline">
                                            <?php if (empty($cust['invoices'])): ?>
                                                <span class="text-muted">—</span>
                                            <?php else: foreach ($cust['invoices'] as $inv):
                                                $pdf_exists = !empty($inv['file_path']) && file_exists("../" . $inv['file_path']);
                                            ?>
                                                <div class="doc-item-inline d-flex align-items-center justify-content-between gap-2 flex-nowrap py-1">
                                                    <div>
                                                        <code><?= htmlspecialchars($inv['invoice_number']) ?></code>
                                                        <div class="small text-muted text-nowrap">RM <?= number_format($inv['final_amount'], 2) ?> | Due: <?= $inv['due_date'] ?></div>
                                                    </div>

                                                    <?php if ($pdf_exists): ?>
                                                        <a href="../<?= $inv['file_path'] ?>" target="_blank" class="btn-action btn-sm text-nowrap">
                                                            <i class="bi bi-file-pdf"></i> PDF
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted text-nowrap">—</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($view == 'customers'): ?>
        <?php
            $step_list = ['Order', 'Deposit 50%', 'Fabrication', 'Installation', '30% on going job', '20% complete job'];
            $total_steps = count($step_list);

            $cust_query = "
                SELECT c.*,
                       (SELECT staff_id FROM project_progress WHERE customer_id = c.customer_id LIMIT 1) as staff_id
                FROM customers c
                WHERE c.status = 1
            ";

            $all_cust_res = $conn->query($cust_query);
            $customers_data = [];

            while ($c = $all_cust_res->fetch_assoc()) {
                $cust_id = $c['customer_id'];

                $c['selected_products'] = [];
                $p_res = $conn->query("SELECT p.door_brand FROM customer_selected_products csp JOIN products p ON csp.product_id = p.product_id WHERE csp.customer_id = $cust_id");

                if ($p_res) {
                    while ($prow = $p_res->fetch_assoc()) {
                        $c['selected_products'][] = $prow['door_brand'];
                    }
                }

                $sel_prod_ids = [];
                $sp_res = $conn->query("SELECT product_id FROM customer_selected_products WHERE customer_id = $cust_id");

                if ($sp_res) {
                    while ($sp = $sp_res->fetch_assoc()) {
                        $sel_prod_ids[] = $sp['product_id'];
                    }
                }

                $c['selected_products_json'] = htmlspecialchars(json_encode($sel_prod_ids), ENT_QUOTES, 'UTF-8');

                // Step status
                $completed_count = 0;
                $step_status = [];

                foreach ($step_list as $step) {
                    $check = $conn->query("SELECT id FROM project_progress WHERE customer_id = $cust_id AND progress_step = '$step' AND status = 'Completed'");

                    if ($check && $check->num_rows > 0) {
                        $completed_count++;
                        $step_status[$step] = true;
                    } else {
                        $step_status[$step] = false;
                    }
                }

                $is_completed = $step_status['20% complete job'] ?? false;

                $c['completed_steps'] = $completed_count;
                $c['is_completed'] = $is_completed;
                $c['step_status'] = $step_status;

                $customers_data[] = $c;
            }

            $ongoing_customers = array_filter($customers_data, fn($c) => !$c['is_completed']);
            $completed_customers = array_filter($customers_data, fn($c) => $c['is_completed']);

            if ($search) {
                $ongoing_customers = array_filter($ongoing_customers, function($c) use ($search) {
                    return stripos($c['name'], $search) !== false ||
                           stripos($c['email'] ?? '', $search) !== false ||
                           stripos($c['phone'] ?? '', $search) !== false;
                });

                $completed_customers = array_filter($completed_customers, function($c) use ($search) {
                    return stripos($c['name'], $search) !== false ||
                           stripos($c['email'] ?? '', $search) !== false ||
                           stripos($c['phone'] ?? '', $search) !== false;
                });
            }

            usort($ongoing_customers, function($a, $b) {
                return $b['completed_steps'] - $a['completed_steps'];
            });

            function highlightText2($text, $keyword) {
                if (!$keyword) return htmlspecialchars($text);

                return preg_replace(
                    '/(' . preg_quote($keyword, '/') . ')/i',
                    '<mark class="highlight">$1</mark>',
                    htmlspecialchars($text)
                );
            }
        ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0"><i class="bi bi-people-fill me-2"></i>Customer Management</h3>

            <button class="btn btn-dark px-4 py-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-person-plus-fill me-2"></i>Add Customer
            </button>
        </div>

        <div class="row g-4" id="customersContainer">
            <div class="col-12">
                <div class="card-custom h-100">
                    <div class="card-header-custom bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-hourglass-split me-2"></i>Ongoing Projects</h5>
                        <span class="badge bg-secondary"><?= count($ongoing_customers) ?> customers</span>
                    </div>

                    <div class="p-3" style="max-height: 60vh; overflow-y: auto;" id="ongoingCustomersList">
                        <?php if (empty($ongoing_customers)): ?>
                            <div class="text-center text-muted py-5">No ongoing projects.</div>
                        <?php else: foreach ($ongoing_customers as $c):
                            $avatar_img = $c['profile_image'] ?? '';
                            $avatar_src = "../" . $avatar_img;

                            if (empty($avatar_img) || !file_exists($avatar_src)) {
                                $avatar_src = "https://ui-avatars.com/api/?name=" . urlencode($c['name']) . "&background=20304a&color=fff&size=60&rounded=true&bold=true";
                            }

                            $staff_id = $c['staff_id'] ?? 0;
                            $detail_url = "admin_dashboard.php?view=staff_progress&staff_id=" . $staff_id;
                        ?>
                            <div class="card mb-3 border shadow-sm customer-card" onclick="window.location.href='<?= $detail_url ?>'" style="cursor: pointer;"
                                 data-customer-name="<?= htmlspecialchars($c['name']) ?>"
                                 data-customer-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                                 data-customer-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>"
                                 data-customer-address="<?= htmlspecialchars($c['address'] ?? '') ?>"
                                 data-customer-products="<?= htmlspecialchars(implode(', ', $c['selected_products'])) ?>">

                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $avatar_src ?>" width="50" height="50" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($c['name']) ?>&background=20304a&color=fff&size=60&rounded=true&bold=true';">

                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold"><?= highlightText2($c['name'], $search) ?></h6>
                                            <div class="small text-muted">ID: #<?= $c['customer_id'] ?></div>

                                            <div class="small mt-2">
                                                <i class="bi bi-envelope me-1"></i> <?= highlightText2($c['email'] ?? 'N/A', $search) ?>
                                            </div>

                                            <div class="small">
                                                <i class="bi bi-telephone me-1"></i> <?= highlightText2($c['phone'] ?? 'N/A', $search) ?>
                                            </div>

                                            <div class="small">
                                                <i class="bi bi-geo-alt me-1"></i> <?= highlightText2($c['address'] ?? 'No address', $search) ?>
                                            </div>

                                            <div class="small mt-1">
                                                <i class="bi bi-person me-1"></i> <?= htmlspecialchars($c['gender'] ?? '-') ?> / <?= htmlspecialchars($c['race'] ?? '-') ?>
                                            </div>

                                            <div class="small mt-1 text-primary">
                                                <i class="bi bi-box me-1"></i> <?= empty($c['selected_products']) ? 'None' : highlightText2(implode(', ', $c['selected_products']), $search) ?>
                                            </div>

                                            <div class="mt-2">
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= round(($c['completed_steps'] / $total_steps) * 100) ?>%;"></div>
                                                </div>

                                                <small class="text-muted"><?= $c['completed_steps'] ?>/<?= $total_steps ?> steps completed</small>
                                            </div>
                                        </div>

                                        <div onclick="event.stopPropagation();">
                                            <a href="?view=customers&toggle_customer=<?= $c['customer_id'] ?>&status=<?= $c['status'] == 1 ? 0 : 1 ?>" class="status-badge <?= $c['status'] == 1 ? 'status-active' : 'status-inactive' ?>">
                                                <?= $c['status'] == 1 ? 'Active' : 'Inactive' ?>
                                            </a>

                                            <button class="btn-action d-block mt-2" onclick="event.stopPropagation(); editCustomer(<?= $c['customer_id'] ?>, '<?= htmlspecialchars($c['name']) ?>', '<?= htmlspecialchars($c['email'] ?? '') ?>', '<?= htmlspecialchars($c['phone'] ?? '') ?>', '<?= htmlspecialchars($c['address'] ?? '') ?>', '<?= htmlspecialchars($c['gender'] ?? '') ?>', '<?= htmlspecialchars($c['race'] ?? '') ?>', '<?= $c['selected_products_json'] ?>')" data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card-custom h-100">
                    <div class="card-header-custom bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-check2-circle me-2"></i>Completed Projects</h5>
                        <span class="badge bg-success"><?= count($completed_customers) ?> customers</span>
                    </div>

                    <div class="p-3" style="max-height: 60vh; overflow-y: auto;" id="completedCustomersList">
                        <?php if (empty($completed_customers)): ?>
                            <div class="text-center text-muted py-5">No completed projects yet.</div>
                        <?php else: foreach ($completed_customers as $c):
                            $avatar_img = $c['profile_image'] ?? '';
                            $avatar_src = "../" . $avatar_img;

                            if (empty($avatar_img) || !file_exists($avatar_src)) {
                                $avatar_src = "https://ui-avatars.com/api/?name=" . urlencode($c['name']) . "&background=28a745&color=fff&size=60&rounded=true&bold=true";
                            }

                            $staff_id = $c['staff_id'] ?? 0;
                            $detail_url = "admin_dashboard.php?view=staff_progress&staff_id=" . $staff_id;
                        ?>
                            <div class="card mb-3 border shadow-sm customer-card" onclick="window.location.href='<?= $detail_url ?>'" style="cursor: pointer;"
                                 data-customer-name="<?= htmlspecialchars($c['name']) ?>"
                                 data-customer-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                                 data-customer-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>"
                                 data-customer-address="<?= htmlspecialchars($c['address'] ?? '') ?>"
                                 data-customer-products="<?= htmlspecialchars(implode(', ', $c['selected_products'])) ?>">

                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $avatar_src ?>" width="50" height="50" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($c['name']) ?>&background=28a745&color=fff&size=60&rounded=true&bold=true';">

                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold"><?= highlightText2($c['name'], $search) ?></h6>
                                            <div class="small text-muted">ID: #<?= $c['customer_id'] ?></div>

                                            <div class="small mt-2">
                                                <i class="bi bi-envelope me-1"></i> <?= highlightText2($c['email'] ?? 'N/A', $search) ?>
                                            </div>

                                            <div class="small">
                                                <i class="bi bi-telephone me-1"></i> <?= highlightText2($c['phone'] ?? 'N/A', $search) ?>
                                            </div>

                                            <div class="small">
                                                <i class="bi bi-geo-alt me-1"></i> <?= highlightText2($c['address'] ?? 'No address', $search) ?>
                                            </div>

                                            <div class="small mt-1">
                                                <i class="bi bi-person me-1"></i> <?= htmlspecialchars($c['gender'] ?? '-') ?> / <?= htmlspecialchars($c['race'] ?? '-') ?>
                                            </div>

                                            <div class="small mt-1 text-primary">
                                                <i class="bi bi-box me-1"></i> <?= empty($c['selected_products']) ? 'None' : highlightText2(implode(', ', $c['selected_products']), $search) ?>
                                            </div>

                                            <div class="mt-2">
                                                <span class="badge bg-success">Project Completed</span>
                                            </div>
                                        </div>

                                        <div onclick="event.stopPropagation();">
                                            <a href="?view=customers&toggle_customer=<?= $c['customer_id'] ?>&status=<?= $c['status'] == 1 ? 0 : 1 ?>" class="status-badge <?= $c['status'] == 1 ? 'status-active' : 'status-inactive' ?>">
                                                <?= $c['status'] == 1 ? 'Active' : 'Inactive' ?>
                                            </a>

                                            <button class="btn-action d-block mt-2" onclick="event.stopPropagation(); editCustomer(<?= $c['customer_id'] ?>, '<?= htmlspecialchars($c['name']) ?>', '<?= htmlspecialchars($c['email'] ?? '') ?>', '<?= htmlspecialchars($c['phone'] ?? '') ?>', '<?= htmlspecialchars($c['address'] ?? '') ?>', '<?= htmlspecialchars($c['gender'] ?? '') ?>', '<?= htmlspecialchars($c['race'] ?? '') ?>', '<?= $c['selected_products_json'] ?>')" data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form method="POST" enctype="multipart/form-data" class="modal-content" onsubmit="return validateCustomerPasswords()">
                <div class="modal-header bg-light"><h5 class="modal-title">Add New Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Customer Name</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Profile Image</label><input type="file" name="profile_image" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Password</label><input type="password" name="password" id="customer_password" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Confirm Password</label><input type="password" name="confirm_password" id="customer_confirm_password" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Gender</label><select name="gender" class="form-select" required><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                        <div class="col-md-6 mb-3"><label>Race</label><select name="race" class="form-select" required><option value="">Select</option><option value="Malay">Malay</option><option value="Chinese">Chinese</option><option value="Indian">Indian</option><option value="Other">Other</option></select></div>
                        <div class="col-12 mb-3"><label>Address</label><textarea name="address" class="form-control" rows="2" required></textarea></div>
                        <div class="col-12 mb-3">
                            <label>Interested Products</label>
                            <select name="selected_products[]" class="form-select select2-products" multiple="multiple" style="width: 100%;">
                                <?php foreach($all_products as $p): ?>
                                    <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['door_brand']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_customer" class="btn btn-dark w-100">Add Customer</button></div>
            </form>
        </div>
    </div>
    
    <div class="modal fade" id="editCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form method="POST" enctype="multipart/form-data" class="modal-content" onsubmit="return validateEditCustomerPasswords()">
                <div class="modal-header bg-light"><h5 class="modal-title">Edit Customer Profile</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_customer_id">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Customer Name</label><input type="text" name="name" id="edit_customer_name" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" id="edit_customer_email" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Phone</label><input type="text" name="phone" id="edit_customer_phone" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Profile Image</label><input type="file" name="profile_image" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>New Password</label><input type="password" name="password" id="edit_customer_password" class="form-control" placeholder="Leave blank to keep current"></div>
                        <div class="col-md-6 mb-3"><label>Confirm New Password</label><input type="password" name="confirm_password" id="edit_customer_confirm_password" class="form-control" placeholder="Leave blank"></div>
                        <div class="col-md-6 mb-3"><label>Gender</label><select name="gender" id="edit_customer_gender" class="form-select"><option value="Male">Male</option><option value="Female">Female</option></select></div>
                        <div class="col-md-6 mb-3"><label>Race</label><select name="race" id="edit_customer_race" class="form-select"><option value="Malay">Malay</option><option value="Chinese">Chinese</option><option value="Indian">Indian</option><option value="Other">Other</option></select></div>
                        <div class="col-12 mb-3"><label>Address</label><textarea name="address" id="edit_customer_address" class="form-control" rows="2"></textarea></div>
                        <div class="col-12 mb-3">
                            <label>Interested Products</label>
                            <select name="selected_products[]" id="edit_customer_products" class="form-select select2-products-edit" multiple="multiple" style="width: 100%;">
                                <?php foreach($all_products as $p): ?>
                                    <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['door_brand']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" name="edit_customer" class="btn btn-dark w-100">Save Changes</button></div>
            </form>
        </div>
    </div>

    <?php elseif ($view == 'staff_progress'): ?>
        <?php
            $selected_staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;
            $step_order = ['Order' => 0, 'Deposit 50%' => 1, 'Fabrication' => 2, 'Installation' => 3, '30% on going job' => 4, '20% complete job' => 5];
            $step_names = array_keys($step_order);
            $step_icons = ['bi-receipt', 'bi-cash-stack', 'bi-tools', 'bi-truck', 'bi-hourglass-split', 'bi-check2-circle'];

            $all_staff_with_projects = $conn->query("
                SELECT DISTINCT s.id, s.staff_name
                FROM staff s
                INNER JOIN project_progress p ON s.id = p.staff_id
                WHERE s.status = 1
                ORDER BY s.staff_name ASC
            ");

            if ($selected_staff_id == 0) {
                $staff_list = $conn->query("
                    SELECT DISTINCT s.id, s.staff_name, s.email, s.phone, s.profile_image,
                        (
                            SELECT COUNT(DISTINCT p2.customer_id)
                            FROM project_progress p2
                            WHERE p2.staff_id = s.id
                              AND EXISTS (SELECT 1 FROM customers c WHERE c.customer_id = p2.customer_id AND c.status = 1)
                        ) as total_projects,
                        (
                            SELECT COUNT(DISTINCT p2.customer_id)
                            FROM project_progress p2
                            WHERE p2.staff_id = s.id
                              AND p2.customer_id NOT IN (
                                  SELECT p3.customer_id FROM project_progress p3
                                  WHERE p3.staff_id = s.id AND p3.progress_step = '20% complete job' AND p3.status = 'Completed'
                              )
                              AND EXISTS (SELECT 1 FROM customers c WHERE c.customer_id = p2.customer_id AND c.status = 1)
                        ) as ongoing_projects
                    FROM staff s
                    INNER JOIN project_progress p ON s.id = p.staff_id
                    WHERE s.status = 1
                    GROUP BY s.id
                    ORDER BY ongoing_projects DESC, total_projects ASC
                ");

                if (!$staff_list || $staff_list->num_rows == 0) {
                    echo '<div class="alert alert-info">No staff with active projects found.</div>';
                } else {
                    echo '<div class="mb-4">';
                    echo '<h3 class="fw-bold mb-0"><i class="bi bi-bar-chart-steps me-2"></i>Staff Progress Overview</h3>';
                    echo '</div>';

                    echo '<div class="row g-4" id="staffProgressListContainer">';

                    while ($staff = $staff_list->fetch_assoc()) {
                        $avatar_path = !empty($staff['profile_image']) ? $staff['profile_image'] : '../images/default-avatar.png';

                        if (strpos($avatar_path, '../') !== 0 && strpos($avatar_path, 'http') !== 0) {
                            $avatar_path = '../' . $avatar_path;
                        }

                        if (!file_exists($avatar_path)) $avatar_path = '../images/default-avatar.png';

                        echo '<div class="col-md-4 col-lg-3 staff-card" data-staff-name="' . htmlspecialchars($staff['staff_name']) . '" data-staff-email="' . htmlspecialchars($staff['email'] ?? '') . '" data-staff-phone="' . htmlspecialchars($staff['phone'] ?? '') . '">';
                        echo '<div class="card staff-card h-100 shadow-sm" onclick="window.location.href=\'?view=staff_progress&staff_id=' . $staff['id'] . '\'" style="cursor: pointer;">';
                        echo '<div class="card-body text-center">';
                        echo '<img src="' . $avatar_path . '" class="rounded-circle mb-2" style="width:70px; height:70px; object-fit:cover;" onerror="this.src=\'../images/default-avatar.png\'">';
                        echo '<h5 class="mt-2">' . htmlspecialchars($staff['staff_name']) . '</h5>';
                        echo '<p class="text-muted small mb-1"><i class="bi bi-envelope"></i> ' . htmlspecialchars($staff['email'] ?? '—') . '</p>';
                        echo '<p class="text-muted small"><i class="bi bi-telephone"></i> ' . htmlspecialchars($staff['phone'] ?? '—') . '</p>';
                        echo '<hr><div class="d-flex justify-content-center gap-3 mt-2">';
                        echo '<span class="badge bg-primary">Ongoing: ' . $staff['ongoing_projects'] . '</span>';
                        echo '<span class="badge bg-secondary">Total: ' . $staff['total_projects'] . '</span>';
                        echo '</div></div></div></div>';
                    }

                    echo '</div>';
                }
            } else {
                $staff_info = $conn->query("SELECT staff_name FROM staff WHERE id = $selected_staff_id");

                if (!$staff_info || $staff_info->num_rows == 0) {
                    echo '<div class="alert alert-danger">Staff not found.</div>';
                } else {
                    $staff_name = htmlspecialchars($staff_info->fetch_assoc()['staff_name']);
                    $customer_search = $search;
                    $cust_where = "p.staff_id = $selected_staff_id AND c.status = 1";

                    if ($customer_search) {
                        $cust_where .= " AND (c.name LIKE '%$customer_search%' OR c.email LIKE '%$customer_search%')";
                    }

                    $customers_list = $conn->query("
                        SELECT DISTINCT c.customer_id, c.name, c.email, c.profile_image
                        FROM customers c
                        INNER JOIN project_progress p ON c.customer_id = p.customer_id
                        WHERE $cust_where
                        ORDER BY c.name ASC
                    ");
        ?>
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                        <a href="?view=staff_progress" class="btn btn-dark rounded-pill px-4 py-2">
                            <i class="bi bi-arrow-left me-2"></i>Back to Staff List
                        </a>

                        <div class="flex-grow-2 text-center text-md-start">
                            <h2 class="fw-bold mb-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); background-clip: text; -webkit-background-clip: text; color: transparent; white-space: nowrap; overflow-x: auto; overflow-y: hidden; max-width: 100%;">
                                <i class="bi bi-person-badge me-3"></i><?= $staff_name ?> - Projects Progress
                            </h2>
                        </div>

                        <div style="width: 120px;"></div>
                    </div>

                    <?php if (!$customers_list || $customers_list->num_rows == 0): ?>
                        <div class="alert alert-info">No projects found for this staff.</div>
                    <?php else: while ($cust = $customers_list->fetch_assoc()):
                        $cust_id = $cust['customer_id'];
                        $cust_name = htmlspecialchars($cust['name']);
                        $cust_email = htmlspecialchars($cust['email']);

                        if ($customer_search) {
                            $cust_name = preg_replace('/(' . preg_quote($customer_search, '/') . ')/i', '<mark class="highlight">$1</mark>', $cust_name);
                            $cust_email = preg_replace('/(' . preg_quote($customer_search, '/') . ')/i', '<mark class="highlight">$1</mark>', $cust_email);
                        }

                        $avatar_img = $cust['profile_image'] ?? '';
                        $avatar_src = "../" . $avatar_img;

                        if (empty($avatar_img) || !file_exists($avatar_src)) {
                            $avatar_src = "https://ui-avatars.com/api/?name=" . urlencode($cust['name']) . "&background=20304a&color=fff&size=60&rounded=true&bold=true";
                        }

                        $sql_steps = "
                            SELECT progress_step, status, notes, updated_at
                            FROM project_progress
                            WHERE customer_id = $cust_id AND staff_id = $selected_staff_id
                            ORDER BY updated_at ASC
                        ";

                        $steps_res = mysqli_query($conn, $sql_steps);
                        $progress_records = [];
                        $step_status = array_fill(0, count($step_names), 'Pending');
                        $step_notes = array_fill(0, count($step_names), '');

                        while ($row = mysqli_fetch_assoc($steps_res)) {
                            $step_name = $row['progress_step'];
                            $progress_records[] = $row;

                            if (isset($step_order[$step_name])) {
                                $idx = $step_order[$step_name];

                                if ($row['status'] == 'Completed') {
                                    $step_status[$idx] = 'Completed';
                                    $step_notes[$idx] = $row['notes'] ?? '';
                                } elseif ($row['status'] == 'In Progress') {
                                    $step_status[$idx] = 'In Progress';
                                    $step_notes[$idx] = $row['notes'] ?? '';
                                }
                            }
                        }

                        $completed_steps = count(array_filter($step_status, fn($s) => $s == 'Completed'));
                        $total_steps = count($step_names);
                        $next_step_index = -1;

                        for ($i = 0; $i < $total_steps; $i++) {
                            if ($step_status[$i] != 'Completed') {
                                $next_step_index = $i;
                                break;
                            }
                        }

                        $has_next = ($next_step_index != -1);
                    ?>
                        <div class="card-custom mb-4 customer-progress-card"
                             data-customer-name="<?= htmlspecialchars($cust['name']) ?>"
                             data-customer-email="<?= htmlspecialchars($cust['email']) ?>">

                            <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= $avatar_src ?>" width="50" height="50" style="border-radius: 50%; object-fit: cover; border: 2px solid #e4e4e7;">

                                    <div>
                                        <h5 class="mb-0 fw-bold"><?= $cust_name ?></h5>
                                        <small class="text-muted">CUST-<?= str_pad($cust_id, 4, '0', STR_PAD_LEFT) ?> | <?= $cust_email ?></small>
                                    </div>
                                </div>

                                <div>
                                    <button class="btn btn-sm btn-outline-danger delete-project-btn" data-customer-id="<?= $cust_id ?>" data-staff-id="<?= $selected_staff_id ?>">
                                        <i class="bi bi-trash"></i> Delete Project
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <div style="position: relative; margin: 30px 0 20px;">
                                    <div style="position: absolute; top: 22px; left: 5%; width: 90%; height: 3px; background: #e5e7eb; z-index: 1;"></div>

                                    <?php $progress_percent = ($total_steps > 0) ? round(($completed_steps / $total_steps) * 100) : 0; ?>

                                    <div style="position: absolute; top: 22px; left: 5%; width: <?= $progress_percent ?>%; max-width: 90%; height: 3px; background: var(--success-green); z-index: 1; transition: width 0.3s ease;"></div>

                                    <div class="d-flex justify-content-between" style="position: relative; z-index: 2;">
                                        <?php foreach ($step_names as $idx => $step):
                                            $circle_bg = 'white';
                                            $circle_border = '#e5e7eb';
                                            $icon_color = '#a1a1aa';
                                            $text_color = '#a1a1aa';
                                            $status_badge_text = '';
                                            $status_badge_class = '';
                                            $show_mark_btn = false;
                                            $show_textarea = false;
                                            $current_notes = $step_notes[$idx];

                                            if ($step_status[$idx] == 'Completed') {
                                                $circle_bg = 'var(--success-green)';
                                                $circle_border = 'var(--success-green)';
                                                $icon_color = 'white';
                                                $text_color = 'var(--text-dark)';
                                                $status_badge_text = 'Done';
                                                $status_badge_class = 'bg-success';
                                            } elseif ($has_next && $idx == $next_step_index) {
                                                $circle_bg = '#3b82f6';
                                                $circle_border = '#3b82f6';
                                                $icon_color = 'white';
                                                $text_color = '#3b82f6';
                                                $status_badge_text = 'In Progress';
                                                $status_badge_class = 'bg-primary';
                                                $show_mark_btn = true;
                                                $show_textarea = true;
                                            } elseif ($step_status[$idx] == 'In Progress') {
                                                $circle_bg = '#3b82f6';
                                                $circle_border = '#3b82f6';
                                                $icon_color = 'white';
                                                $text_color = '#3b82f6';
                                                $status_badge_text = 'In Progress';
                                                $status_badge_class = 'bg-primary';
                                            } else {
                                                $status_badge_text = 'Pending';
                                                $status_badge_class = 'bg-secondary';
                                            }
                                        ?>
                                            <div class="text-center" style="flex: 1;">
                                                <div style="width:45px;height:45px;margin:0 auto 8px;background:<?= $circle_bg ?>;border:3px solid <?= $circle_border ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;color:<?= $icon_color ?>;font-size:1.2rem;">
                                                    <i class="bi <?= $step_icons[$idx] ?>"></i>
                                                </div>

                                                <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:<?= $text_color ?>;">
                                                    <?= $step ?>
                                                </div>

                                                <?php if ($status_badge_text): ?>
                                                    <span class="badge <?= $status_badge_class ?> mt-2"><?= $status_badge_text ?></span>
                                                <?php endif; ?>

                                                <?php if ($show_textarea): ?>
                                                    <textarea class="form-control mt-2 step-notes" rows="2" placeholder="Add notes (optional)..." data-customer-id="<?= $cust_id ?>" data-step="<?= $step ?>" data-staff-id="<?= $selected_staff_id ?>" style="font-size:0.8rem;"><?= htmlspecialchars($current_notes) ?></textarea>
                                                <?php endif; ?>

                                                <?php if ($show_mark_btn): ?>
                                                    <button class="btn btn-sm btn-dark mt-2 mark-step-btn">Mark Complete</button>
                                                <?php endif; ?>

                                                <?php if ($step_status[$idx] == 'Completed' && !empty($step_notes[$idx])): ?>
                                                    <div class="small text-muted mt-1">Note: <?= nl2br(htmlspecialchars($step_notes[$idx])) ?></div>
                                                <?php endif; ?>

                                                <?php if ($idx == $total_steps - 1 && $step_status[$idx] == 'Completed'): ?>
                                                    <div class="small text-muted mt-2"><?= $completed_steps ?>/<?= $total_steps ?> steps</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Tracking Details</h6>

                                    <?php if (empty($progress_records)): ?>
                                        <div class="text-muted text-center py-3">No records yet.</div>
                                    <?php else: ?>
                                        <div style="max-height:300px;overflow-y:auto;">
                                            <?php foreach ($progress_records as $record):
                                                $status_badge = ($record['status'] == 'Completed') ? 'status-active' : (($record['status'] == 'In Progress') ? 'status-pending' : 'status-inactive');
                                                $dot_color = ($record['status'] == 'Completed') ? 'var(--success-green)' : '#d4d4d8';
                                            ?>
                                                <div style="display:flex;gap:20px;margin-bottom:20px;">
                                                    <div style="margin-top:4px;">
                                                        <div style="width:12px;height:12px;border-radius:50%;background:<?= $dot_color ?>;border:2px solid white;box-shadow:0 0 0 1px <?= $dot_color ?>;"></div>
                                                    </div>

                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($record['progress_step']) ?></div>

                                                        <div>
                                                            <span class="status-badge <?= $status_badge ?>" style="font-size:0.7rem;padding:2px 8px;">
                                                                <?= $record['status'] ?>
                                                            </span>
                                                        </div>

                                                        <?php if (!empty($record['notes'])): ?>
                                                            <div class="small text-muted mt-1">
                                                                <i class="bi bi-chat-left-text"></i> Note: <?= nl2br(htmlspecialchars($record['notes'])) ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="small text-muted mt-1">
                                                            <?= date('d M Y, H:i', strtotime($record['updated_at'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; endif;
                }
            }
        ?>

        <script>
            document.querySelectorAll('.mark-step-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const container = this.parentElement;
                    const notesTextarea = container.querySelector('.step-notes');

                    if (!notesTextarea) {
                        alert('Error: Could not find notes input.');
                        return;
                    }

                    const customerId = notesTextarea.dataset.customerId;
                    const stepName = notesTextarea.dataset.step;
                    const staffId = notesTextarea.dataset.staffId;
                    const notes = notesTextarea.value.trim();

                    if (!customerId || !stepName) return;

                    if (confirm(`Mark "${stepName}" as Completed for this customer?`)) {
                        fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=mark_step_complete&customer_id=${customerId}&step_name=${encodeURIComponent(stepName)}&staff_id=${staffId}&notes=${encodeURIComponent(notes)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) location.reload();
                            else alert('Update failed: ' + data.message);
                        })
                        .catch(err => {
                            alert('Network error, please try again.');
                        });
                    }
                });
            });
            document.querySelectorAll('.delete-project-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const customerId = this.dataset.customerId;
                    const staffId = this.dataset.staffId;

                    if (confirm('Are you sure you want to delete all progress records for this customer? This action cannot be undone.')) {
                        fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=delete_progress&customer_id=${customerId}&staff_id=${staffId}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) location.reload();
                            else alert('Delete failed: ' + data.message);
                        })
                        .catch(err => {
                            alert('Request failed.');
                        });
                    }
                });
            });
        </script>

    <?php endif; ?>

</div>

<script>
    let originalContentCache = {};

    function safeHighlightElement(element, keyword) {
        if (!keyword) return;

        const lowerKeyword = keyword.toLowerCase();

        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function(node) {
                    const parent = node.parentElement;

                    if (parent && (parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE')) {
                        return NodeFilter.FILTER_SKIP;
                    }

                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );

        const textNodes = [];

        while (walker.nextNode()) textNodes.push(walker.currentNode);

        textNodes.forEach(node => {
            const text = node.nodeValue;

            if (text && text.toLowerCase().includes(lowerKeyword)) {
                const regex = new RegExp(`(${keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                const newHtml = text.replace(regex, '<mark class="highlight">$1</mark>');

                if (newHtml !== text) {
                    const span = document.createElement('span');
                    span.innerHTML = newHtml;
                    node.parentNode.replaceChild(span, node);
                }
            }
        });
    }

    function removeSafeHighlights(element) {
        element.querySelectorAll('mark').forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize();
        });
    }

    function cacheOriginalContent(containerId, viewType) {
        const container = document.getElementById(containerId);

        if (!container) return;

        const key = viewType + '_' + containerId;

        if (originalContentCache[key]) return;

        originalContentCache[key] = container.cloneNode(true);
    }

    function restoreOriginalContent(containerId, viewType) {
        const container = document.getElementById(containerId);

        if (!container) return;

        const key = viewType + '_' + containerId;

        if (originalContentCache[key]) {
            container.innerHTML = originalContentCache[key].innerHTML;
        }
    }
    function highlightTable(containerId, keyword) {
        const container = document.getElementById(containerId);

        if (!container) return;

        const view = '<?= $view ?>';

        cacheOriginalContent(containerId, view);
        removeSafeHighlights(container);

        if (!keyword) return;

        safeHighlightElement(container, keyword);
    }

    function filterTable(containerId, keyword) {
        const container = document.getElementById(containerId);

        if (!container) return;

        const view = '<?= $view ?>';

        cacheOriginalContent(containerId, view);
        restoreOriginalContent(containerId, view);

        if (!keyword) return;

        const rows = container.querySelectorAll('tbody tr');
        const lowerKeyword = keyword.toLowerCase();

        rows.forEach(row => {
            let searchText = '';
            const cells = row.querySelectorAll('td');

            cells.forEach(cell => {
                searchText += ' ' + (cell.textContent || cell.innerText);
            });

            const match = searchText.toLowerCase().includes(lowerKeyword);

            if (!match) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
                safeHighlightElement(row, keyword);
            }
        });
    }

    function highlightQuotationsInvoices(keyword) {
        const container = document.getElementById('qiContainer');

        if (!container) return;

        const view = '<?= $view ?>';

        cacheOriginalContent('qiContainer', view);
        removeSafeHighlights(container);

        if (!keyword) return;

        safeHighlightElement(container, keyword);
    }

    function filterQuotationsInvoices(keyword) {
        const container = document.getElementById('qiContainer');

        if (!container) return;

        const view = '<?= $view ?>';

        cacheOriginalContent('qiContainer', view);
        restoreOriginalContent('qiContainer', view);

        if (!keyword) return;

        const rows = document.querySelectorAll('#qiTable tbody tr');
        const lowerKeyword = keyword.toLowerCase();

        rows.forEach(row => {
            const name = row.getAttribute('data-customer-name') || '';
            const email = row.getAttribute('data-customer-email') || '';
            const phone = row.getAttribute('data-customer-phone') || '';
            const products = row.getAttribute('data-products') || '';

            const contactCell = row.querySelector('td:nth-child(2)');
            const quotationCell = row.querySelector('td:nth-child(4)');
            const invoiceCell = row.querySelector('td:nth-child(5)');

            let contactText = contactCell ? contactCell.innerText : '';
            let quotationText = quotationCell ? quotationCell.innerText : '';
            let invoiceText = invoiceCell ? invoiceCell.innerText : '';

            const searchText = (name + ' ' + email + ' ' + phone + ' ' + products + ' ' + contactText + ' ' + quotationText + ' ' + invoiceText).toLowerCase();

            const match = searchText.includes(lowerKeyword);

            if (!match) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
                safeHighlightElement(row, keyword);
            }
        });
    }

    function highlightCustomers(keyword) {
        const container = document.getElementById('customersContainer');

        if (!container) return;

        const view = '<?= $view ?>';

        cacheOriginalContent('customersContainer', view);
        removeSafeHighlights(container);

        if (!keyword) return;

        safeHighlightElement(container, keyword);
    }

    function filterCustomers(keyword) {
        const container = document.getElementById('customersContainer');

        if (!container) return;

        const view = '<?= $view ?>';

        cacheOriginalContent('customersContainer', view);
        restoreOriginalContent('customersContainer', view);

        if (!keyword) return;

        const cards = container.querySelectorAll('.customer-card');
        const lowerKeyword = keyword.toLowerCase();

        cards.forEach(card => {
            const name = card.getAttribute('data-customer-name') || '';
            const email = card.getAttribute('data-customer-email') || '';
            const phone = card.getAttribute('data-customer-phone') || '';
            const products = card.getAttribute('data-customer-products') || '';

            const searchText = (name + ' ' + email + ' ' + phone + ' ' + products).toLowerCase();

            const match = searchText.includes(lowerKeyword);

            if (!match) {
                card.style.display = 'none';
            } else {
                card.style.display = '';
                safeHighlightElement(card, keyword);
            }
        });
    }

    function highlightStaffProgress(keyword) {
        const isOverview = document.getElementById('staffProgressListContainer') !== null;
        const container = isOverview ? document.getElementById('staffProgressListContainer') : document.querySelector('.main-content');

        if (!container) return;

        const view = '<?= $view ?>';
        const key = isOverview ? 'staffProgressListContainer' : 'staff_progress_details';

        cacheOriginalContent(key, view);
        removeSafeHighlights(container);

        if (!keyword) return;

        safeHighlightElement(container, keyword);
    }

    function filterStaffProgress(keyword) {
        const isOverview = document.getElementById('staffProgressListContainer') !== null;
        const container = isOverview ? document.getElementById('staffProgressListContainer') : document.querySelector('.main-content');

        if (!container) return;

        const view = '<?= $view ?>';
        const key = isOverview ? 'staffProgressListContainer' : 'staff_progress_details';

        cacheOriginalContent(key, view);

        if (!keyword) {
            restoreOriginalContent(key, view);

            const allCards = isOverview
                ? document.querySelectorAll('#staffProgressListContainer .staff-card')
                : document.querySelectorAll('.customer-progress-card');

            allCards.forEach(card => card.style.display = '');

            return;
        }

        restoreOriginalContent(key, view);

        const lowerKeyword = keyword.toLowerCase();

        if (isOverview) {
            const cards = document.querySelectorAll('#staffProgressListContainer .staff-card');

            cards.forEach(card => {
                let searchText = card.textContent || card.innerText;

                const name = card.getAttribute('data-staff-name') || '';
                const email = card.getAttribute('data-staff-email') || '';
                const phone = card.getAttribute('data-staff-phone') || '';

                searchText = (searchText + ' ' + name + ' ' + email + ' ' + phone).toLowerCase();

                const match = searchText.includes(lowerKeyword);

                if (!match) {
                    card.style.display = 'none';
                } else {
                    card.style.display = '';
                    safeHighlightElement(card, keyword);
                }
            });
        } else {
            const cards = document.querySelectorAll('.customer-progress-card');

            cards.forEach(card => {
                let searchText = card.textContent || card.innerText;

                const name = card.getAttribute('data-customer-name') || '';
                const email = card.getAttribute('data-customer-email') || '';

                searchText = (searchText + ' ' + name + ' ' + email).toLowerCase();

                const match = searchText.includes(lowerKeyword);

                if (!match) {
                    card.style.display = 'none';
                } else {
                    card.style.display = '';
                    safeHighlightElement(card, keyword);
                }
            });
        }
    }

    const globalSearch = document.getElementById('globalSearchInput');

    if (globalSearch) {
        globalSearch.addEventListener('input', function() {
            const view = '<?= $view ?>';
            const keyword = this.value.trim();

            switch (view) {
                case 'products': highlightTable('productsContainer', keyword); break;
                case 'staff': highlightTable('staffContainer', keyword); break;
                case 'admins': highlightTable('adminsContainer', keyword); break;
                case 'customers': highlightCustomers(keyword); break;
                case 'quotations_invoices': highlightQuotationsInvoices(keyword); break;
                case 'staff_progress': highlightStaffProgress(keyword); break;
                default: break;
            }
        });

        // On Enter: full filter
        globalSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                const view = '<?= $view ?>';
                const keyword = this.value.trim();

                switch (view) {
                    case 'products': filterTable('productsContainer', keyword); break;
                    case 'staff': filterTable('staffContainer', keyword); break;
                    case 'admins': filterTable('adminsContainer', keyword); break;
                    case 'customers': filterCustomers(keyword); break;
                    case 'quotations_invoices': filterQuotationsInvoices(keyword); break;
                    case 'staff_progress': filterStaffProgress(keyword); break;
                    default: break;
                }
            }
        });

        if (globalSearch.value.trim() !== '') {
            globalSearch.dispatchEvent(new Event('input'));
        }
    }

    $(document).ready(function() {
        $('.select2-products').select2({
            placeholder: "Select interested products...",
            allowClear: true,
            dropdownParent: $('#addCustomerModal'),
            width: '100%'
        });

        $('.select2-products-edit').select2({
            placeholder: "Select interested products...",
            allowClear: true,
            dropdownParent: $('#editCustomerModal'),
            width: '100%'
        });
    });

    function validateStaffPasswords() {
        let pwd = document.getElementById('staff_password')?.value;
        let confirm = document.getElementById('staff_confirm_password')?.value;

        if (pwd !== confirm) {
            alert('Passwords do not match!');
            return false;
        }

        if (pwd && pwd.length < 6) {
            alert('Password must be at least 6 characters!');
            return false;
        }

        return true;
    }

    function validateEditStaffPasswords() {
        let pwd = document.getElementById('edit_staff_password')?.value;
        let confirm = document.getElementById('edit_staff_confirm_password')?.value;

        if (pwd !== confirm) {
            alert('New passwords do not match!');
            return false;
        }

        if (pwd && pwd.length < 6) {
            alert('Password must be at least 6 characters!');
            return false;
        }

        return true;
    }

    function validateCustomerPasswords() {
        let pwd = document.getElementById('customer_password')?.value;
        let confirm = document.getElementById('customer_confirm_password')?.value;

        if (pwd !== confirm) {
            alert('Passwords do not match!');
            return false;
        }

        if (pwd && pwd.length < 6) {
            alert('Password must be at least 6 characters!');
            return false;
        }

        return true;
    }

    function validateEditCustomerPasswords() {
        let pwd = document.getElementById('edit_customer_password')?.value;
        let confirm = document.getElementById('edit_customer_confirm_password')?.value;

        if (pwd !== confirm) {
            alert('New passwords do not match!');
            return false;
        }

        if (pwd && pwd.length < 6) {
            alert('Password must be at least 6 characters!');
            return false;
        }

        return true;
    }

    function validateAdminPasswords() {
        let pwd = document.getElementById('a_password')?.value;
        let confirm = document.getElementById('a_confirm_password')?.value;

        if (pwd !== confirm) {
            alert('Passwords do not match!');
            return false;
        }

        if (pwd && pwd.length < 6) {
            alert('Password must be at least 6 characters!');
            return false;
        }

        return true;
    }

    function validateEditAdminPasswords() {
        let pwd = document.getElementById('edit_admin_password')?.value;
        let confirm = document.getElementById('edit_admin_confirm_password')?.value;

        if (pwd !== confirm) {
            alert('New passwords do not match!');
            return false;
        }

        if (pwd && pwd.length < 6) {
            alert('Password must be at least 6 characters!');
            return false;
        }

        return true;
    }

    function editCustomer(id, name, email, phone, address, gender, race, selectedProductsJson) {
        document.querySelector('#edit_customer_id').value = id;
        document.querySelector('#edit_customer_name').value = name;
        document.querySelector('#edit_customer_email').value = email;
        document.querySelector('#edit_customer_phone').value = phone;
        document.querySelector('#edit_customer_address').value = address;
        document.querySelector('#edit_customer_gender').value = gender;
        document.querySelector('#edit_customer_race').value = race;

        if (selectedProductsJson) {
            let pids = JSON.parse(selectedProductsJson);
            $('#edit_customer_products').val(pids).trigger('change');
        } else {
            $('#edit_customer_products').val(null).trigger('change');
        }
    }

    document.querySelectorAll('.edit-staff-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_staff_id').value = this.dataset.id;
            document.getElementById('edit_staff_name').value = this.dataset.name;
            document.getElementById('edit_staff_email').value = this.dataset.email;
            document.getElementById('edit_staff_phone').value = this.dataset.phone;
            document.getElementById('edit_staff_calendar').value = this.dataset.calendar || '';

            let avatarPreview = document.getElementById('edit_staff_avatar_preview');

            if (avatarPreview) avatarPreview.src = this.dataset.avatar;
        });
    });

    document.querySelectorAll('.edit-admin-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_admin_id').value = this.dataset.id;
            document.getElementById('edit_admin_fullname').value = this.dataset.fullname;
            document.getElementById('edit_admin_email').value = this.dataset.email;
            document.getElementById('edit_admin_phone').value = this.dataset.phone;
            document.getElementById('edit_admin_address').value = this.dataset.address;
            document.getElementById('edit_admin_calendar').value = this.dataset.calendar || '';
        });
    });

    function previewEditStaffAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('edit_staff_avatar_preview').src = e.target.result;
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

</body>
</html>