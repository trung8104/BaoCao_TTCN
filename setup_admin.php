<?php
// Chạy 1 lần: php setup_admin.php  (hoặc mở trên trình duyệt), rồi XÓA file này.
require_once __DIR__ . '/includes/db.php';
header('Content-Type: text/plain; charset=utf-8');
$role = db()->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
if (!$role) { exit("Chưa import database.sql (thiếu bảng roles).\n"); }
$username = 'admin';
if (q("SELECT id FROM users WHERE username = ?", [$username])->fetch()) {
    exit("Tai khoan admin da ton tai; mat khau hien tai duoc giu nguyen.\n");
}
$password = 'Tomato@123';
$st = db()->prepare("INSERT INTO users (role_id, username, password_hash, full_name) VALUES (?,?,?,?)");
$st->execute([$role, $username, password_hash($password, PASSWORD_DEFAULT), 'Quản trị viên']);
echo "Created account: $username / $password\nRemove setup_admin.php after setup.\n";
