<?php
require_once dirname(__DIR__) . "/config/config.php";
$conn = mysqli_connect("localhost", "root", "", "bookstore_db");
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");
mysqli_query($conn, "SET time_zone = '+07:00'");
?>