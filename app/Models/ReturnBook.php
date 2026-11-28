<?php
/**
 * Model ReturnBook - Mô-đun Quản lý trả sách
 * Người phụ trách: Hoài Linh
 *
 * Chức năng:
 *  1. Xác nhận trả sách      -> confirmReturn()
 *  2. Quản lý hạn trả        -> getBorrowing(), extendDueDate()
 *  3. Xử lý quá hạn          -> getOverdue(), calculateFine()
 */
class ReturnBook
{
    private PDO $db;

    public const FINE_PER_DAY = 2000; // tiền phạt mỗi ngày trễ (VNĐ)
    public const EXTEND_DAYS  = 7;    // mỗi lần gia hạn thêm 7 ngày
    public const MAX_RENEW    = 1;    // tối đa 1 lần gia hạn

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** Danh sách phiếu đang mượn (tìm theo người mượn / tên sách / mã phiếu) */
    public function getBorrowing(string $keyword = ''): array
    {
        $sql = "SELECT br.id, br.borrow_date, br.due_date, br.renew_count,
                       u.full_name, bk.book_code, bk.title,
                       DATEDIFF(CURDATE(), br.due_date) AS days_late
                FROM borrows br
                JOIN users u  ON u.id  = br.user_id
                JOIN books bk ON bk.id = br.book_id
                WHERE br.status = 'borrowing'";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (u.full_name LIKE :kw1 OR bk.title LIKE :kw2 OR br.id = :id)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
            $params[':id']  = (int) $keyword;
        }
        $sql .= " ORDER BY br.due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Danh sách phiếu quá hạn kèm tiền phạt tạm tính */
    public function getOverdue(): array
    {
        $sql = "SELECT br.id, br.borrow_date, br.due_date,
                       u.full_name, bk.book_code, bk.title,
                       DATEDIFF(CURDATE(), br.due_date) AS days_late
                FROM borrows br
                JOIN users u  ON u.id  = br.user_id
                JOIN books bk ON bk.id = br.book_id
                WHERE br.status = 'borrowing' AND br.due_date < CURDATE()
                ORDER BY days_late DESC";
        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['fine'] = $this->calculateFine((int) $row['days_late']);
        }
        return $rows;
    }

    /** Lịch sử trả sách */
    public function getHistory(): array
    {
        $sql = "SELECT rb.id, rb.return_date, rb.book_condition, rb.days_late,
                       rb.fine_amount, rb.note,
                       br.id AS borrow_id, u.full_name, bk.book_code, bk.title,
                       st.full_name AS staff_name
                FROM return_books rb
                JOIN borrows br ON br.id = rb.borrow_id
                JOIN users u    ON u.id  = br.user_id
                JOIN books bk   ON bk.id = br.book_id
                JOIN users st   ON st.id = rb.confirmed_by
                ORDER BY rb.return_date DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Tính tiền phạt quá hạn */
    public function calculateFine(int $daysLate): int
    {
        return max(0, $daysLate) * self::FINE_PER_DAY;
    }

    /**
     * Xác nhận trả sách (dùng transaction để dữ liệu luôn nhất quán)
     * @return array [bool $success, string $message]
     */
    public function confirmReturn(int $borrowId, int $staffId, string $condition, int $extraFee = 0, ?string $note = null): array
    {
        if (!in_array($condition, ['good', 'damaged', 'lost'], true)) {
            return [false, 'Tình trạng sách không hợp lệ.'];
        }

        try {
            $this->db->beginTransaction();

            // Khóa dòng để tránh 2 thủ thư xác nhận trùng 1 phiếu
            $stmt = $this->db->prepare(
                "SELECT id, book_id, due_date, status FROM borrows WHERE id = ? FOR UPDATE"
            );
            $stmt->execute([$borrowId]);
            $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$borrow || $borrow['status'] !== 'borrowing') {
                $this->db->rollBack();
                return [false, 'Phiếu mượn không tồn tại hoặc đã được trả.'];
            }

            $due      = new DateTime($borrow['due_date']);
            $today    = new DateTime('today');
            $daysLate = $today > $due ? (int) $due->diff($today)->days : 0;
            $fine     = $this->calculateFine($daysLate) + max(0, $extraFee);

            // 1. Lưu phiếu trả
            $this->db->prepare(
                "INSERT INTO return_books (borrow_id, book_condition, days_late, fine_amount, note, confirmed_by)
                 VALUES (?, ?, ?, ?, ?, ?)"
            )->execute([$borrowId, $condition, $daysLate, $fine, $note, $staffId]);

            // 2. Cập nhật trạng thái phiếu mượn
            $this->db->prepare("UPDATE borrows SET status = 'returned' WHERE id = ?")
                     ->execute([$borrowId]);

            // 3. Cập nhật số lượng sách trong bảng books của nhóm
            //    (sách còn lại = quantity - borrowed_quantity)
            if ($condition === 'lost') {
                // Mất sách: giảm cả số đang mượn lẫn tổng số sách
                $this->db->prepare(
                    "UPDATE books
                     SET borrowed_quantity = GREATEST(borrowed_quantity - 1, 0),
                         quantity = GREATEST(quantity - 1, 0)
                     WHERE id = ?"
                )->execute([$borrow['book_id']]);
            } else {
                // Trả bình thường: giảm số đang mượn đi 1
                $this->db->prepare(
                    "UPDATE books SET borrowed_quantity = GREATEST(borrowed_quantity - 1, 0) WHERE id = ?"
                )->execute([$borrow['book_id']]);
            }

            $this->db->commit();

            $msg = 'Xác nhận trả sách thành công.';
            if ($fine > 0) {
                $msg .= ' Tiền phạt: ' . number_format($fine, 0, ',', '.') . ' đ.';
            }
            return [true, $msg];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return [false, 'Có lỗi xảy ra khi xác nhận trả sách.'];
        }
    }

    /** Gia hạn ngày trả (chỉ khi chưa quá hạn và chưa vượt số lần gia hạn) */
    public function extendDueDate(int $borrowId): array
    {
        $stmt = $this->db->prepare(
            "UPDATE borrows
             SET due_date = DATE_ADD(due_date, INTERVAL :days DAY),
                 renew_count = renew_count + 1
             WHERE id = :id AND status = 'borrowing'
               AND renew_count < :max AND due_date >= CURDATE()"
        );
        $stmt->execute([
            ':days' => self::EXTEND_DAYS,
            ':id'   => $borrowId,
            ':max'  => self::MAX_RENEW,
        ]);

        return $stmt->rowCount() > 0
            ? [true, 'Gia hạn thành công thêm ' . self::EXTEND_DAYS . ' ngày.']
            : [false, 'Không thể gia hạn (đã quá hạn, đã gia hạn tối đa hoặc phiếu không hợp lệ).'];
    }
}
