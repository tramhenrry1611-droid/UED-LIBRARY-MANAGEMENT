<?php
/* Phần dùng chung của các trang trả sách: kiểu dáng, menu, thông báo.
   Được bọc bởi layouts/header và layouts/footer thông qua hàm view(). */
if (!function_exists('money')) {
    function money($value): string
    {
        return number_format((float) $value, 0, ',', '.') . ' đ';
    }
}
?>
<style>
    .return-page { max-width: 1100px; margin: 24px auto; }
    .return-page nav a { margin-right: 14px; text-decoration: none; font-weight: bold; }
    .return-page table { width: 100%; border-collapse: collapse; margin-top: 14px; }
    .return-page th, .return-page td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 14px; }
    .return-page th { background: #f2f2f2; }
    .return-page .late { color: #c0392b; font-weight: bold; }
    .return-page .msg-success { background: #e8f8ec; padding: 10px; margin-top: 12px; }
    .return-page .msg-error { background: #fdecea; padding: 10px; margin-top: 12px; }
    .return-page .inline-form { display: flex; gap: 4px; flex-wrap: wrap; align-items: center; }
    .return-page .inline-form input[type=number] { width: 80px; }
</style>
<div class="return-page">
    <h2>Quản lý trả sách</h2>
    <nav>
        <a href="<?= e(url('/return')) ?>">Đang mượn</a>
        <a href="<?= e(url('/return/overdue')) ?>">Quá hạn</a>
        <a href="<?= e(url('/return/history')) ?>">Lịch sử trả</a>
    </nav>
    <?php if (!empty($flash)): ?>
        <div class="msg-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
