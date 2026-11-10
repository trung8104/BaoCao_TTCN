<?php
// Thẻ bài viết dùng chung. Cần biến $p (một dòng bài viết).
?>
<div class="col-12 col-sm-6 col-lg-4">
  <a class="card card-post text-decoration-none text-reset" href="<?= url('public/post.php?slug=' . rawurlencode($p['slug'])) ?>">
    <?php if (!empty($p['thumbnail'])): ?>
      <img class="thumb" src="<?= e(upload_url($p['thumbnail'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
    <?php else: ?><div class="thumb-empty">🍅</div><?php endif; ?>
    <div class="card-body">
      <div class="mb-2">
        <span class="badge text-bg-light border"><?= e($p['category_name']) ?></span>
        <?php if (!empty($p['stage_name'])): ?><span class="badge badge-stage"><?= e($p['stage_name']) ?></span><?php endif; ?>
      </div>
      <h2 class="h5 card-title"><?= e($p['title']) ?></h2>
      <p class="card-text text-muted small mb-0"><?= e(excerpt($p['summary'] ?? '', 110)) ?></p>
    </div>
    <div class="card-footer bg-white border-0 small text-muted"><?= fmt_date($p['created_at']) ?> · <?= (int)$p['views'] ?> lượt xem</div>
  </a>
</div>
