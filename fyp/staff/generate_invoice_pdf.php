<?php
session_start();
header('Content-Type: application/json');
require '../includes/db.php';
require_once 'invoice_functions.php'; 

if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}