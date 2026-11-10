<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $id = (int)$_POST['id'];
    $files = q("SELECT file_path FROM images WHERE post_id = ?", [$id])->fetchAll(PDO::FETCH_COLUMN);
    $th = q("SELECT thumbnail FROM posts WHERE id = ?", [$id])->fetchColumn();
    q("DELETE FROM posts WHERE id = ?", [$id]);
    foreach ($files as $f) delete_image_file($f);
    delete_image_file($th);
    flash('success', 'Đã xóa bài viết.');
    redirect('admin/posts.php');
}
$page_title = 'Bài viết'; $active = 'posts';
$qs = trim($_GET['q'] ?? ''); $st = $_GET['status'] ?? '';
$where = ['1=1']; $params = [];
if ($qs !== '') { $where[] = 'p.title LIKE ?'; $params[] = like_escape($qs); }
if (in_array($st, ['draft', 'published'], true)) { $where[] = 'p.status = ?'; $params[] = $st; }
$perPage = 15; $page = max(1, (int)($_GET['page'] ?? 1));
$w = implode(' AND ', $where);
$total = (int)q("SELECT COUNT(*) FROM posts p WHERE $w", $params)->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage)); $page = min($page, $pages);
$rows = q("SELECT p.id, p.title, p.status, p.views, p.updated_at, c.name AS cat, s.name AS stage
           FROM posts p JOIN categories c ON c.id = p.category_id LEFT JOIN growth_stages s ON s.id = p.growth_stage_id
           WHERE $w ORDER BY p.updated_at DESC, p.id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage), $params)->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Bài viết</h1>
  <a class="btn btn-danger" href="<?= url('admin/post_edit.php') ?>">+ Thêm bài viết</a>
</div>
<form class="row g-2 mb-3" method="get">
  <div class="col-12 col-md-5"><input class="form-control" name="q" placeholder="Tìm theo tiêu đề…" value="<?= e($qs) ?>"></div>
  <div class="col-6 col-md-3"><select class="form-select" name="status">
    <option value="">Mọi trạng thái</option>
    <option value="published" <?= $st === 'published' ? 'selected' : '' ?>>Đã đăng</option>
    <option value="draft" <?= $st === 'draft' ? 'selected' : '' ?>>Nháp</option></select></div>
  <div class="col-6 col-md-2 d-grid"><button class="btn btn-outline-secondary">Lọc</button></div>
</form>
<div class="table-responsive card"><table class="table table-hover table-admin mb-0">
  <thead><tr><th>Tiêu đề</th><th>Chuyên mục</th><th>Giai đoạn</th><th>Trạng thái</th><th>Xem</th><th>Cập nhật</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-4">Chưa có bài viết.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><a href="<?= url('admin/post_edit.php?id=' . (int)$r['id']) ?>" class="fw-semibold"><?= e($r['title']) ?></a></td>
      <td><?= e($r['cat']) ?></td><td><?= e($r['stage'] ?? '—') ?></td>
      <td><span class="badge text-bg-<?= $r['status'] === 'published' ? 'success' : 'secondary' ?>"><?= $r['status'] === 'published' ? 'Đã đăng' : 'Nháp' ?></span></td>
      <td><?= (int)$r['views'] ?></td><td><?= fmt_date($r['updated_at']) ?></td>
      <td class="text-end text-nowrap">
        <a class="btn btn-sm btn-outline-primary" href="<?= url('admin/post_edit.php?id=' . (int)$r['id']) ?>">Sửa</a>
        <form method="post" class="d-inline" data-confirm="Xóa bài viết này và các ảnh của nó?"><?= csrf_field() ?>
          <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Xóa</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<div class="mt-3"><?= render_pagination($page, $pages, ['q' => $qs, 'status' => $st]) ?></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
