<?php
// Đăng nhập, phân quyền (roles: admin, editor)
require_once __DIR__ . '/helpers.php';

function current_user() {
    static $u = false;
    if ($u === false) {
        $u = null;
        if (!empty($_SESSION['user_id'])) {
            $u = q("SELECT u.id, u.username, u.full_name, u.is_active, r.name AS role
                    FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?", [$_SESSION['user_id']])->fetch() ?: null;
            if ($u && !$u['is_active']) $u = null;
        }
    }
    return $u;
}
function is_logged_in() { return current_user() !== null; }
function is_admin() { $u = current_user(); return $u && $u['role'] === 'admin'; }
function require_login() { if (!is_logged_in()) redirect('admin/login.php'); }
function require_admin() {
    require_login();
    if (!is_admin()) { flash('error', 'Chỉ quản trị viên mới được vào trang này.'); redirect('admin/index.php'); }
}

// Trả về chuỗi lỗi hoặc null nếu thành công. Khóa 5 phút sau 5 lần sai.
function attempt_login($username, $password) {
    $now = time();
    if (($_SESSION['lock_until'] ?? 0) > $now) {
        return 'Đăng nhập sai quá nhiều lần. Thử lại sau ' . ceil((($_SESSION['lock_until'] - $now)) / 60) . ' phút.';
    }
    $u = q("SELECT id, password_hash, is_active FROM users WHERE username = ?", [trim($username)])->fetch();
    if ($u && $u['is_active'] && password_verify($password, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];
        unset($_SESSION['fails'], $_SESSION['lock_until']);
        return null;
    }
    $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
    if ($_SESSION['fails'] >= 5) { $_SESSION['lock_until'] = $now + 300; $_SESSION['fails'] = 0; }
    return 'Sai tên đăng nhập hoặc mật khẩu.';
}
function do_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
