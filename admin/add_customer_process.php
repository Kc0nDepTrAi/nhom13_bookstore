<?php
require_once '../db/db_connect.php';bookstore_models();
if (isset($_POST['add_user'])) {
    $data=$_POST;$model=new \App\Models\UserModel();
    if (($data['password']??'')!==($data['re_password']??'') || strlen($data['password']??'')<6) bookstore_reject('Mật khẩu cần khớp và có ít nhất 6 ký tự.',400);
    if (empty($data['username']) || empty($data['fullname'])) bookstore_reject('Thiếu tên đăng nhập hoặc họ tên.',400);
    if ($model->usernameExists($data['username'])) bookstore_reject('Tên đăng nhập đã được sử dụng.',409);
    $model->register($data);header('Location: manage_customers.php');exit;
}
