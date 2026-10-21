<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('bookstore_sess');
    session_start();
}
include '../db/db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_session = $_SESSION['username'];

bookstore_models();
$model=new \App\Models\UserModel();$user=$model->findByUsername($user_session);
if (!$user) bookstore_reject('Phiên đăng nhập không hợp lệ.',401);
if (isset($_POST['update_profile'])) {$model->updateProfile((int)$user['id'],$_POST);header('Location: profile.php');exit;}
if (isset($_POST['change_password'])) {
    $new=$_POST['new_password']??'';
    if (!$model->verifyPassword($user_session,(string)($_POST['old_password']??''))) bookstore_reject('Mật khẩu hiện tại không chính xác.',401);
    if (strlen($new)<6 || $new!==($_POST['confirm_password']??'')) bookstore_reject('Mật khẩu mới cần khớp và có ít nhất 6 ký tự.',400);
    $model->changePassword((int)$user['id'],$new);header('Location: profile.php');exit;
}
// 4. XỬ LÝ XÓA TÀI KHOẢN
if (isset($_POST['delete_account'])) {
    $delete_sql = 'DELETE FROM users WHERE id = '.(int)$user['id'];
    if (mysqli_query($conn, $delete_sql)) {
        session_destroy();
        echo "<script>alert('Tài khoản đã được xóa vĩnh viễn!'); window.location='index.php';</script>";
    }
}
?>

<?php
$stitchPage = 'account';
require dirname(__DIR__, 1) . '/app/views/stitch/render.php';
?>
