<?php
// Trang CRUD đơn giản dùng chung cho categories, growth_stages, symptoms.
// $cfg: table, title, active, slug (bool), fields [name => [label,type,options?,required?]], list [col => label],
//       order, in_use_msg
require_once __DIR__ . '/auth.php';
function simple_crud(array $cfg) {
    require_login();
    $table = $cfg['table'];
    if (!in_array($table, ['categories', 'growth_stages', 'symptoms'], true)) throw new InvalidArgumentException('table');
    $fields = $cfg['fields'];
    $editId = (int)($_GET['edit'] ?? 0);
    $errors = [];
    $row = $editId ? q("SELECT * FROM $table WHERE id = ?", [$editId])->fetch() : null;
    if ($editId && !$row) { flash('error', 'Không tìm thấy mục cần sửa.'); redirect($cfg['self']); }
    $data = $row ?: array_fill_keys(array_keys($fields), '');
    if (!$row && isset($fields['sort_order'])) $data['sort_order'] = 0;
    if (!$row && isset($fields['body_part'])) $data['body_part'] = 'leaf';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();
        $action = $_POST['action'] ?? 'save';
        if ($action === 'delete') {
            try { q("DELETE FROM $table WHERE id = ?", [(int)$_POST['id']]); flash('success', 'Đã xóa.'); }
            catch (PDOException $ex) { flash('error', $cfg['in_use_msg'] ?? 'Không xóa được vì đang được sử dụng.'); }
            redirect($cfg['self']);
        }
        foreach ($fields as $k => $f) $data[$k] = trim($_POST[$k] ?? '');
        foreach ($fields as $k => $f) if (!empty($f['required']) && $data[$k] === '') $errors[] = $f['label'] . ' không được để trống.';
        if (isset($fields['body_part']) && !isset($fields['body_part']['options'][$data['body_part']])) $errors[] = 'Bộ phận không hợp lệ.';
        if (!empty($cfg['unique_name']) && !$errors && q("SELECT id FROM $table WHERE name = ? AND id <> ?", [$data['name'], $editId])->fetch()) $errors[] = 'Tên này đã tồn tại.';
        if (!$errors) {
            $vals = [];
            foreach ($fields as $k => $f) $vals[$k] = ($f['type'] ?? '') === 'number' ? (int)$data[$k] : ($data[$k] !== '' ? $data[$k] : null);
            if (!empty($cfg['slug'])) $vals['slug'] = unique_slug($table, slugify($data['name']), $editId);
            if ($row) {
                $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($vals)));
                q("UPDATE $table SET $set WHERE id = ?", array_merge(array_values($vals), [$editId]));
                flash('success', 'Đã cập nhật.');
            } else {
                $cols = implode(', ', array_keys($vals)); $ph = implode(', ', array_fill(0, count($vals), '?'));
                q("INSERT INTO $table ($cols) VALUES ($ph)", array_values($vals));
                flash('success', 'Đã thêm.');
            }
            redirect($cfg['self']);
        }
    }
    $rows = q("SELECT * FROM $table ORDER BY " . $cfg['order'])->fetchAll();
    $page_title = $cfg['title']; $active = $cfg['active'];
    require __DIR__ . '/admin_header.php';
    ?>
    <h1 class="h3 mb-3"><?= e($cfg['title']) ?></h1>
    <div class="row g-4">
      <div class="col-lg-4"><div class="card"><div class="card-header fw-semibold"><?= $row ? 'Sửa' : 'Thêm mới' ?></div><div class="card-body">
        <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="save">
          <?php foreach ($fields as $k => $f): $t = $f['type'] ?? 'text'; ?>
            <div class="mb-3"><label class="form-label fw-semibold" for="f_<?= e($k) ?>"><?= e($f['label']) ?><?= !empty($f['required']) ? ' *' : '' ?></label>
              <?php if ($t === 'textarea'): ?><textarea class="form-control" id="f_<?= e($k) ?>" name="<?= e($k) ?>" rows="3"><?= e($data[$k]) ?></textarea>
              <?php elseif ($t === 'select'): ?><select class="form-select" id="f_<?= e($k) ?>" name="<?= e($k) ?>">
                <?php foreach ($f['options'] as $ov => $ol): ?><option value="<?= e($ov) ?>" <?= (string)$data[$k] === (string)$ov ? 'selected' : '' ?>><?= e($ol) ?></option><?php endforeach; ?></select>
              <?php else: ?><input class="form-control" id="f_<?= e($k) ?>" type="<?= $t === 'number' ? 'number' : 'text' ?>" name="<?= e($k) ?>" value="<?= e($data[$k]) ?>" <?= !empty($f['required']) ? 'required' : '' ?>><?php endif; ?>
            </div>
          <?php endforeach; ?>
          <button class="btn btn-danger"><?= $row ? 'Cập nhật' : 'Thêm' ?></button>
          <?php if ($row): ?><a class="btn btn-outline-secondary" href="<?= url($cfg['self']) ?>">Hủy</a><?php endif; ?>
        </form></div></div></div>
      <div class="col-lg-8"><div class="table-responsive card"><table class="table table-hover table-admin mb-0">
        <thead><tr><?php foreach ($cfg['list'] as $label): ?><th><?= e($label) ?></th><?php endforeach; ?><th></th></tr></thead><tbody>
        <?php if (!$rows): ?><tr><td colspan="<?= count($cfg['list']) + 1 ?>" class="text-center text-muted py-4">Chưa có dữ liệu.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?><tr>
          <?php foreach ($cfg['list'] as $col => $label): $v = $r[$col];
            if (isset($fields[$col]['options'][$v])) $v = $fields[$col]['options'][$v]; ?><td><?= e($v) ?></td><?php endforeach; ?>
          <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="<?= url($cfg['self'] . '?edit=' . (int)$r['id']) ?>">Sửa</a>
            <form method="post" class="d-inline" data-confirm="Xóa mục này?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Xóa</button></form></td></tr>
        <?php endforeach; ?></tbody></table></div></div>
    </div>
    <?php
    require __DIR__ . '/admin_footer.php';
}
