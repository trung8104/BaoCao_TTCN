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
<h1 class="h3 mb-3">Kết quả tìm kiếm<?= $qs !== '' ? ': “' . e($qs) . '”' : '' ?></h1>
<?php if ($qs === ''): ?>
  <div class="alert alert-light border">Nhập từ khóa vào ô tìm kiếm phía trên.</div>
<?php else: ?>
  <h2 class="h5 mt-4">Bài viết (<?= count($posts) ?>)</h2>
  <?php if (!$posts): ?><p class="text-muted">Không có bài viết phù hợp.</p><?php endif; ?>
  <div class="row g-4 mb-4"><?php foreach ($posts as $p) include __DIR__ . '/_card.php'; ?></div>
  <h2 class="h5 mt-4">Sâu bệnh (<?= count($pests) ?>)</h2>
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
