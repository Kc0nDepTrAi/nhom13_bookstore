<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
// ============================================================
//  config/config.php — Cấu hình toàn bộ ứng dụng
// ============================================================

// Base URL của ứng dụng (không có dấu / cuối)
define('BASE_URL', '/bookstore');

// Đường dẫn tuyệt đối đến thư mục gốc
define('ROOT_PATH',  dirname(__DIR__));
define('APP_PATH',   ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH  . '/views');

// Database
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'bookstore_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Ảnh
define('IMAGES_DIR', ROOT_PATH . '/images');
define('IMAGES_URL', BASE_URL  . '/images');

// Session
define('SESSION_NAME', 'bookstore_sess');

// Khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}


// Shared protection for existing API and legacy entry points.
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
function bookstore_reject(string $message, int $status = 403): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'status'=>'error','message'=>$message], JSON_UNESCAPED_UNICODE);
    exit;
}
function bookstore_models(): void {
    spl_autoload_register(static function(string $class): void {
        $parts=explode('\\', $class); $file=array_pop($parts).'.php';
        $path=ROOT_PATH.'/'.strtolower(implode('/', $parts)).'/'.$file;
        if (is_file($path)) require_once $path;
    });
}
function bookstore_validate_book(array $data): void {
    foreach (['title','author'] as $field) {
        if (array_key_exists($field,$data) && (!is_string($data[$field]) || trim($data[$field])==='' || strlen($data[$field])>1000))
            throw new InvalidArgumentException('Tên sách và tác giả không được để trống.');
    }
    if (isset($data['price']) && (!is_numeric($data['price']) || !is_finite((float)$data['price']) || (float)$data['price']<=0 || (float)$data['price']>99999999.99))
        throw new InvalidArgumentException('Giá sách phải lớn hơn 0 và trong giới hạn cho phép.');
    foreach (['quantity','sold'] as $field) {
        if (array_key_exists($field,$data) && (filter_var($data[$field], FILTER_VALIDATE_INT)===false || (int)$data[$field]<0 || (int)$data[$field]>2147483647))
            throw new InvalidArgumentException('Số lượng phải là số nguyên không âm.');
    }
}
function bookstore_upload(array $file): string {
    if (($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']??'')) throw new InvalidArgumentException('Không có ảnh hợp lệ.');
    if (($file['size']??0)>4*1024*1024 || filesize($file['tmp_name'])>4*1024*1024) throw new InvalidArgumentException('Ảnh không được vượt quá 4 MB.');
    $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
    $types=['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp'];
    $info=@getimagesize($file['tmp_name']);
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$ext]) || !$info || $mime!==$types[$ext] || ($info['mime']??'')!==$mime) throw new InvalidArgumentException('Nội dung ảnh không khớp định dạng JPG, PNG, GIF hoặc WebP.');
    $name=bin2hex(random_bytes(16)).'.'.$ext;
    if (!is_dir(IMAGES_DIR)) mkdir(IMAGES_DIR,0755,true);
    if (!move_uploaded_file($file['tmp_name'],IMAGES_DIR.'/'.$name)) throw new RuntimeException('Không lưu được ảnh.');
    return $name;
}
if (PHP_SAPI !== 'cli') {
    $script=str_replace('\\','/', $_SERVER['SCRIPT_FILENAME']??'');
    if (str_contains($script,'/admin/') && ($_SESSION['role']??'')!=='admin') bookstore_reject('Bạn cần quyền quản trị.');
    $method=$_SERVER['REQUEST_METHOD']??'GET';
    if (!in_array($method,['GET','HEAD','OPTIONS'],true)) {
        $token=$_SERVER['HTTP_X_CSRF_TOKEN']??$_POST['_csrf']??'';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'],$token)) bookstore_reject('Phiên xác nhận không hợp lệ. Hãy tải lại trang.');
    }
}
