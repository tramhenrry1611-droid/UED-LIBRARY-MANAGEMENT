<?php
class Category {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM categories ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function existsByName($name, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
            $stmt->execute([$name]);
        }
        return $stmt->fetchColumn() > 0;
    }

    public function create($name, $description) {
        $stmt = $this->conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        return $stmt->execute([$name, $description]);
    }

    public function update($id, $name, $description) {
        $stmt = $this->conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $id]);
    }

    public function countBooks($categoryId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM books WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        return (int)$stmt->fetchColumn();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}