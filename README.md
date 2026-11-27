# UED Library Management

Hệ thống quản lý thư viện (PHP thuần, mô hình MVC, MySQL).

## Module đã có: Quản lý tài khoản (User) - phụ trách: Tấn Trình

Chức năng: đăng nhập, đăng ký, đăng xuất, phân quyền (admin / librarian / teacher / student),
quản lý thông tin tài khoản (admin sửa, khóa, xóa; sinh viên và giảng viên tự cập nhật).

## Cách chạy (XAMPP + VS Code)

1. Chép thư mục `UED-LIBRARY-MANAGEMENT` vào `C:\xampp\htdocs\`.
2. Mở XAMPP, bật **Apache** và **MySQL**.
3. Vào `http://localhost/phpmyadmin` -> tab **Import** -> chọn file `database.sql` -> **Import**
   (hoặc mở file trong MySQL Workbench rồi chạy toàn bộ).
4. Nếu MySQL của bạn có mật khẩu root, sửa trong `config/database.php`.
5. Mở `http://localhost/UED-LIBRARY-MANAGEMENT/`.

## Tài khoản mẫu

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị viên | admin@ued.edu.vn | Admin@123 |
| Thủ thư | thuthu@ued.edu.vn | Librarian@123 |

Sinh viên và giảng viên tự đăng ký tại trang `/register`.

## Cấu trúc

```
app/Models/        User, Student, Teacher (+ các Model của thành viên khác)
app/Controllers/   AuthController, AdminController, StudentController, TeacherController
app/Views/         auth, admin, student, teacher, librarian, layouts
config/database.php   kết nối CSDL (class Database)
routes/web.php        khai báo route
index.php             điểm vào, bộ định tuyến, hàm hỗ trợ
database.sql          tạo CSDL và dữ liệu mẫu
```

## Cho thành viên khác dùng chung phân quyền

Trong Controller của bạn, gọi ở đầu hàm:

```php
AuthController::requireRole(['admin', 'librarian']); // chỉ các vai trò này được vào
AuthController::requireLogin();                        // chỉ cần đã đăng nhập
```

Thêm Model/Controller mới vào đúng thư mục `app/Models`, `app/Controllers`: `index.php` tự nạp.
Thêm route mới vào `routes/web.php`.
