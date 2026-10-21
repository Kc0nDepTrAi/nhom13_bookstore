<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
session_destroy();
header("Location: login.php");
exit();
?>