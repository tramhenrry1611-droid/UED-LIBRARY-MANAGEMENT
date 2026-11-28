<?php require __DIR__ . '/_menu.php'; ?>

    <p>Mức phạt quá hạn: <strong><?= money($finePerDay) ?>/ngày</strong>. Tiền phạt được tính tự động khi xác nhận trả sách.</p>

    <table>
        <tr>
            <th>Mã phiếu</th><th>Người mượn</th><th>Mã sách</th><th>Tên sách</th>
            <th>Hạn trả</th><th>Số ngày trễ</th><th>Tiền phạt tạm tính</th><th>Thao tác</th>
        </tr>
        <?php if (empty($overdueList)): ?>
            <tr><td colspan="8">Hiện không có phiếu mượn nào quá hạn.</td></tr>
        <?php endif; ?>

        <?php foreach ($overdueList as $o): ?>
            <tr>
                <td>#<?= e($o['id']) ?></td>
                <td><?= e($o['full_name']) ?></td>
                <td><?= e($o['book_code']) ?></td>
                <td><?= e($o['title']) ?></td>
                <td><?= e(date('d/m/Y', strtotime($o['due_date']))) ?></td>
                <td class="late"><?= e($o['days_late']) ?> ngày</td>
                <td class="late"><?= money($o['fine']) ?></td>
                <td>
                    <form method="post" action="<?= e(url('/return/confirm')) ?>" class="inline-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="borrow_id" value="<?= e($o['id']) ?>">
                        <input type="hidden" name="back" value="return/overdue">
                        <input type="hidden" name="book_condition" value="good">
                        <button type="submit" onclick="return confirm('Xác nhận trả và thu phạt phiếu #<?= e($o['id']) ?>?')">Trả sách &amp; thu phạt</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
