<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fyp";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die(mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>