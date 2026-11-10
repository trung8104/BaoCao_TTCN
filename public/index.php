<?php
require_once __DIR__ . '/../includes/functions_public.php';
$page_title = 'Trang chủ'; $active = 'home';
[$latest] = get_posts([], 1, 6);
$cats = get_categories();
$stages = get_stages();
$pests = q("SELECT name, slug, type FROM pests ORDER BY created_at DESC LIMIT 5")->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<section class="hero mb-5">
  <h1 class="display-6">Sổ tay hướng dẫn canh tác cà chua</h1>
  <p class="lead mb-4">Kỹ thuật gieo trồng, chăm sóc, phòng trừ sâu bệnh và thu hoạch cho người mới bắt đầu.</p>
  <form action="<?= url('public/search.php') ?>" method="get" class="row g-2">
    <div class="col-12 col-md-8"><input class="form-control form-control-lg" type="search" name="q" placeholder="Ví dụ: tỉa cành, lá vàng, bón phân…"></div>
    <div class="col-12 col-md-4 d-grid"><button class="btn btn-dark btn-lg">Tìm kiếm</button></div>
  </form>
</section>

<section class="mb-5">
  <h2 class="h4 mb-3">Theo giai đoạn sinh trưởng</h2>
  <div>
    <?php foreach ($stages as $s): ?>
      <a class="stage-chip" href="<?= url('public/posts.php?stage=' . (int)$s['id']) ?>"><?= e($s['name']) ?></a>
    <?php endforeach; ?>
    <a class="stage-chip" href="<?= url('public/calendar.php') ?>">📅 Lịch canh tác</a>
  </div>
</section>

<section class="mb-5">
  <h2 class="h4 mb-3">Chuyên mục</h2>
  <div class="row g-3">
    <?php foreach ($cats as $c): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <a class="cat-tile" href="<?= url('public/posts.php?category=' . (int)$c['id']) ?>">
          <div class="fw-semibold"><?= e($c['name']) ?></div>
          <div class="small text-muted"><?= (int)$c['post_count'] ?> bài viết</div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="mb-5">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <h2 class="h4 mb-0">Bài viết mới</h2>
    <a href="<?= url('public/posts.php') ?>">Xem tất cả »</a>
  </div>
  <?php if (!$latest): ?><p class="text-muted">Chưa có bài viết nào.</p><?php endif; ?>
  <div class="row g-4"><?php foreach ($latest as $p) include __DIR__ . '/_card.php'; ?></div>
</section>

<section class="mb-3">
  <div class="p-4 rounded-3" style="background:var(--leaf-soft)">
    <h2 class="h4">Cây có dấu hiệu lạ?</h2>
    <p class="mb-2">Chọn các triệu chứng bạn thấy trên lá, thân, quả để biết có thể là sâu bệnh gì.</p>
    <a class="btn btn-success" href="<?= url('public/pests.php') ?>">Tra cứu sâu bệnh theo triệu chứng</a>
    <?php if ($pests): ?><div class="mt-3 small">Mới cập nhật:
      <?php foreach ($pests as $i => $x): ?><?= $i ? ' · ' : '' ?><a href="<?= url('public/pest.php?slug=' . rawurlencode($x['slug'])) ?>"><?= e($x['name']) ?></a><?php endforeach; ?>
    </div><?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
