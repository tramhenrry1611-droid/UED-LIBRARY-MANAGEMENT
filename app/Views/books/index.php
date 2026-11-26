<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sách - UED Library</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .btn { padding: 8px 14px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #1a73e8; color: #fff; }
        .btn-secondary { background: #5f6368; color: #fff; }
        .btn-warn { background: #f9ab00; color: #000; padding: 4px 8px; }
        .btn-danger { background: #d93025; color: #fff; padding: 4px 8px; }
        .filter-box { background: #f8f9fa; padding: 12px; border-radius: 6px; margin-bottom: 16px; display: flex; gap: 8px; }
        .filter-box input, .filter-box select { padding: 7px 10px; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f1f3f4; }
        .alert-success { background: #e6f4ea; color: #137333; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        .alert-error { background: #fce8e6; color: #c5221f; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="top-bar">
        <h2>Quản lý kho sách thư viện</h2>
        <div>
            <a href="index.php?route=categories" class="btn btn-secondary">Quản lý thể loại</a>
            <a href="index.php?route=books/create" class="btn btn-primary">+ Thêm sách mới</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="GET" action="index.php" class="filter-box">
        <input type="hidden" name="route" value="books">
        <input type="text" name="keyword" placeholder="Tìm theo tên sách, tác giả, mã sách..." 
               value="<?= htmlspecialchars($keyword ?? '') ?>" style="width: 300px;">
        <select name="category_id">
            <option value="">-- Tất cả thể loại --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= (($categoryId ?? '') == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        <a href="index.php?route=books" class="btn btn-secondary">Đặt lại</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Mã sách</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Thể loại</th>
                <th>NXB / Năm</th>
                <th>Tổng SL</th>
                <th>Đang mượn</th>
                <th>Vị trí</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($books)): ?>
                <tr><td colspan="10" style="text-align:center;">Không tìm thấy đầu sách nào phù hợp.</td></tr>
            <?php else: ?>
                <?php foreach ($books as $b): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($b['book_code']) ?></strong></td>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['author']) ?></td>
                    <td><?= htmlspecialchars($b['category_name'] ?? 'Chưa phân loại') ?></td>
                    <td><?= htmlspecialchars(($b['publisher'] ?? '-') . ' (' . ($b['publish_year'] ?? '-') . ')') ?></td>
                    <td><?= (int)$b['quantity'] ?></td>
                    <td><?= (int)$b['borrowed_quantity'] ?></td>
                    <td><?= htmlspecialchars($b['location'] ?? '') ?></td>
                    <td><?= ((int)$b['status'] === 1) ? 'Đang lưu hành' : 'Ngừng mượn' ?></td>
                    <td>
                        <a href="index.php?route=books/edit&id=<?= (int)$b['id'] ?>" class="btn btn-warn">Sửa</a>
                        <a href="index.php?route=books/delete&id=<?= (int)$b['id'] ?>" class="btn btn-danger"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa cuốn sách này?')">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>