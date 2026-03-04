<?php
session_start();
if(isset($_SESSION['admin'])){
    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login-staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('css/background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            margin: 0;
        }

        .admin-card {
            border: none;
            border-radius: 25px;
            background: rgba(33, 37, 41, 0.9); 
            backdrop-filter: blur(15px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            padding: 20px;
        }

        .admin-header {
            text-align: center;
            padding: 30px 10px 20px 10px;
        }

        .admin-header img { 
            width: 250px; 
            height: auto;
            margin-bottom: 15px;
        }

        .admin-header h4 {
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 1.2rem;
        }

        .form-label {
            color: #ced4da;
            font-weight: 500;
        }

        .form-control {
            border-radius: 12px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #dc3545;
            color: #fff;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .btn-admin {
            background-color: #dc3545;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            color: white;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-admin:hover {
            background-color: #bb2d3b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }

        .footer-links {
            text-align: center;
            margin-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
        }

        .back-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card admin-card">
                <div class="admin-header">
                    
                    <h4>Staff sign in</h4>
                    <p style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">Authorized Access Only</p>
                </div>
                
                <div class="card-body p-4 pt-0">
                    <form action="admin_login_process.php" method="post">
                        <div class="mb-3">
                            <label class="form-label small">Admin Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Enter Admin ID" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small">Secret Password</label>
                            <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                        </div>

                        <button type="submit" name="admin_login" class="btn btn-admin w-100">Sign In as admin</button>
                    </form>

                    <div class="footer-links">
                        <a href="../homepage.html" class="back-link">← Back to homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>