<?php
// Header khu quản trị. Biến dùng: $page_title, $active
require_once __DIR__ . '/auth.php';
require_login();
$u = current_user();
$active = $active ?? '';
$menu = ['dash' => ['Tổng quan', 'admin/index.php'], 'posts' => ['Bài viết', 'admin/posts.php'],
         'pests' => ['Sâu bệnh', 'admin/pests.php'], 'symptoms' => ['Triệu chứng', 'admin/symptoms.php'],
         'categories' => ['Chuyên mục', 'admin/categories.php'], 'stages' => ['Giai đoạn', 'admin/stages.php'],
         'images' => ['Thư viện ảnh', 'admin/images.php']];
if (is_admin()) $menu['users'] = ['Tài khoản', 'admin/users.php'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($page_title) ? e($page_title) . ' | ' : '' ?>Quản trị</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="admin-body">
<nav class="navbar navbar-expand-lg navbar-dark admin-nav">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= url('admin/index.php') ?>">⚙️ Quản trị</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-label="Mở menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto">
        <?php foreach ($menu as $k => [$label, $href]): ?>
          <li class="nav-item"><a class="nav-link<?= $active === $k ? ' active' : '' ?>" href="<?= url($href) ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <span class="navbar-text me-3 small"><?= e($u['full_name']) ?> (<?= e($u['role']) ?>)</span>
      <a class="btn btn-sm btn-outline-light me-2" href="<?= url('public/index.php') ?>" target="_blank">Xem website</a>
      <form method="post" action="<?= url('admin/logout.php') ?>" class="d-inline"><?= csrf_field() ?>
        <button class="btn btn-sm btn-light">Đăng xuất</button></form>
    </div>
  </div>
</nav>
<main class="container-fluid py-4 px-lg-4">
<?= flash_render() ?>
