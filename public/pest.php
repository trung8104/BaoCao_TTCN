<?php
require_once __DIR__ . '/../includes/functions_public.php';
$pest = get_pest_by_slug($_GET['slug'] ?? '');
if (!$pest) { http_response_code(404); $page_title = 'Không tìm thấy'; require __DIR__ . '/../includes/header.php';
  echo '<div class="alert alert-warning">Không tìm thấy thông tin. <a href="' . e(url('public/pests.php')) . '">Về trang tra cứu</a></div>';
  require __DIR__ . '/../includes/footer.php'; exit; }
$symptoms = get_pest_symptoms($pest['id']);
$images = get_pest_images($pest['id']);
$page_title = $pest['name']; $active = 'pests';
$sections = ['description' => 'Mô tả', 'cause' => 'Nguyên nhân', 'prevention' => 'Biện pháp phòng', 'treatment' => 'Biện pháp trừ'];
require __DIR__ . '/../includes/header.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb small">
  <li class="breadcrumb-item"><a href="<?= url('public/index.php') ?>">Trang chủ</a></li>
  <li class="breadcrumb-item"><a href="<?= url('public/pests.php') ?>">Tra cứu sâu bệnh</a></li>
  <li class="breadcrumb-item active"><?= e($pest['name']) ?></li>
</ol></nav>
<div class="row"><div class="col-lg-8">
  <span class="badge <?= $pest['type'] === 'pest' ? 'badge-pest' : 'badge-disease' ?>"><?= e(pest_type_label($pest['type'])) ?></span>
  <h1 class="h2 mt-2"><?= e($pest['name']) ?></h1>
  <?php if ($images): ?><div class="row g-2 gallery mb-3">
    <?php foreach ($images as $im): ?><div class="col-6 col-md-4"><a href="<?= e(upload_url($im['file_path'])) ?>" target="_blank"><img src="<?= e(upload_url($im['file_path'])) ?>" alt="<?= e($im['alt_text'] ?: $pest['name']) ?>" loading="lazy"></a></div><?php endforeach; ?>
  </div><?php endif; ?>
  <?php if ($symptoms): ?>
    <h2 class="h5">Triệu chứng nhận biết</h2>
    <ul><?php foreach ($symptoms as $s): ?><li><?= e($s['name']) ?> <span class="text-muted small">(<?= e(body_part_label($s['body_part'])) ?>)</span></li><?php endforeach; ?></ul>
  <?php endif; ?>
  <div class="article-content">
    <?php foreach ($sections as $k => $label): if (trim((string)$pest[$k]) === '') continue; ?>
      <h3><?= e($label) ?></h3><?= render_content($pest[$k]) ?>
    <?php endforeach; ?>
  </div>
  <?php if ($pest['source']): ?><p class="small text-muted mt-4 border-top pt-3"><strong>Nguồn tham khảo:</strong> <?= e($pest['source']) ?></p><?php endif; ?>
  <div class="alert alert-warning small mt-3">Khi cần dùng thuốc bảo vệ thực vật, hãy làm theo hướng dẫn trên nhãn và tư vấn của cán bộ khuyến nông hoặc bảo vệ thực vật địa phương.</div>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
