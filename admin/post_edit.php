<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$post = $id ? q("SELECT * FROM posts WHERE id = ?", [$id])->fetch() : null;
if ($id && !$post) { flash('error', 'Không tìm thấy bài viết.'); redirect('admin/posts.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_pasted_image') {
    header('Content-Type: application/json; charset=utf-8');
    csrf_verify();
    try {
        if (empty($_FILES['image'])) throw new RuntimeException('Không nhận được ảnh cần tải lên.');
        echo json_encode(['file' => save_uploaded_image($_FILES['image'])]);
    } catch (RuntimeException $ex) {
        http_response_code(422);
        echo json_encode(['error' => $ex->getMessage()]);
    }
    exit;
}
$cats = q("SELECT id, name FROM categories ORDER BY sort_order, name")->fetchAll();
$stages = q("SELECT id, name FROM growth_stages ORDER BY sort_order, name")->fetchAll();
$errors = [];
$data = $post ?: ['title' => '', 'slug' => '', 'category_id' => '', 'growth_stage_id' => '', 'summary' => '',
                  'content' => '', 'source' => '', 'status' => 'draft', 'thumbnail' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    foreach (['title', 'slug', 'summary', 'content', 'source'] as $k) $data[$k] = trim($_POST[$k] ?? '');
    $data['category_id'] = (int)($_POST['category_id'] ?? 0);
    $data['growth_stage_id'] = (int)($_POST['growth_stage_id'] ?? 0) ?: null;
    $data['status'] = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
    if ($data['title'] === '') $errors[] = 'Tiêu đề không được để trống.';
    if (mb_strlen($data['title']) > 200) $errors[] = 'Tiêu đề tối đa 200 ký tự.';
    if ($data['content'] === '') $errors[] = 'Nội dung không được để trống.';
    if (!q("SELECT id FROM categories WHERE id = ?", [$data['category_id']])->fetch()) $errors[] = 'Hãy chọn chuyên mục.';
    if ($data['growth_stage_id'] && !q("SELECT id FROM growth_stages WHERE id = ?", [$data['growth_stage_id']])->fetch()) $data['growth_stage_id'] = null;

    if (!$errors) {
        $slug = unique_slug('posts', slugify($data['slug'] !== '' ? $data['slug'] : $data['title']), $id);
        $newThumb = null; $uploaded = [];
        try {
            if (!empty($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) $newThumb = save_uploaded_image($_FILES['thumbnail']);
            foreach (normalize_files($_FILES['images'] ?? []) as $f) {
                if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
                $uploaded[] = save_uploaded_image($f);
            }
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
            delete_image_file($newThumb); foreach ($uploaded as $u) delete_image_file($u);
        }
    }
    if (!$errors) {
        $pdo = db(); $pdo->beginTransaction();
        try {
            $oldThumb = $post['thumbnail'] ?? null;
            $thumb = $newThumb ?: $oldThumb;
            if (!empty($_POST['remove_thumbnail']) && !$newThumb) $thumb = null;
            if ($post) {
                q("UPDATE posts SET category_id=?, growth_stage_id=?, title=?, slug=?, summary=?, content=?, thumbnail=?, source=?, status=? WHERE id=?",
                  [$data['category_id'], $data['growth_stage_id'], $data['title'], $slug, $data['summary'] ?: null, $data['content'], $thumb, $data['source'] ?: null, $data['status'], $id]);
            } else {
                q("INSERT INTO posts (category_id, growth_stage_id, author_id, title, slug, summary, content, thumbnail, source, status) VALUES (?,?,?,?,?,?,?,?,?,?)",
                  [$data['category_id'], $data['growth_stage_id'], current_user()['id'], $data['title'], $slug, $data['summary'] ?: null, $data['content'], $thumb, $data['source'] ?: null, $data['status']]);
                $id = (int)$pdo->lastInsertId();
            }
            foreach ($uploaded as $u) q("INSERT INTO images (file_path, alt_text, post_id, uploaded_by) VALUES (?,?,?,?)", [$u, $data['title'], $id, current_user()['id']]);
            $del = array_map('intval', (array)($_POST['delete_images'] ?? []));
            $toDelete = [];
            foreach ($del as $imgId) {
                $fp = q("SELECT file_path FROM images WHERE id = ? AND post_id = ?", [$imgId, $id])->fetchColumn();
                if ($fp) { q("DELETE FROM images WHERE id = ?", [$imgId]); $toDelete[] = $fp; }
            }
            $pdo->commit();
            foreach ($toDelete as $fp) delete_image_file($fp);
            if ($oldThumb && $oldThumb !== $thumb) delete_image_file($oldThumb);
            flash('success', $post ? 'Đã cập nhật bài viết.' : 'Đã thêm bài viết.');
            redirect('admin/post_edit.php?id=' . $id);
        } catch (Throwable $ex) {
            $pdo->rollBack();
            delete_image_file($newThumb); foreach ($uploaded as $u) delete_image_file($u);
            $errors[] = 'Lỗi lưu dữ liệu: ' . $ex->getMessage();
        }
    }
}
$images = $id ? q("SELECT * FROM images WHERE post_id = ? ORDER BY id", [$id])->fetchAll() : [];
$page_title = $post ? 'Sửa bài viết' : 'Thêm bài viết'; $active = 'posts';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0"><?= e($page_title) ?></h1>
  <div>
    <?php if ($post && $post['status'] === 'published'): ?><a class="btn btn-outline-secondary btn-sm" target="_blank" href="<?= url('public/post.php?slug=' . rawurlencode($post['slug'])) ?>">Xem trên website</a><?php endif; ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= url('admin/posts.php') ?>">← Danh sách</a>
  </div>
</div>
<?php foreach ($errors as $er): ?><div class="alert alert-danger"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="row g-4"><?= csrf_field() ?>
  <div class="col-lg-8"><div class="card"><div class="card-body">
    <div class="mb-3"><label class="form-label fw-semibold" for="title">Tiêu đề *</label>
      <input class="form-control" id="title" name="title" maxlength="200" required value="<?= e($data['title']) ?>"></div>
    <div class="mb-3"><label class="form-label fw-semibold" for="slug">Đường dẫn (slug)</label>
      <input class="form-control" id="slug" name="slug" value="<?= e($data['slug']) ?>" placeholder="Để trống sẽ tự tạo từ tiêu đề"></div>
    <div class="mb-3"><label class="form-label fw-semibold" for="summary">Tóm tắt</label>
      <textarea class="form-control" id="summary" name="summary" rows="2" maxlength="500"><?= e($data['summary']) ?></textarea></div>
    <div class="mb-3"><label class="form-label fw-semibold" for="content">Nội dung *</label>
      <textarea class="form-control" id="content" name="content" rows="16" required><?= e($data['content']) ?></textarea>
      <div id="content-paste-status" class="form-text" role="status">Bạn có thể dán ảnh đã sao chép; ảnh sẽ được chèn tại vị trí con trỏ.</div>
      <div class="form-text">Định dạng: dòng bắt đầu bằng <code>## </code> là tiêu đề mục; dòng bắt đầu bằng <code>- </code> là gạch đầu dòng; để trống một dòng để xuống đoạn mới.</div></div>
    <div class="mb-0"><label class="form-label fw-semibold" for="source">Nguồn tham khảo</label>
      <input class="form-control" id="source" name="source" maxlength="255" value="<?= e($data['source']) ?>" placeholder="Tên tài liệu, cơ quan, năm"></div>
  </div></div></div>
  <div class="col-lg-4">
    <div class="card mb-3"><div class="card-body">
      <div class="mb-3"><label class="form-label fw-semibold" for="status">Trạng thái</label>
        <select class="form-select" id="status" name="status">
          <option value="draft" <?= $data['status'] === 'draft' ? 'selected' : '' ?>>Nháp (chưa hiện ra website)</option>
          <option value="published" <?= $data['status'] === 'published' ? 'selected' : '' ?>>Đăng công khai</option></select></div>
      <div class="mb-3"><label class="form-label fw-semibold" for="category_id">Chuyên mục *</label>
        <select class="form-select" id="category_id" name="category_id" required>
          <option value="">— Chọn —</option>
          <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$data['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div class="mb-3"><label class="form-label fw-semibold" for="growth_stage_id">Giai đoạn sinh trưởng</label>
        <select class="form-select" id="growth_stage_id" name="growth_stage_id">
          <option value="">— Bài viết chung —</option>
          <?php foreach ($stages as $s): ?><option value="<?= (int)$s['id'] ?>" <?= (int)$data['growth_stage_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></div>
      <div class="d-grid"><button class="btn btn-danger">Lưu bài viết</button></div>
    </div></div>
    <div class="card mb-3"><div class="card-body">
      <label class="form-label fw-semibold" for="thumbnail">Ảnh đại diện</label>
      <?php if (!empty($data['thumbnail'])): ?>
        <img class="img-fluid rounded mb-2" src="<?= e(upload_url($data['thumbnail'])) ?>" alt="">
        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="remove_thumbnail" id="rmth"><label class="form-check-label" for="rmth">Xóa ảnh đại diện</label></div>
      <?php endif; ?>
      <input class="form-control" type="file" id="thumbnail" name="thumbnail" accept="image/*" data-preview="#thPrev">
      <div id="thPrev" class="mt-2"></div>
      <div class="form-text">JPG, PNG, GIF, WEBP, tối đa <?= round(config()['upload_max_bytes'] / 1048576, 1) ?> MB.</div>
    </div></div>
    
  </div>
  </div></div>
</form>
<script>
(() => {
  const editor = document.getElementById('content');
  const status = document.getElementById('content-paste-status');
  const csrf = document.querySelector('input[name="csrf"]').value;
  let pendingUploads = 0;

  editor.form.addEventListener('submit', (event) => {
    if (pendingUploads > 0) {
      event.preventDefault();
      status.textContent = 'Vui lòng đợi ảnh tải lên xong rồi lưu bài viết.';
    }
  });

  editor.addEventListener('paste', async (event) => {
    const image = Array.from(event.clipboardData.items)
      .find((item) => item.type.startsWith('image/'));
    if (!image) return;

    event.preventDefault();
    const file = image.getAsFile();
    if (!file) {
      status.textContent = 'Không đọc được ảnh trong clipboard.';
      return;
    }

    const marker = `[[uploading:${Date.now()}-${Math.random().toString(16).slice(2)}]]`;
    const start = editor.selectionStart;
    const end = editor.selectionEnd;
    editor.setRangeText(marker, start, end, 'end');
    editor.focus();
    pendingUploads++;
    status.textContent = 'Đang tải ảnh lên...';

    try {
      const body = new FormData();
      body.append('action', 'upload_pasted_image');
      body.append('csrf', csrf);
      body.append('image', file, file.name || 'pasted-image');
      const response = await fetch(window.location.href, {
        method: 'POST',
        body,
        credentials: 'same-origin'
      });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Không tải được ảnh lên.');

      editor.value = editor.value.replace(marker, `[[image:${result.file}]]`);
      status.textContent = 'Đã chèn ảnh tại vị trí con trỏ.';
    } catch (error) {
      editor.value = editor.value.replace(marker, '');
      status.textContent = error.message || 'Không tải được ảnh lên.';
    } finally {
      pendingUploads--;
    }
  });
})();
</script>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
