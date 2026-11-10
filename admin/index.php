<?php
require_once __DIR__ . '/../includes/admin_header.php';
$page_title = 'Tổng quan'; $active = 'dash';
$stats = [
  'Bài viết đã đăng' => q("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn(),
  'Bài nháp' => q("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn(),
  'Sâu bệnh' => q("SELECT COUNT(*) FROM pests")->fetchColumn(),
  'Triệu chứng' => q("SELECT COUNT(*) FROM symptoms")->fetchColumn(),
  'Ảnh' => q("SELECT COUNT(*) FROM images")->fetchColumn(),
  'Lượt xem bài viết' => q("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn(),
];
$top = q("SELECT id, title, views FROM posts WHERE status='published' ORDER BY views DESC, id DESC LIMIT 5")->fetchAll();
$recent = q("SELECT id, title, status, updated_at FROM posts ORDER BY updated_at DESC LIMIT 5")->fetchAll();
?>
<h1 class="h3 mb-4">Tổng quan</h1>
<div class="row g-3 mb-4">
  <?php foreach ($stats as $label => $n): ?>
    <div class="col-6 col-md-4 col-xl-2"><div class="card stat-card"><div class="card-body">
      <div class="num"><?= (int)$n ?></div><div class="small text-muted"><?= e($label) ?></div></div></div></div>
  <?php endforeach; ?>
</div>
<div class="d-flex flex-wrap gap-2 mb-4">
  <a class="btn btn-danger" href="<?= url('admin/post_edit.php') ?>">+ Thêm bài viết</a>
  <a class="btn btn-outline-danger" href="<?= url('admin/pest_edit.php') ?>">+ Thêm sâu bệnh</a>
</div>
<div class="row g-4">
  <div class="col-lg-6"><div class="card"><div class="card-header fw-semibold">Bài viết xem nhiều</div>
    <ul class="list-group list-group-flush">
      <?php if (!$top): ?><li class="list-group-item text-muted">Chưa có dữ liệu.</li><?php endif; ?>
      <?php foreach ($top as $r): ?><li class="list-group-item d-flex justify-content-between">
        <a href="<?= url('admin/post_edit.php?id=' . (int)$r['id']) ?>"><?= e($r['title']) ?></a><span class="badge text-bg-secondary"><?= (int)$r['views'] ?></span></li><?php endforeach; ?>
    </ul></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-header fw-semibold">Cập nhật gần đây</div>
    <ul class="list-group list-group-flush">
      <?php if (!$recent): ?><li class="list-group-item text-muted">Chưa có bài viết.</li><?php endif; ?>
      <?php foreach ($recent as $r): ?><li class="list-group-item d-flex justify-content-between">
        <a href="<?= url('admin/post_edit.php?id=' . (int)$r['id']) ?>"><?= e($r['title']) ?></a>
        <span class="small text-muted"><?= $r['status'] === 'draft' ? 'Nháp · ' : '' ?><?= fmt_date($r['updated_at']) ?></span></li><?php endforeach; ?>
    </ul></div></div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
