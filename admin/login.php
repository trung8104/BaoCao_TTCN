<?php
require_once __DIR__ . '/../includes/auth.php';
if (is_logged_in()) redirect('admin/index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $error = attempt_login($_POST['username'] ?? '', $_POST['password'] ?? '');
    if ($error === null) redirect('admin/index.php');
}
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập quản trị</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>"></head>
<body class="admin-body d-flex align-items-center" style="min-height:100vh">
<div class="container" style="max-width:420px">
  <div class="card shadow-sm"><div class="card-body p-4">
    <h1 class="h4 text-center mb-4">🍅 Đăng nhập quản trị</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off"><?= csrf_field() ?>
      <div class="mb-3"><label class="form-label" for="username">Tên đăng nhập</label>
        <input class="form-control" id="username" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label" for="password">Mật khẩu</label>
        <input class="form-control" id="password" type="password" name="password" required></div>
      <div class="d-grid"><button class="btn btn-danger">Đăng nhập</button></div>
    </form>
    <div class="text-center mt-3"><a href="<?= url('public/index.php') ?>" class="small">← Về trang chủ</a></div>
  </div></div>
</div></body></html>
