<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$me = current_user();
$roles = q("SELECT id, name FROM roles ORDER BY id")->fetchAll();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $un = trim($_POST['username'] ?? ''); $fn = trim($_POST['full_name'] ?? ''); $em = trim($_POST['email'] ?? '');
        $pw = $_POST['password'] ?? ''; $rid = (int)($_POST['role_id'] ?? 0);
        if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $un)) $errors[] = 'Tên đăng nhập 3–50 ký tự, chỉ gồm chữ, số, dấu _ và dấu chấm.';
        if ($fn === '') $errors[] = 'Họ tên không được để trống.';
        if ($em !== '' && !filter_var($em, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
        if (strlen($pw) < 8) $errors[] = 'Mật khẩu tối thiểu 8 ký tự.';
        if (!q("SELECT id FROM roles WHERE id = ?", [$rid])->fetch()) $errors[] = 'Vai trò không hợp lệ.';
        if (!$errors && q("SELECT id FROM users WHERE username = ?", [$un])->fetch()) $errors[] = 'Tên đăng nhập đã tồn tại.';
        if (!$errors && $em !== '' && q("SELECT id FROM users WHERE email = ?", [$em])->fetch()) $errors[] = 'Email đã được dùng.';
        if (!$errors) {
            q("INSERT INTO users (role_id, username, password_hash, full_name, email) VALUES (?,?,?,?,?)", [$rid, $un, password_hash($pw, PASSWORD_DEFAULT), $fn, $em ?: null]);
            flash('success', 'Đã tạo tài khoản ' . $un . '.'); redirect('admin/users.php');
        }
    } elseif ($action === 'toggle' || $action === 'delete' || $action === 'reset') {
        $id = (int)$_POST['id'];
        if ($id === (int)$me['id'] && $action !== 'reset') { flash('error', 'Không thể khóa hoặc xóa chính tài khoản đang đăng nhập.'); redirect('admin/users.php'); }
        if ($action === 'toggle') { q("UPDATE users SET is_active = 1 - is_active WHERE id = ?", [$id]); flash('success', 'Đã đổi trạng thái tài khoản.'); }
        elseif ($action === 'delete') { q("DELETE FROM users WHERE id = ?", [$id]); flash('success', 'Đã xóa tài khoản.'); }
        else {
            $pw = $_POST['new_password'] ?? '';
            if (strlen($pw) < 8) flash('error', 'Mật khẩu mới tối thiểu 8 ký tự.');
            else { q("UPDATE users SET password_hash = ? WHERE id = ?", [password_hash($pw, PASSWORD_DEFAULT), $id]); flash('success', 'Đã đặt lại mật khẩu.'); }
        }
        redirect('admin/users.php');
    }
}
$users = q("SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id")->fetchAll();
$page_title = 'Tài khoản'; $active = 'users';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1 class="h3 mb-3">Tài khoản quản trị</h1>
<div class="row g-4">
  <div class="col-lg-4"><div class="card"><div class="card-header fw-semibold">Thêm tài khoản</div><div class="card-body">
    <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" autocomplete="off"><?= csrf_field() ?><input type="hidden" name="action" value="create">
      <div class="mb-3"><label class="form-label" for="un">Tên đăng nhập *</label><input class="form-control" id="un" name="username" required></div>
      <div class="mb-3"><label class="form-label" for="fn">Họ tên *</label><input class="form-control" id="fn" name="full_name" required></div>
      <div class="mb-3"><label class="form-label" for="em">Email</label><input class="form-control" id="em" type="email" name="email"></div>
      <div class="mb-3"><label class="form-label" for="pw">Mật khẩu * (tối thiểu 8 ký tự)</label><input class="form-control" id="pw" type="password" name="password" minlength="8" required></div>
      <div class="mb-3"><label class="form-label" for="rl">Vai trò</label><select class="form-select" id="rl" name="role_id">
        <?php foreach ($roles as $r): ?><option value="<?= (int)$r['id'] ?>" <?= $r['name'] === 'editor' ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></div>
      <button class="btn btn-danger">Tạo tài khoản</button>
    </form></div></div></div>
  <div class="col-lg-8"><div class="table-responsive card"><table class="table table-admin mb-0">
    <thead><tr><th>Tài khoản</th><th>Họ tên</th><th>Vai trò</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    <?php foreach ($users as $x): ?><tr>
      <td class="fw-semibold"><?= e($x['username']) ?></td><td><?= e($x['full_name']) ?></td><td><?= e($x['role_name']) ?></td>
      <td><span class="badge text-bg-<?= $x['is_active'] ? 'success' : 'secondary' ?>"><?= $x['is_active'] ? 'Hoạt động' : 'Đã khóa' ?></span></td>
      <td class="text-end text-nowrap">
        <?php if ((int)$x['id'] !== (int)$me['id']): ?>
          <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="btn btn-sm btn-outline-secondary"><?= $x['is_active'] ? 'Khóa' : 'Mở khóa' ?></button></form>
          <form method="post" class="d-inline" data-confirm="Xóa tài khoản này?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="btn btn-sm btn-outline-danger">Xóa</button></form>
        <?php endif; ?>
        <form method="post" class="d-inline-flex gap-1 mt-1"><?= csrf_field() ?><input type="hidden" name="action" value="reset"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
          <input class="form-control form-control-sm" style="width:130px" type="password" name="new_password" placeholder="Mật khẩu mới" minlength="8" required autocomplete="new-password">
          <button class="btn btn-sm btn-outline-primary">Đặt lại</button></form>
      </td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
