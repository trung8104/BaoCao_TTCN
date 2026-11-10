<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $fp = q("SELECT file_path FROM images WHERE id = ?", [(int)$_POST['id']])->fetchColumn();
    if ($fp) { q("DELETE FROM images WHERE id = ?", [(int)$_POST['id']]); delete_image_file($fp); flash('success', 'Đã xóa ảnh.'); }
    redirect('admin/images.php');
}
$page_title = 'Thư viện ảnh'; $active = 'images';
$perPage = 24; $page = max(1, (int)($_GET['page'] ?? 1));
$total = (int)q("SELECT COUNT(*) FROM images")->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage)); $page = min($page, $pages);
$rows = q("SELECT i.*, p.title AS post_title, p.id AS pid, d.name AS pest_name, d.id AS did
           FROM images i LEFT JOIN posts p ON p.id = i.post_id LEFT JOIN pests d ON d.id = i.pest_id
           ORDER BY i.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage))->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<h1 class="h3 mb-1">Thư viện ảnh</h1>
<p class="text-muted">Ảnh được tải lên khi sửa bài viết hoặc sâu bệnh. Tổng cộng <?= $total ?> ảnh.</p>
<?php if (!$rows): ?><div class="alert alert-light border">Chưa có ảnh nào.</div><?php endif; ?>
<div class="row g-3">
<?php foreach ($rows as $im): ?><div class="col-6 col-md-4 col-lg-2"><div class="card h-100"><img class="card-img-top" style="aspect-ratio:4/3;object-fit:cover" src="<?= e(upload_url($im['file_path'])) ?>" alt="">
  <div class="card-body p-2 small">
    <?php if ($im['pid']): ?>Bài: <a href="<?= url('admin/post_edit.php?id=' . (int)$im['pid']) ?>"><?= e(excerpt($im['post_title'], 30)) ?></a>
    <?php elseif ($im['did']): ?>Sâu bệnh: <a href="<?= url('admin/pest_edit.php?id=' . (int)$im['did']) ?>"><?= e(excerpt($im['pest_name'], 30)) ?></a>
    <?php else: ?><span class="text-muted">Chưa gắn với mục nào</span><?php endif; ?>
    <form method="post" class="mt-2" data-confirm="Xóa ảnh này?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$im['id'] ?>">
      <button class="btn btn-sm btn-outline-danger w-100">Xóa</button></form>
  </div></div></div><?php endforeach; ?>
</div>
<div class="mt-4"><?= render_pagination($page, $pages) ?></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
