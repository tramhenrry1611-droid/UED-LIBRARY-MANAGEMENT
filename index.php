<?php
/**
 * index.php - điểm vào duy nhất của ứng dụng.
 * Nạp cấu hình, Model, Controller rồi chuyển request tới đúng Controller theo routes/web.php
 */

declare(strict_types=1);
session_start();

define('ROOT_PATH', __DIR__);
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

require_once ROOT_PATH . '/config/database.php';

foreach (glob(ROOT_PATH . '/app/Models/*.php') as $file) {
    require_once $file;
}
foreach (glob(ROOT_PATH . '/app/Controllers/*.php') as $file) {
    require_once $file;
}

/* ------------------------------------------------------------
 * Hàm hỗ trợ dùng chung
 * ---------------------------------------------------------- */

/** Chống XSS khi in dữ liệu ra HTML. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Tạo đường dẫn tuyệt đối trong ứng dụng, ví dụ url('/login'). */
function url(string $path = '/'): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Đường dẫn tới file trong thư mục public (css, js, images). */
function asset(string $path): string
{
    return BASE_URL . '/public/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** Hiển thị View trong app/Views, bọc bởi layouts/header và layouts/footer. */
function view(string $name, array $data = []): void
{
    extract($data);
    require ROOT_PATH . '/app/Views/layouts/header.php';
    require ROOT_PATH . '/app/Views/' . $name . '.php';
    require ROOT_PATH . '/app/Views/layouts/footer.php';
}

/** Thông báo một lần (flash) hiển thị sau khi redirect. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pullFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/** Giữ lại dữ liệu người dùng đã nhập khi form bị lỗi. */
function old(string $key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/** Kiểm tra token của form POST, sai thì dừng với mã 419. */
function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), (string) $token)) {
        http_response_code(419);
        exit('Phiên làm việc hết hạn hoặc yêu cầu không hợp lệ. Hãy tải lại trang và thử lại.');
    }
}

/* ------------------------------------------------------------
 * Bộ định tuyến (router)
 * ---------------------------------------------------------- */

$routes = require ROOT_PATH . '/routes/web.php';
$method = $_SERVER['REQUEST_METHOD'];
$path   = '/' . trim((string) ($_GET['url'] ?? ''), '/');

foreach ($routes as [$routeMethod, $routePath, $controllerName, $action]) {
    if ($routeMethod !== $method) {
        continue;
    }

    $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $routePath) . '$#';
    if (preg_match($pattern, $path, $matches)) {
        $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
        $controller = new $controllerName();
        $controller->$action(...$params);
        unset($_SESSION['old']);
        exit;
    }
}

http_response_code(404);
view('layouts/404', ['title' => 'Không tìm thấy trang']);
