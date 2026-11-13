# Sổ tay hướng dẫn canh tác cà chua (PHP + MySQL)

Website thông tin về canh tác cà chua, gồm trang người xem và trang quản trị. Viết bằng PHP thuần (PDO) + MySQL, giao diện Bootstrap 5.

## Cài đặt (XAMPP)
1. Giải nén vào `C:\xampp\htdocs\tomato-guide` (đúng tên thư mục, nếu đổi tên thì sửa `base_url` trong `config/config.php`).
2. Bật **Apache** và **MySQL** trong XAMPP Control Panel.
3. Mở http://localhost/phpmyadmin → tab **Nhập (Import)** → chọn `database.sql` → Thực hiện.
4. (Tùy chọn) Import tiếp `sample_data.sql` để có 5 bài viết và 5 sâu bệnh mẫu chạy thử.
5. Sau khi import `database.sql`, đăng nhập quản trị bằng `admin` / `Tomato@123`. Với CSDL đã tồn tại, import lại `database.sql` một lần để đặt mật khẩu admin về mặc định. Xem `docs/admin-login.md`, rồi **xóa file `setup_admin.php`** sau cài đặt.
6. Xem website: http://localhost/tomato-guide/ — Quản trị: http://localhost/tomato-guide/admin/login.php

Cần internet khi chạy vì Bootstrap tải từ CDN. Mật khẩu MySQL mặc định của XAMPP là rỗng; nếu khác, sửa `config/config.php`.

## Chức năng
**Người xem:** trang chủ; danh sách bài viết có tìm kiếm, lọc theo chuyên mục và giai đoạn sinh trưởng, phân trang; trang chi tiết bài viết (ảnh, bài liên quan, đếm lượt xem); tra cứu sâu bệnh theo tên, loại và nhiều triệu chứng (xếp theo số triệu chứng khớp); tìm kiếm tổng hợp; lịch canh tác tự tính theo ngày gieo, tạo/cập nhật nhiều luống hoặc chậu cùng lúc (lưu trên trình duyệt).

**Quản trị:** đăng nhập/đăng xuất, phân quyền admin/editor; tổng quan thống kê; CRUD bài viết (nháp/đăng, ảnh đại diện, nhiều ảnh), sâu bệnh (gắn triệu chứng, ảnh), triệu chứng, chuyên mục, giai đoạn sinh trưởng; thư viện ảnh; quản lý tài khoản (chỉ admin).

## Cấu trúc thư mục
| Thư mục / file | Phụ trách | Nội dung |
|---|---|---|
| `database.sql`, `config/`, `includes/db.php`, nhánh `main` | Trung | CSDL (9 bảng), kết nối, cấu hình |
| `admin/`, `includes/auth.php`, `includes/simple_crud.php`, `includes/admin_*.php`, `uploads/` | Dũng | Trang quản trị, đăng nhập, upload |
| `public/` (trừ giao diện trang chủ), `includes/functions_public.php` | Quang Anh | Trang người xem, truy vấn, tìm kiếm |
| `assets/`, `includes/header.php`, `includes/footer.php`, `public/calendar.php` | Vũ | CSS, JS, layout, lịch canh tác |
| `docs/`, `sample_data.sql` | Giang | Báo cáo, test case, nội dung, ERD (`docs/erd.mmd`) |
| `includes/helpers.php` | Cả nhóm | Hàm dùng chung (CSRF, slug, upload, phân trang) |

## Cách nhập nội dung bài viết
Trong ô Nội dung: dòng bắt đầu bằng `## ` là tiêu đề mục; dòng bắt đầu bằng `- ` là gạch đầu dòng; để trống một dòng để xuống đoạn mới. Nội dung luôn được escape, không chèn được HTML hay script.

## Bảo mật đã áp dụng
- PDO prepared statement cho mọi truy vấn (chống SQL injection); từ khóa LIKE được escape ký tự đặc biệt.
- `htmlspecialchars` khi in dữ liệu ra HTML (chống XSS); nội dung bài viết qua `render_content()`.
- CSRF token cho mọi form POST; mật khẩu băm bằng `password_hash`; `session_regenerate_id` khi đăng nhập; khóa 5 phút sau 5 lần đăng nhập sai.
- Upload ảnh: kiểm tra nội dung thật bằng `getimagesize` (không tin phần mở rộng), giới hạn dung lượng, đổi tên ngẫu nhiên; `uploads/.htaccess` chặn chạy PHP.

## Quy ước làm việc nhóm
- Tên bảng, cột: tiếng Anh, `snake_case`. Chỉ Trung được đổi cấu trúc bảng; cần thêm cột thì báo Trung.
- Mỗi người một nhánh: `dung-admin`, `quanganh-public`, `vu-ui`. Chỉ Trung gộp vào `main`.
- Không commit `config/config.php` và ảnh trong `uploads/`. Đẩy code lên GitHub cuối mỗi buổi làm.

## Lưu ý
- `sample_data.sql` chỉ là nội dung khái quát để minh họa. Giang cần thay bằng tài liệu có nguồn rõ ràng trước khi nộp báo cáo.
- Khi triển khai lên hosting: đổi `base_url`, `db_*` trong `config/config.php`, xóa `setup_admin.php`, đổi mật khẩu admin.
