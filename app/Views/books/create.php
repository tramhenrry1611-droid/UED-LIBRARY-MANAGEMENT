<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sách mới</title>
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
    <h2>Thêm sách mới vào thư viện</h2>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?route=books/store">
        <div class="form-group">
            <label>Mã sách (*)</label>
            <input type="text" name="book_code" required placeholder="VD: BK006">
        </div>
        <div class="form-group">
            <label>Tên sách (*)</label>
            <input type="text" name="title" required>
        </div>
        <div class="form-group">
            <label>Tác giả (*)</label>
            <input type="text" name="author" required>
        </div>
        <div class="form-group">
            <label>Thể loại (*)</label>
            <select name="category_id" required>
                <option value="">-- Chọn thể loại --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Nhà xuất bản</label>
            <input type="text" name="publisher">
        </div>
        <div class="form-group">
            <label>Năm xuất bản</label>
            <input type="number" name="publish_year" min="1900" max="2026">
        </div>
        <div class="form-group">
            <label>Tổng số lượng (*)</label>
            <input type="number" name="quantity" min="0" value="1" required>
        </div>
        <div class="form-group">
            <label>Vị trí kệ sách</label>
            <input type="text" name="location" placeholder="VD: Tầng 2 - Kệ A1">
        </div>
        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
                <option value="1">Đang lưu hành</option>
                <option value="0">Ngừng cho mượn</option>
            </select>
        </div>
        <div class="form-group">
            <label>Mô tả tóm tắt</label>
            <textarea name="description" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Lưu sách</button>
        <a href="index.php?route=books" class="btn btn-secondary">Quay lại</a>
    </form>
</body>
</html>