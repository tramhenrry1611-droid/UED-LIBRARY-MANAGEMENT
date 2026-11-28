<?php require __DIR__ . '/_menu.php'; ?>

    <form method="get" action="<?= e(url('/return')) ?>" class="inline-form" style="margin-top:14px">
        <input type="text" name="keyword" value="<?= e($keyword) ?>" placeholder="Tìm theo mã phiếu, người mượn, tên sách...">
        <button type="submit">Tìm</button>
    </form>

    <table>
        <tr>
            <th>Mã phiếu</th><th>Người mượn</th><th>Mã sách</th><th>Tên sách</th>
            <th>Ngày mượn</th><th>Hạn trả</th><th>Tình trạng hạn</th><th>Thao tác</th>
        </tr>
        <?php if (empty($borrows)): ?>
            <tr><td colspan="8">Không có phiếu mượn nào đang chờ trả.</td></tr>
        <?php endif; ?>

        <?php foreach ($borrows as $b): ?>
            <?php $late = (int) $b['days_late']; ?>
            <tr>
                <td>#<?= e($b['id']) ?></td>
                <td><?= e($b['full_name']) ?></td>
                <td><?= e($b['book_code']) ?></td>
                <td><?= e($b['title']) ?></td>
                <td><?= e(date('d/m/Y', strtotime($b['borrow_date']))) ?></td>
                <td><?= e(date('d/m/Y', strtotime($b['due_date']))) ?></td>
                <td>
                    <?php if ($late > 0): ?>
                        <span class="late">Quá hạn <?= $late ?> ngày</span>
                    <?php else: ?>
                        Còn <?= abs($late) ?> ngày
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Xác nhận trả sách -->
                    <form method="post" action="<?= e(url('/return/confirm')) ?>" class="inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="borrow_id" value="<?= e($b['id']) ?>">
                        <input type="hidden" name="back" value="return">
                        <select name="book_condition">
                            <option value="good">Nguyên vẹn</option>
                            <option value="damaged">Hư hỏng</option>
                            <option value="lost">Mất sách</option>
                        </select>
                        <input type="number" name="extra_fee" min="0" step="1000" value="0" title="Phí bồi thường thêm (nếu có)">
                        <input type="text" name="note" placeholder="Ghi chú" maxlength="255">
                        <button type="submit" onclick="return confirm('Xác nhận trả phiếu #<?= e($b['id']) ?>?')">Xác nhận trả</button>
                    </form>

                    <!-- Gia hạn -->
                    <?php if ($late <= 0 && (int) $b['renew_count'] < $maxRenew): ?>
                        <form method="post" action="<?= e(url('/return/extend')) ?>" style="margin-top:4px">
                            <?= csrfField() ?>
                            <input type="hidden" name="borrow_id" value="<?= e($b['id']) ?>">
                            <button type="submit">Gia hạn +7 ngày</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
