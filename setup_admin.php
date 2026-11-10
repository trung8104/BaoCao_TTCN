<?php
// Chạy 1 lần: php setup_admin.php  (hoặc mở trên trình duyệt), rồi XÓA file này.
require_once __DIR__ . '/includes/db.php';
header('Content-Type: text/plain; charset=utf-8');
$role = db()->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
if (!$role) { exit("Chưa import database.sql (thiếu bảng roles).\n"); }
if (db()->query("SELECT COUNT(*) FROM users")->fetchColumn() > 0) { exit("Đã có tài khoản trong hệ thống. Xóa file setup_admin.php.\n"); }
$username = 'admin';
$password = bin2hex(random_bytes(5));   // mật khẩu ngẫu nhiên, in ra 1 lần
$st = db()->prepare("INSERT INTO users (role_id, username, password_hash, full_name) VALUES (?,?,?,?)");
$st->execute([$role, $username, password_hash($password, PASSWORD_DEFAULT), 'Quản trị viên']);
echo "Đã tạo tài khoản: $username / $password\nHãy đổi mật khẩu ngay và xóa file setup_admin.php.\n";
