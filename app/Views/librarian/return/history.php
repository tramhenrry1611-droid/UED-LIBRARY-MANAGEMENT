<?php require __DIR__ . '/_menu.php'; ?>

    <?php
    $conditionText = ['good' => 'Nguyên vẹn', 'damaged' => 'Hư hỏng', 'lost' => 'Mất sách'];
    ?>
    <table>
        <tr>
            <th>Mã phiếu</th><th>Người mượn</th><th>Mã sách</th><th>Tên sách</th><th>Ngày trả</th>
            <th>Tình trạng</th><th>Ngày trễ</th><th>Tiền phạt</th><th>Thủ thư xác nhận</th><th>Ghi chú</th>
        </tr>
        <?php if (empty($history)): ?>
            <tr><td colspan="10">Chưa có lịch sử trả sách.</td></tr>
        <?php endif; ?>

        <?php foreach ($history as $h): ?>
            <tr>
                <td>#<?= e($h['borrow_id']) ?></td>
                <td><?= e($h['full_name']) ?></td>
                <td><?= e($h['book_code']) ?></td>
                <td><?= e($h['title']) ?></td>
                <td><?= e(date('d/m/Y H:i', strtotime($h['return_date']))) ?></td>
                <td><?= e($conditionText[$h['book_condition']] ?? $h['book_condition']) ?></td>
                <td class="<?= $h['days_late'] > 0 ? 'late' : '' ?>"><?= e($h['days_late']) ?></td>
                <td><?= money($h['fine_amount']) ?></td>
                <td><?= e($h['staff_name']) ?></td>
                <td><?= e($h['note']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
