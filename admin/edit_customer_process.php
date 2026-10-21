<?php
require_once '../db/db_connect.php';bookstore_models();
if (isset($_POST['update_user'])) {
    (new \App\Models\UserModel())->updateProfile((int)$_POST['id'],$_POST);
    header('Location: manage_customers.php');exit;
}
