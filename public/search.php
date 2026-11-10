<?php
require_once __DIR__ . '/../includes/functions_public.php';
$page_title = 'Tìm kiếm';
$qs = trim($_GET['q'] ?? '');
$posts = $pests = [];
if ($qs !== '') {
    [$posts] = get_posts(['q' => $qs], 1, 12);
    [$pests] = search_pests(['q' => $qs]);
}
require __DIR__ . '/../includes/header.php';
?>
<div class="calendar-intro">
  <span class="eyebrow">TÌM TRONG CẨM NANG CÀ CHUA</span>
  <h1>Kết quả tìm kiếm<?= $qs !== '' ? ': “' . e($qs) . '”' : '' ?></h1>
  <p>Tìm trong bài hướng dẫn, hồ sơ sâu bệnh, nguyên nhân xử lý và triệu chứng trên cây.</p>
</div>
<?php if ($qs === ''): ?>
  <div class="empty-library"><span>⌕</span><div><h3>Bắt đầu với một dấu hiệu hoặc công việc trong vườn</h3><p>Ví dụ: lá vàng, mốc sương, tưới nước, bón phân hoặc tỉa cành.</p></div><a href="<?= url('public/pests.php') ?>">Tra cứu theo triệu chứng →</a></div>
<?php else: ?>
  <div class="d-flex align-items-center justify-content-between mb-2"><h2 class="h5 mb-0">Bài hướng dẫn</h2><span class="small text-muted"><?= count($posts) ?> kết quả gần nhất</span></div>
  <?php if (!$posts): ?><p class="text-muted">Không có bài viết phù hợp.</p><?php endif; ?>
  <div class="row g-4 mb-4"><?php foreach ($posts as $p) include __DIR__ . '/_card.php'; ?></div>
  <div class="d-flex align-items-center justify-content-between mt-4 mb-2"><h2 class="h5 mb-0">Sâu bệnh và dấu hiệu</h2><span class="small text-muted"><?= count($pests) ?> hồ sơ</span></div>
  <?php if (!$pests): ?><p class="text-muted">Không có sâu bệnh phù hợp.</p><?php endif; ?>
  <div class="list-group mb-4">
    <?php foreach ($pests as $p): ?>
      <a class="list-group-item list-group-item-action" href="<?= url('public/pest.php?slug=' . rawurlencode($p['slug'])) ?>">
        <span class="badge <?= $p['type'] === 'pest' ? 'badge-pest' : 'badge-disease' ?>"><?= e(pest_type_label($p['type'])) ?></span>
        <strong class="ms-1"><?= e($p['name']) ?></strong>
        <div class="small text-muted"><?= e(excerpt($p['description'] ?? '', 120)) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
