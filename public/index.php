<?php
require_once __DIR__ . '/../includes/functions_public.php';
$page_title = 'Trang chủ'; $active = 'home';
[$latest] = get_posts([], 1, 3);
$cats = get_categories();
$stages = get_stages();
$pests = q("SELECT name, slug, type FROM pests ORDER BY created_at DESC LIMIT 4")->fetchAll();
$symptoms = get_symptoms();
$stats = [
  'posts' => (int)q("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
  'pests' => (int)q("SELECT COUNT(*) FROM pests")->fetchColumn(),
  'stages' => count($stages),
];
$symptomLinks = ['Lá vàng', 'Lá héo rũ', 'Lá xoăn, co rút', 'Đốm nâu trên lá', 'Quả nứt', 'Quả có lỗ đục, bị sâu ăn', 'Đốm đen hoặc thối ở đáy quả'];
$quickSymptoms = [];
foreach ($symptoms as $symptom) if (in_array($symptom['name'], $symptomLinks, true)) $quickSymptoms[] = $symptom;
require __DIR__ . '/../includes/header.php';
?>
<section class="home-hero mb-5">
  <div class="hero-copy">
    <span class="eyebrow"><span class="eyebrow-dot"></span> CẨM NANG CÀ CHUA · TỪ GIEO HẠT ĐẾN THU HOẠCH</span>
    <h1>Trồng cà chua<br><span>tự tin hơn mỗi ngày.</span></h1>
    <p>Hướng dẫn chăm cây theo từng giai đoạn, nhận biết sâu bệnh qua dấu hiệu và chủ động lên lịch chăm sóc cho vườn của bạn.</p>
    <form action="<?= url('public/search.php') ?>" method="get" class="hero-search">
      <span aria-hidden="true">⌕</span>
      <input type="search" name="q" aria-label="Tìm kiếm kiến thức" placeholder="Bạn muốn tìm hiểu điều gì? Ví dụ: lá vàng, tỉa cành…">
      <button type="submit">Tìm kiếm <span aria-hidden="true">→</span></button>
    </form>
    <div class="search-suggestions"><span>Tìm nhanh:</span>
      <a href="<?= url('public/search.php?q=' . rawurlencode('lá vàng')) ?>">lá vàng</a>
      <a href="<?= url('public/search.php?q=' . rawurlencode('bón phân')) ?>">bón phân</a>
      <a href="<?= url('public/search.php?q=' . rawurlencode('mốc sương')) ?>">mốc sương</a>
    </div>
  </div>
  <div class="hero-art" aria-hidden="true">
    <div class="sun-disc"></div><div class="art-leaf leaf-one"></div><div class="art-leaf leaf-two"></div>
    <div class="tomato tomato-one"><i></i></div><div class="tomato tomato-two"><i></i></div>
    <div class="art-stem"></div><div class="art-caption"><span>01</span><div><b>Vườn khỏe</b><small>Bắt đầu từ hiểu đúng</small></div></div>
  </div>
</section>

<section class="feature-grid mb-5" aria-label="Chức năng chính">
  <a class="feature-card feature-guide" href="<?= url('public/posts.php') ?>">
    <span class="feature-icon">✳</span><span class="feature-index">01 / CẨM NANG</span>
    <h2>Hướng dẫn canh tác</h2><p>Gieo ươm, tưới nước, dinh dưỡng, tỉa cành và thu hoạch.</p><span class="feature-link">Khám phá bài viết <b>↗</b></span>
  </a>
  <a class="feature-card feature-diagnose" href="<?= url('public/pests.php') ?>">
    <span class="feature-icon">⌕</span><span class="feature-index">02 / CHẨN ĐOÁN</span>
    <h2>Tra cứu sâu bệnh</h2><p>Đối chiếu dấu hiệu trên lá, thân, hoa, quả và rễ.</p><span class="feature-link">Tìm theo triệu chứng <b>↗</b></span>
  </a>
  <a class="feature-card feature-calendar" href="<?= url('public/calendar.php') ?>">
    <span class="feature-icon">▦</span><span class="feature-index">03 / THEO DÕI</span>
    <h2>Lịch canh tác</h2><p>Lập các mốc chăm sóc dự kiến từ ngày gieo hạt.</p><span class="feature-link">Tạo lịch của bạn <b>↗</b></span>
  </a>
</section>

<section class="stage-section mb-5">
  <div class="section-heading"><div><span class="eyebrow">ĐI CÙNG CÂY QUA TỪNG CHẶNG</span><h2>Hôm nay, vườn đang ở giai đoạn nào?</h2></div><a class="text-link" href="<?= url('public/calendar.php') ?>">Xem lịch chăm sóc <b>→</b></a></div>
  <div class="stage-track">
    <?php foreach ($stages as $i => $stage): ?>
      <a class="stage-step" href="<?= url('public/posts.php?stage=' . (int)$stage['id']) ?>">
        <span class="stage-number"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span><span class="stage-name"><?= e($stage['name']) ?></span><span class="stage-count"><?= (int)$stage['post_count'] ?> bài <b>↗</b></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="diagnose-section mb-5">
  <div class="diagnose-intro"><span class="eyebrow">QUAN SÁT · ĐỐI CHIẾU · XỬ LÝ</span><h2>Cây đang muốn<br><span>nói điều gì?</span></h2><p>Chọn dấu hiệu gần giống nhất để xem các nguyên nhân thường gặp và cách chăm sóc phù hợp.</p><a class="btn-forest" href="<?= url('public/pests.php') ?>">Mở thư viện chẩn đoán <b>→</b></a></div>
  <div class="symptom-panel"><div class="symptom-panel-head"><span>DẤU HIỆU THƯỜNG GẶP</span><span><?= count($quickSymptoms) ?> dấu hiệu</span></div>
    <div class="symptom-chips">
      <?php foreach ($quickSymptoms as $symptom): ?>
        <a href="<?= url('public/pests.php?symptoms%5B%5D=' . (int)$symptom['id']) ?>"><span class="symptom-mark">+</span><?= e($symptom['name']) ?><b>↗</b></a>
      <?php endforeach; ?>
    </div>
    <div class="symptom-foot"><span>Chưa chắc triệu chứng là gì?</span><a href="<?= url('public/pests.php') ?>">Chọn nhiều dấu hiệu để đối chiếu →</a></div>
  </div>
</section>

<section class="mb-5">
  <div class="section-heading"><div><span class="eyebrow">KIẾN THỨC ĐỂ BẮT ĐẦU</span><h2>Khám phá theo chủ đề</h2></div><a class="text-link" href="<?= url('public/posts.php') ?>">Tất cả bài viết <b>→</b></a></div>
  <div class="topic-grid">
    <?php foreach ($cats as $i => $category): ?>
      <a class="topic-card" href="<?= url('public/posts.php?category=' . (int)$category['id']) ?>"><span class="topic-no"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span><span class="topic-title"><?= e($category['name']) ?></span><span class="topic-meta"><?= (int)$category['post_count'] ?> bài viết <b>↗</b></span></a>
    <?php endforeach; ?>
  </div>
</section>

<section class="latest-section mb-5">
  <div class="section-heading"><div><span class="eyebrow">ĐỌC VÀ ÁP DỤNG</span><h2>Bài viết dành cho khu vườn của bạn</h2></div><a class="text-link" href="<?= url('public/posts.php') ?>">Đọc thư viện <b>→</b></a></div>
  <?php if (!$latest): ?><div class="empty-library"><span>✳</span><div><h3>Thư viện đang chờ những bài viết đầu tiên</h3><p>Các bài hướng dẫn đã được biên tập sẽ xuất hiện tại đây.</p></div><a href="<?= url('admin/login.php') ?>">Mở trang quản trị →</a></div>
  <?php else: ?><div class="row g-4"><?php foreach ($latest as $p) include __DIR__ . '/_card.php'; ?></div><?php endif; ?>
</section>

<section class="source-note"><span class="source-mark">i</span><p>Thông tin trên trang mang tính tham khảo. Hãy quan sát điều kiện vườn, giống cây và thời tiết địa phương; khi cần xử lý dịch hại, tham khảo cán bộ khuyến nông và hướng dẫn trên nhãn sản phẩm.</p><span class="source-stats"><?= $stats['posts'] ?> bài · <?= $stats['pests'] ?> hồ sơ · <?= $stats['stages'] ?> giai đoạn</span></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
