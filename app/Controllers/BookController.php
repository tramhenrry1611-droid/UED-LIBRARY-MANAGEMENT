<?php
require_once __DIR__ . '/../Models/Book.php';
require_once __DIR__ . '/../Models/Category.php';

class BookController {
    private $bookModel;
    private $categoryModel;

    public function __construct($db) {
        $this->bookModel = new Book($db);
        $this->categoryModel = new Category($db);
    }

    // 1 & 6. Danh sách sách + Tìm kiếm & Lọc theo thể loại
    public function index() {
        $keyword = trim($_GET['keyword'] ?? '');
        $categoryId = $_GET['category_id'] ?? '';

        $books = $this->bookModel->getAll($keyword, $categoryId);
        $categories = $this->categoryModel->getAll();

        require_once __DIR__ . '/../Views/books/index.php';
    }

    // 2. Hiển thị form thêm sách
    public function create() {
        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../Views/books/create.php';
    }

    // 2. Lưu sách mới
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=books");
            exit;
        }

        $data = [
            'book_code'    => trim($_POST['book_code'] ?? ''),
            'title'        => trim($_POST['title'] ?? ''),
            'author'       => trim($_POST['author'] ?? ''),
            'publisher'    => trim($_POST['publisher'] ?? ''),
            'publish_year' => trim($_POST['publish_year'] ?? ''),
            'category_id'  => (int)($_POST['category_id'] ?? 0),
            'quantity'     => max(0, (int)($_POST['quantity'] ?? 0)),
            'location'     => trim($_POST['location'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'status'       => (int)($_POST['status'] ?? 1),
        ];

        if ($data['book_code'] === '' || $data['title'] === '' || $data['author'] === '' || $data['category_id'] <= 0) {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ Mã sách, Tên sách, Tác giả và Thể loại.";
            header("Location: index.php?route=books/create");
            exit;
        }

        if ($this->bookModel->existsByCode($data['book_code'])) {
            $_SESSION['error'] = "Mã sách '{$data['book_code']}' đã tồn tại. Vui lòng nhập mã khác.";
            header("Location: index.php?route=books/create");
            exit;
        }

        $this->bookModel->create($data);
        $_SESSION['success'] = "Thêm sách mới thành công!";
        header("Location: index.php?route=books");
        exit;
    }

    // 3. Hiển thị form sửa sách
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $book = $this->bookModel->findById($id);

        if (!$book) {
            $_SESSION['error'] = "Không tìm thấy sách cần sửa.";
            header("Location: index.php?route=books");
            exit;
        }

        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../Views/books/edit.php';
    }

    // 3. Cập nhật thông tin sách
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=books");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $book = $this->bookModel->findById($id);
        if (!$book) {
            $_SESSION['error'] = "Sách không tồn tại.";
            header("Location: index.php?route=books");
            exit;
        }

        $data = [
            'book_code'    => trim($_POST['book_code'] ?? ''),
            'title'        => trim($_POST['title'] ?? ''),
            'author'       => trim($_POST['author'] ?? ''),
            'publisher'    => trim($_POST['publisher'] ?? ''),
            'publish_year' => trim($_POST['publish_year'] ?? ''),
            'category_id'  => (int)($_POST['category_id'] ?? 0),
            'quantity'     => max(0, (int)($_POST['quantity'] ?? 0)),
            'location'     => trim($_POST['location'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'status'       => (int)($_POST['status'] ?? 1),
        ];

        if ($data['quantity'] < (int)$book['borrowed_quantity']) {
            $_SESSION['error'] = "Tổng số lượng ({$data['quantity']}) không được nhỏ hơn số sách đang được mượn ({$book['borrowed_quantity']}).";
            header("Location: index.php?route=books/edit&id={$id}");
            exit;
        }

        if ($this->bookModel->existsByCode($data['book_code'], $id)) {
            $_SESSION['error'] = "Mã sách '{$data['book_code']}' đã thuộc về một cuốn sách khác.";
            header("Location: index.php?route=books/edit&id={$id}");
            exit;
        }

        $this->bookModel->update($id, $data);
        $_SESSION['success'] = "Cập nhật thông tin sách thành công!";
        header("Location: index.php?route=books");
        exit;
    }

    // 4. Xóa sách (chặn xóa nếu đang có người mượn)
    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        $book = $this->bookModel->findById($id);

        if (!$book) {
            $_SESSION['error'] = "Không tìm thấy sách cần xóa.";
            header("Location: index.php?route=books");
            exit;
        }

        if ((int)$book['borrowed_quantity'] > 0) {
            $_SESSION['error'] = "Không thể xóa sách '{$book['title']}' vì đang có {$book['borrowed_quantity']} cuốn được mượn.";
            header("Location: index.php?route=books");
            exit;
        }

        $this->bookModel->delete($id);
        $_SESSION['success'] = "Đã xóa sách thành công!";
        header("Location: index.php?route=books");
        exit;
    }

    // 5. Quản lý danh mục (thể loại)
    public function categories() {
        $categories = $this->categoryModel->getAll();
        $editCategory = null;
        if (isset($_GET['edit_id'])) {
            $editCategory = $this->categoryModel->findById((int)$_GET['edit_id']);
        }
        require_once __DIR__ . '/../Views/books/categories.php';
    }

    public function storeCategory() {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $_SESSION['error'] = "Tên thể loại không được để trống.";
        } elseif ($this->categoryModel->existsByName($name)) {
            $_SESSION['error'] = "Thể loại '{$name}' đã tồn tại.";
        } else {
            $this->categoryModel->create($name, $description);
            $_SESSION['success'] = "Thêm thể loại thành công!";
        }
        header("Location: index.php?route=categories");
        exit;
    }

    public function updateCategory() {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            $_SESSION['error'] = "Tên thể loại không được để trống.";
        } elseif ($this->categoryModel->existsByName($name, $id)) {
            $_SESSION['error'] = "Tên thể loại '{$name}' đã tồn tại.";
        } else {
            $this->categoryModel->update($id, $name, $description);
            $_SESSION['success'] = "Cập nhật thể loại thành công!";
        }
        header("Location: index.php?route=categories");
        exit;
    }

    public function deleteCategory() {
        $id = (int)($_GET['id'] ?? 0);
        $bookCount = $this->categoryModel->countBooks($id);

        if ($bookCount > 0) {
            $_SESSION['error'] = "Không thể xóa thể loại này vì đang có {$bookCount} đầu sách thuộc thể loại.";
        } else {
            $this->categoryModel->delete($id);
            $_SESSION['success'] = "Đã xóa thể loại thành công!";
        }
        header("Location: index.php?route=categories");
        exit;
    }
}