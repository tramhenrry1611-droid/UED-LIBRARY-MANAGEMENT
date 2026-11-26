<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý thể loại sách</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; max-width: 800px; }
        .form-box { background: #f8f9fa; padding: 14px; border-radius: 6px; margin-bottom: 18px; }
        input, textarea { width: 100%; padding: 8px; margin: 6px 0 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f1f3f4; }
        .btn { padding: 7px 14px; border-radius: 4px; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #1a73e8; color: #fff; }
        .btn-secondary { background: #5f6368; color: #fff; }
        .btn-warn { background: #f9ab00; color: #000; padding: 4px 8px; }
        .btn-danger { background: #d93025; color: #fff; padding: 4px 8px; }
        .alert-success { background: #e6f4ea; color: #137333; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        .alert-error { background: #fce8e6; color: #c5221f; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Quản lý thể loại sách</h2>
        <a href="index.php?route=books" class="btn btn-secondary">← Quay lại danh sách sách</a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h3><?= $editCategory ? 'Sửa thể loại' : 'Thêm thể loại mới' ?></h3>
        <form method="POST" action="index.php?route=<?= $editCategory ? 'categories/update' : 'categories/store' ?>">
            <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
            <?php endif; ?>
            <label>Tên thể loại (*)</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>">
            <label>Mô tả</label>
            <textarea name="description" rows="2"><?= htmlspecialchars($editCategory['description'] ?? '') ?></textarea>
            <button type="submit" class="btn btn-primary"><?= $editCategory ? 'Lưu thay đổi' : 'Thêm thể loại' ?></button>
            <?php if ($editCategory): ?>
                <a href="index.php?route=categories" class="btn btn-secondary">Hủy sửa</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên thể loại</th>
                <th>Mô tả</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= (int)$cat['id'] ?></td>
                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                <td><?= htmlspecialchars($cat['description'] ?? '') ?></td>
                <td>
                    <a href="index.php?route=categories&edit_id=<?= (int)$cat['id'] ?>" class="btn btn-warn">Sửa</a>
                    <a href="index.php?route=categories/delete&id=<?= (int)$cat['id'] ?>" class="btn btn-danger"
                       onclick="return confirm('Xóa thể loại này?')">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>