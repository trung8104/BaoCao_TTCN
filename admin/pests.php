<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_verify();
    $id = (int)$_POST['id'];
    $files = q("SELECT file_path FROM images WHERE pest_id = ?", [$id])->fetchAll(PDO::FETCH_COLUMN);
    q("DELETE FROM pests WHERE id = ?", [$id]);
    foreach ($files as $f) delete_image_file($f);
    flash('success', 'Đã xóa sâu bệnh.');
    redirect('admin/pests.php');
}
$page_title = 'Sâu bệnh'; $active = 'pests';
$qs = trim($_GET['q'] ?? '');
$params = []; $w = '1=1';
if ($qs !== '') { $w = 'p.name LIKE ?'; $params[] = like_escape($qs); }
$rows = q("SELECT p.id, p.name, p.type, p.updated_at,
                  (SELECT COUNT(*) FROM pest_symptoms ps WHERE ps.pest_id = p.id) AS n_sym,
                  (SELECT COUNT(*) FROM images i WHERE i.pest_id = p.id) AS n_img
           FROM pests p WHERE $w ORDER BY p.name", $params)->fetchAll();
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Sâu bệnh</h1>
  <a class="btn btn-danger" href="<?= url('admin/pest_edit.php') ?>">+ Thêm sâu bệnh</a>
</div>
<form class="row g-2 mb-3" method="get"><div class="col-12 col-md-5"><input class="form-control" name="q" placeholder="Tìm theo tên…" value="<?= e($qs) ?>"></div>
  <div class="col-6 col-md-2 d-grid"><button class="btn btn-outline-secondary">Tìm</button></div></form>
<div class="table-responsive card"><table class="table table-hover table-admin mb-0">
  <thead><tr><th>Tên</th><th>Loại</th><th>Triệu chứng</th><th>Ảnh</th><th>Cập nhật</th><th></th></tr></thead><tbody>
  <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?><tr>
    <td><a class="fw-semibold" href="<?= url('admin/pest_edit.php?id=' . (int)$r['id']) ?>"><?= e($r['name']) ?></a></td>
    <td><span class="badge <?= $r['type'] === 'pest' ? 'badge-pest' : 'badge-disease' ?>"><?= e(pest_type_label($r['type'])) ?></span></td>
    <td><?= (int)$r['n_sym'] ?></td><td><?= (int)$r['n_img'] ?></td><td><?= fmt_date($r['updated_at']) ?></td>
    <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/pest_edit.php?id=' . (int)$r['id']) ?>">Sửa</a>
      <form method="post" class="d-inline" data-confirm="Xóa sâu bệnh này và các ảnh của nó?"><?= csrf_field() ?>
        <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button class="btn btn-sm btn-outline-danger">Xóa</button></form></td></tr>
  <?php endforeach; ?></tbody></table></div>
<?php
function pest_type_label($t) { return $t === 'pest' ? 'Sâu hại' : 'Bệnh'; }
require __DIR__ . '/../includes/admin_footer.php'; ?>
