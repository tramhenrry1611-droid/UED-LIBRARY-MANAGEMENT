<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cập nhật thông tin sách</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; max-width: 650px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-primary { background: #1a73e8; color: #fff; }
        .btn-secondary { background: #5f6368; color: #fff; }
        .alert-error { background: #fce8e6; color: #c5221f; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h2>Cập nhật sách: <?= htmlspecialchars($book['title']) ?></h2>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?route=books/update">
        <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
        <div class="form-group">
            <label>Mã sách (*)</label>
            <input type="text" name="book_code" value="<?= htmlspecialchars($book['book_code']) ?>" required>
        </div>
        <div class="form-group">
            <label>Tên sách (*)</label>
            <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Tác giả (*)</label>
            <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
        </div>
        <div class="form-group">
            <label>Thể loại (*)</label>
            <select name="category_id" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= ($book['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Nhà xuất bản</label>
            <input type="text" name="publisher" value="<?= htmlspecialchars($book['publisher'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Năm xuất bản</label>
            <input type="number" name="publish_year" value="<?= htmlspecialchars($book['publish_year'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Tổng số lượng (Đang mượn: <?= (int)$book['borrowed_quantity'] ?>)</label>
            <input type="number" name="quantity" min="<?= (int)$book['borrowed_quantity'] ?>" value="<?= (int)$book['quantity'] ?>" required>
        </div>
        <div class="form-group">
            <label>Vị trí kệ sách</label>
            <input type="text" name="location" value="<?= htmlspecialchars($book['location'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
                <option value="1" <?= ((int)$book['status'] === 1) ? 'selected' : '' ?>>Đang lưu hành</option>
                <option value="0" <?= ((int)$book['status'] === 0) ? 'selected' : '' ?>>Ngừng cho mượn</option>
            </select>
        </div>
        <div class="form-group">
            <label>Mô tả tóm tắt</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="index.php?route=books" class="btn btn-secondary">Hủy</a>
    </form>
</body>
</html>