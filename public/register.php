<?php
require_once '../db/db_connect.php';
if (isset($_POST['register'])) {
    bookstore_models();$data=$_POST;$model=new \App\Models\UserModel();
    foreach (['username','password','fullname','phone','address'] as $key) if (empty($data[$key])) bookstore_reject('Thiếu thông tin bắt buộc.',400);
    if (strlen($data['password'])<6) bookstore_reject('Mật khẩu phải ít nhất 6 ký tự.',400);
    if ($model->usernameExists($data['username'])) bookstore_reject('Tên đăng nhập đã được sử dụng.',409);
    $model->register($data);header('Location: login.php');exit;
}
$stitchPage='register';require dirname(__DIR__).'/app/views/stitch/render.php';
