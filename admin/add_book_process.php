<?php
require_once '../db/db_connect.php';bookstore_models();
if (isset($_POST['add_book'])) {
    try {
        $data=$_POST;bookstore_validate_book($data);
        foreach (['title','author','price','category_id'] as $key) if (empty($data[$key])) throw new InvalidArgumentException('Thiếu thông tin sách.');
        $cat=(new \App\Models\CategoryModel())->findById((int)$data['category_id']);
        if (!$cat) throw new InvalidArgumentException('Thể loại không hợp lệ.');
        $data['image']=bookstore_upload($_FILES['image']??[]);
        $id=(new \App\Models\BookModel())->createBook($data,$cat['name']);
        header('Location: book_detail.php?id='.$id);exit;
    } catch (Throwable $e) {bookstore_reject($e->getMessage(),400);}
}
