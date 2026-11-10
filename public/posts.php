<?php
require_once __DIR__ . '/../includes/functions_public.php';
$page_title = 'Bài viết'; $active = 'posts';
$f = ['q' => trim($_GET['q'] ?? ''), 'category' => (int)($_GET['category'] ?? 0), 'stage' => (int)($_GET['stage'] ?? 0)];
$page = max(1, (int)($_GET['page'] ?? 1));
[$posts, $total] = get_posts($f, $page);
$pages = max(1, (int)ceil($total / POSTS_PER_PAGE));
if ($page > $pages) { $page = $pages; [$posts, $total] = get_posts($f, $page); }
$cats = get_categories(); $stages = get_stages();
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-3">Bài viết hướng dẫn</h1>
<form method="get" class="row g-2 mb-4">
  <div class="col-12 col-md-4"><input class="form-control" type="search" name="q" placeholder="Từ khóa…" value="<?= e($f['q']) ?>"></div>
  <div class="col-6 col-md-3">
    <select class="form-select" name="category" aria-label="Chuyên mục">
      <option value="0">Tất cả chuyên mục</option>
      <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $f['category'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-3">
    <select class="form-select" name="stage" aria-label="Giai đoạn">
      <option value="0">Mọi giai đoạn</option>
      <?php foreach ($stages as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $f['stage'] == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 col-md-2 d-grid"><button class="btn btn-danger">Lọc</button></div>
</form>
<p class="text-muted small"><?= $total ?> bài viết<?= ($f['q'] || $f['category'] || $f['stage']) ? ' phù hợp' : '' ?>.
  <?php if ($f['q'] || $f['category'] || $f['stage']): ?><a href="<?= url('public/posts.php') ?>">Xóa bộ lọc</a><?php endif; ?></p>
<?php if (!$posts): ?><div class="alert alert-light border">Không tìm thấy bài viết nào.</div><?php endif; ?>
<div class="row g-4 mb-4"><?php foreach ($posts as $p) include __DIR__ . '/_card.php'; ?></div>
<?= render_pagination($page, $pages, ['q' => $f['q'], 'category' => $f['category'] ?: '', 'stage' => $f['stage'] ?: '']) ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
