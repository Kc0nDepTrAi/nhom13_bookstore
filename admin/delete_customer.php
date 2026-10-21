<?php
require_once '../db/db_connect.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') bookstore_reject('Thao tác xóa cần gửi bằng POST.',405);
$id=(int)($_POST['id']??0);
$stmt=$conn->prepare('DELETE FROM users WHERE id=?');$stmt->bind_param('i',$id);$stmt->execute();
header('Location: manage_customers.php');exit;
