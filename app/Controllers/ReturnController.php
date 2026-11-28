<?php
/**
 * ReturnController - Mô-đun Quản lý trả sách
 * Người phụ trách: Hoài Linh
 *
 * Route (khai báo trong routes/web.php):
 *   GET  /return           -> index()    Danh sách đang mượn + xác nhận trả
 *   GET  /return/overdue   -> overdue()  Xử lý quá hạn
 *   GET  /return/history   -> history()  Lịch sử trả sách
 *   POST /return/confirm   -> confirm()  Xác nhận trả sách
 *   POST /return/extend    -> extend()   Gia hạn ngày trả
 *
 * index.php tự nạp file này và các Model trong app/Models, và dùng các hàm
 * view(), redirect(), flash(), pullFlash(), verifyCsrf() có sẵn.
 */
class ReturnController
{
    private ReturnBook $returnModel;

    public function __construct()
    {
        // Phân quyền dùng chung của nhóm (AuthController của Tấn Trình)
        if (!class_exists('AuthController') || !method_exists('AuthController', 'requireRole')) {
            http_response_code(500);
            exit('Thiếu AuthController::requireRole(). Hãy lấy code mới nhất của nhóm về (git pull).');
        }
        AuthController::requireRole(['admin', 'librarian']);

        $this->returnModel = new ReturnBook((new Database())->getConnection());
    }

    // ---------------- Các trang ----------------

    public function index(): void
    {
        $keyword = trim((string) ($_GET['keyword'] ?? ''));
        view('librarian/return/index', [
            'title'    => 'Quản lý trả sách',
            'borrows'  => $this->returnModel->getBorrowing($keyword),
            'keyword'  => $keyword,
            'maxRenew' => ReturnBook::MAX_RENEW,
            'flash'    => pullFlash(),
        ]);
    }

    public function overdue(): void
    {
        view('librarian/return/overdue', [
            'title'       => 'Xử lý quá hạn',
            'overdueList' => $this->returnModel->getOverdue(),
            'finePerDay'  => ReturnBook::FINE_PER_DAY,
            'flash'       => pullFlash(),
        ]);
    }

    public function history(): void
    {
        view('librarian/return/history', [
            'title'   => 'Lịch sử trả sách',
            'history' => $this->returnModel->getHistory(),
            'flash'   => pullFlash(),
        ]);
    }

    // ---------------- Các thao tác (POST) ----------------

    public function confirm(): void
    {
        verifyCsrf();

        $staffId = $this->currentUserId();
        if ($staffId === 0) {
            flash('error', 'Không xác định được người đang đăng nhập. Hãy đăng nhập lại.');
            redirect('/return');
        }

        [$ok, $msg] = $this->returnModel->confirmReturn(
            (int) ($_POST['borrow_id'] ?? 0),
            $staffId,
            (string) ($_POST['book_condition'] ?? 'good'),
            (int) ($_POST['extra_fee'] ?? 0),
            trim((string) ($_POST['note'] ?? '')) ?: null
        );

        flash($ok ? 'success' : 'error', $msg);
        redirect($this->backPath());
    }

    public function extend(): void
    {
        verifyCsrf();

        [$ok, $msg] = $this->returnModel->extendDueDate((int) ($_POST['borrow_id'] ?? 0));

        flash($ok ? 'success' : 'error', $msg);
        redirect('/return');
    }

    // ---------------- Hàm hỗ trợ ----------------

    /** Quay về đúng trang vừa thao tác (chỉ cho phép 2 trang của mô-đun). */
    private function backPath(): string
    {
        return str_contains((string) ($_POST['back'] ?? ''), 'overdue')
            ? '/return/overdue'
            : '/return';
    }

    /**
     * Lấy id người đang đăng nhập từ session.
     * CHỈNH lại cho khớp cách AuthController lưu session sau khi đăng nhập.
     */
    private function currentUserId(): int
    {
        $user = $_SESSION['user'] ?? null;
        if (is_array($user) && isset($user['id'])) {
            return (int) $user['id'];
        }
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
