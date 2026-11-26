<?php
class Book {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy danh sách sách + tìm kiếm theo tên, tác giả, mã sách + lọc theo thể loại
    public function getAll($keyword = '', $categoryId = '') {
        $sql = "SELECT b.*, c.name AS category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE 1=1";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.book_code LIKE ?)";
            $like = "%{$keyword}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($categoryId !== '') {
            $sql .= " AND b.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY b.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function existsByCode($bookCode, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM books WHERE book_code = ? AND id != ?");
            $stmt->execute([$bookCode, $excludeId]);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM books WHERE book_code = ?");
            $stmt->execute([$bookCode]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function create($data) {
        $sql = "INSERT INTO books 
                (book_code, title, author, publisher, publish_year, category_id, quantity, borrowed_quantity, location, description, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['book_code'],
            $data['title'],
            $data['author'],
            $data['publisher'] ?: null,
            $data['publish_year'] ?: null,
            $data['category_id'],
            $data['quantity'],
            $data['location'] ?: null,
            $data['description'] ?: null,
            $data['status']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE books SET 
                book_code = ?, title = ?, author = ?, publisher = ?, publish_year = ?, 
                category_id = ?, quantity = ?, location = ?, description = ?, status = ? 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['book_code'],
            $data['title'],
            $data['author'],
            $data['publisher'] ?: null,
            $data['publish_year'] ?: null,
            $data['category_id'],
            $data['quantity'],
            $data['location'] ?: null,
            $data['description'] ?: null,
            $data['status'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }
}