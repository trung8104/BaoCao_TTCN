<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$pest = $id ? q("SELECT * FROM pests WHERE id = ?", [$id])->fetch() : null;
if ($id && !$pest) { flash('error', 'Không tìm thấy sâu bệnh.'); redirect('admin/pests.php'); }
$symptoms = q("SELECT * FROM symptoms ORDER BY FIELD(body_part,'leaf','stem','flower','fruit','root','whole_plant'), name")->fetchAll();
$labels = ['leaf'=>'Lá','stem'=>'Thân','flower'=>'Hoa','fruit'=>'Quả','root'=>'Rễ','whole_plant'=>'Toàn cây'];
$errors = [];
$data = $pest ?: ['name' => '', 'slug' => '', 'type' => 'disease', 'description' => '', 'cause' => '', 'prevention' => '', 'treatment' => '', 'source' => ''];
$chosen = $id ? q("SELECT symptom_id FROM pest_symptoms WHERE pest_id = ?", [$id])->fetchAll(PDO::FETCH_COLUMN) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    foreach (['name', 'slug', 'description', 'cause', 'prevention', 'treatment', 'source'] as $k) $data[$k] = trim($_POST[$k] ?? '');
    $data['type'] = ($_POST['type'] ?? '') === 'pest' ? 'pest' : 'disease';
    $chosen = array_values(array_unique(array_map('intval', (array)($_POST['symptoms'] ?? []))));
    if ($data['name'] === '') $errors[] = 'Tên không được để trống.';
    if (mb_strlen($data['name']) > 150) $errors[] = 'Tên tối đa 150 ký tự.';
    if (!$errors) {
        $slug = unique_slug('pests', slugify($data['slug'] !== '' ? $data['slug'] : $data['name']), $id);
        $uploaded = [];
        try {
            foreach (normalize_files($_FILES['images'] ?? []) as $f) {
                if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
                $uploaded[] = save_uploaded_image($f);
            }
        } catch (RuntimeException $ex) { $errors[] = $ex->getMessage(); foreach ($uploaded as $u) delete_image_file($u); }
    }
    if (!$errors) {
        $pdo = db(); $pdo->beginTransaction();
        try {
            $n = fn($k) => $data[$k] !== '' ? $data[$k] : null;
            if ($pest) {
                q("UPDATE pests SET name=?, slug=?, type=?, description=?, cause=?, prevention=?, treatment=?, source=? WHERE id=?",
                  [$data['name'], $slug, $data['type'], $n('description'), $n('cause'), $n('prevention'), $n('treatment'), $n('source'), $id]);
            } else {
                q("INSERT INTO pests (name, slug, type, description, cause, prevention, treatment, source) VALUES (?,?,?,?,?,?,?,?)",
                  [$data['name'], $slug, $data['type'], $n('description'), $n('cause'), $n('prevention'), $n('treatment'), $n('source')]);
                $id = (int)$pdo->lastInsertId();
            }
            q("DELETE FROM pest_symptoms WHERE pest_id = ?", [$id]);
            foreach ($chosen as $sid) {
                if (q("SELECT id FROM symptoms WHERE id = ?", [$sid])->fetch()) q("INSERT INTO pest_symptoms (pest_id, symptom_id) VALUES (?,?)", [$id, $sid]);
            }
            foreach ($uploaded as $u) q("INSERT INTO images (file_path, alt_text, pest_id, uploaded_by) VALUES (?,?,?,?)", [$u, $data['name'], $id, current_user()['id']]);
            $toDelete = [];
            foreach (array_map('intval', (array)($_POST['delete_images'] ?? [])) as $imgId) {
                $fp = q("SELECT file_path FROM images WHERE id = ? AND pest_id = ?", [$imgId, $id])->fetchColumn();
                if ($fp) { q("DELETE FROM images WHERE id = ?", [$imgId]); $toDelete[] = $fp; }
            }
            $pdo->commit();
            foreach ($toDelete as $fp) delete_image_file($fp);
            flash('success', $pest ? 'Đã cập nhật sâu bệnh.' : 'Đã thêm sâu bệnh.');
            redirect('admin/pest_edit.php?id=' . $id);
        } catch (Throwable $ex) {
            $pdo->rollBack(); foreach ($uploaded as $u) delete_image_file($u);
            $errors[] = 'Lỗi lưu dữ liệu: ' . $ex->getMessage();
        }
    }
}
$images = $id ? q("SELECT * FROM images WHERE pest_id = ? ORDER BY id", [$id])->fetchAll() : [];
$page_title = $pest ? 'Sửa sâu bệnh' : 'Thêm sâu bệnh'; $active = 'pests';
require __DIR__ . '/../includes/admin_header.php';
$fields = ['description' => 'Mô tả', 'cause' => 'Nguyên nhân', 'prevention' => 'Biện pháp phòng', 'treatment' => 'Biện pháp trừ'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?= e($page_title) ?></h1>
  <a class="btn btn-outline-secondary btn-sm" href="<?= url('admin/pests.php') ?>">← Danh sách</a>
</div>
<?php foreach ($errors as $er): ?><div class="alert alert-danger"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="row g-4"><?= csrf_field() ?>
  <div class="col-lg-8"><div class="card"><div class="card-body">
    <div class="row g-3 mb-3">
      <div class="col-md-8"><label class="form-label fw-semibold" for="name">Tên *</label>
        <input class="form-control" id="name" name="name" maxlength="150" required value="<?= e($data['name']) ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold" for="type">Loại</label>
        <select class="form-select" id="type" name="type"><option value="disease" <?= $data['type'] === 'disease' ? 'selected' : '' ?>>Bệnh</option>
          <option value="pest" <?= $data['type'] === 'pest' ? 'selected' : '' ?>>Sâu hại</option></select></div>
    </div>
    <div class="mb-3"><label class="form-label fw-semibold" for="slug">Đường dẫn (slug)</label>
      <input class="form-control" id="slug" name="slug" value="<?= e($data['slug']) ?>" placeholder="Để trống sẽ tự tạo từ tên"></div>
    <?php foreach ($fields as $k => $label): ?>
      <div class="mb-3"><label class="form-label fw-semibold" for="<?= $k ?>"><?= e($label) ?></label>
        <textarea class="form-control" id="<?= $k ?>" name="<?= $k ?>" rows="4"><?= e($data[$k]) ?></textarea></div>
    <?php endforeach; ?>
    <div class="mb-0"><label class="form-label fw-semibold" for="source">Nguồn tham khảo</label>
      <input class="form-control" id="source" name="source" maxlength="255" value="<?= e($data['source']) ?>"></div>
    <div class="form-text mt-2">Mẹo định dạng: dòng bắt đầu bằng <code>- </code> là gạch đầu dòng, dòng trống để xuống đoạn.</div>
  </div></div></div>
  <div class="col-lg-4">
    <div class="card mb-3"><div class="card-body">
      <div class="fw-semibold mb-2">Triệu chứng nhận biết</div>
      <?php if (!$symptoms): ?><div class="text-muted small">Chưa có triệu chứng. <a href="<?= url('admin/symptoms.php') ?>">Thêm triệu chứng</a></div><?php endif; ?>
      <?php $last = null; foreach ($symptoms as $s): if ($s['body_part'] !== $last) { $last = $s['body_part']; echo '<div class="small fw-bold text-uppercase text-muted mt-2">' . e($labels[$last] ?? $last) . '</div>'; } ?>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="symptoms[]" value="<?= (int)$s['id'] ?>" id="sy<?= (int)$s['id'] ?>" <?= in_array((int)$s['id'], array_map('intval', $chosen), true) ? 'checked' : '' ?>>
          <label class="form-check-label" for="sy<?= (int)$s['id'] ?>"><?= e($s['name']) ?></label></div>
      <?php endforeach; ?>
      <div class="d-grid mt-3"><button class="btn btn-danger">Lưu</button></div>
    </div></div>
    <div class="card"><div class="card-body">
      <label class="form-label fw-semibold" for="images">Ảnh minh họa</label>
      <input class="form-control" type="file" id="images" name="images[]" accept="image/*" multiple data-preview="#imgPrev">
      <div id="imgPrev" class="mt-2"></div>
      <?php if ($images): ?><div class="row g-2 mt-1"><?php foreach ($images as $im): ?><div class="col-6 img-tile"><img src="<?= e(upload_url($im['file_path'])) ?>" alt="">
        <div class="form-check"><input class="form-check-input" type="checkbox" name="delete_images[]" value="<?= (int)$im['id'] ?>" id="di<?= (int)$im['id'] ?>"><label class="form-check-label small" for="di<?= (int)$im['id'] ?>">Xóa</label></div></div><?php endforeach; ?></div><?php endif; ?>
    </div></div>
  </div>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
