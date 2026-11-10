<?php
require_once __DIR__ . '/../includes/functions_public.php';
$post = get_post_by_slug($_GET['slug'] ?? '');
if (!$post) { http_response_code(404); $page_title = 'Không tìm thấy'; require __DIR__ . '/../includes/header.php';
  echo '<div class="alert alert-warning">Bài viết không tồn tại hoặc chưa được đăng. <a href="' . e(url('public/posts.php')) . '">Về danh sách bài viết</a></div>';
  require __DIR__ . '/../includes/footer.php'; exit; }
count_view($post['id']);
$images = get_post_images($post['id']);
$related = get_related_posts($post);
$page_title = $post['title']; $active = 'posts';
require __DIR__ . '/../includes/header.php';
?>
<nav aria-label="breadcrumb"><ol class="breadcrumb small">
  <li class="breadcrumb-item"><a href="<?= url('public/index.php') ?>">Trang chủ</a></li>
  <li class="breadcrumb-item"><a href="<?= url('public/posts.php?category=' . (int)$post['category_id']) ?>"><?= e($post['category_name']) ?></a></li>
  <li class="breadcrumb-item active" aria-current="page">Bài viết</li>
</ol></nav>
<div class="row"><div class="col-lg-8">
  <article>
    <h1 class="h2"><?= e($post['title']) ?></h1>
    <div class="text-muted small mb-3">
      <?= fmt_date($post['created_at']) ?><?= $post['author_name'] ? ' · ' . e($post['author_name']) : '' ?> · <?= (int)$post['views'] + 1 ?> lượt xem
      <?php if ($post['stage_name']): ?> · <span class="badge badge-stage"><?= e($post['stage_name']) ?></span><?php endif; ?>
    </div>
    <?php if ($post['thumbnail']): ?><img class="img-fluid rounded mb-3" src="<?= e(upload_url($post['thumbnail'])) ?>" alt="<?= e($post['title']) ?>"><?php endif; ?>
    <?php if ($post['summary']): ?><p class="lead"><?= e($post['summary']) ?></p><?php endif; ?>
    <div class="article-content"><?= render_content($post['content']) ?></div>
    <?php if ($images): ?>
      <h2 class="h5 mt-4">Hình ảnh</h2>
      <div class="row g-2 gallery">
        <?php foreach ($images as $im): ?>
          <div class="col-6 col-md-4"><a href="<?= e(upload_url($im['file_path'])) ?>" target="_blank"><img src="<?= e(upload_url($im['file_path'])) ?>" alt="<?= e($im['alt_text'] ?: $post['title']) ?>" loading="lazy"></a></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($post['source']): ?><p class="small text-muted mt-4 border-top pt-3"><strong>Nguồn tham khảo:</strong> <?= e($post['source']) ?></p><?php endif; ?>
  </article>
</div>
<aside class="col-lg-4 mt-4 mt-lg-0">
  <?php if ($related): ?>
  <div class="card mb-3"><div class="card-header fw-semibold">Bài viết cùng chuyên mục</div>
    <ul class="list-group list-group-flush">
      <?php foreach ($related as $r): ?><li class="list-group-item"><a href="<?= url('public/post.php?slug=' . rawurlencode($r['slug'])) ?>"><?= e($r['title']) ?></a></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>
  <div class="card"><div class="card-body">
    <div class="fw-semibold mb-1">Cây có dấu hiệu lạ?</div>
    <a class="btn btn-outline-success btn-sm" href="<?= url('public/pests.php') ?>">Tra cứu sâu bệnh</a>
  </div></div>
</aside></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
