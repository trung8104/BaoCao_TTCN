<?php
// Header trang người xem. Biến dùng: $page_title, $active (home|posts|pests|calendar)
require_once __DIR__ . '/helpers.php';
$active = $active ?? '';
$nav = ['home' => ['Trang chủ', 'public/index.php'], 'posts' => ['Bài viết', 'public/posts.php'],
        'pests' => ['Tra cứu sâu bệnh', 'public/pests.php'], 'calendar' => ['Lịch canh tác', 'public/calendar.php']];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($page_title) ? e($page_title) . ' | ' : '' ?>Sổ tay canh tác cà chua</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark site-nav sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= url('public/index.php') ?>">🍅 Sổ tay cà chua</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-label="Mở menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($nav as $k => [$label, $href]): ?>
          <li class="nav-item"><a class="nav-link<?= $active === $k ? ' active' : '' ?>" href="<?= url($href) ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <form class="d-flex" role="search" action="<?= url('public/search.php') ?>" method="get">
        <input class="form-control me-2" type="search" name="q" placeholder="Tìm bài viết, sâu bệnh…" aria-label="Tìm kiếm" value="<?= e($_GET['q'] ?? '') ?>">
        <button class="btn btn-light" type="submit">Tìm</button>
      </form>
    </div>
  </div>
</nav>
<main class="container py-4">
<?= flash_render() ?>
