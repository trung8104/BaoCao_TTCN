<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ---------- Cơ bản ----------
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function url($path = '') { return rtrim(config()['base_url'], '/') . '/' . ltrim($path, '/'); }
function redirect($path) { header('Location: ' . url($path)); exit; }
function upload_url($file) { return url('uploads/' . rawurlencode($file)); }
function fmt_date($ts) { return $ts ? date('d/m/Y', strtotime($ts)) : ''; }

// ---------- Flash message ----------
function flash($type, $msg) { $_SESSION['flash'][] = [$type, $msg]; }
function flash_render() {
    if (empty($_SESSION['flash'])) return '';
    $out = '';
    foreach ($_SESSION['flash'] as [$t, $m]) {
        $cls = $t === 'error' ? 'danger' : ($t === 'success' ? 'success' : 'info');
        $out .= '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">' . e($m)
              . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

// ---------- CSRF ----------
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_verify() {
    $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
    if (!$ok) { http_response_code(400); exit('Yêu cầu không hợp lệ (CSRF). Hãy tải lại trang và thử lại.'); }
}

// ---------- Chuỗi ----------
function slugify($s) {
    $s = mb_strtolower(trim((string)$s), 'UTF-8');
    $map = ['a'=>'àáạảãâầấậẩẫăằắặẳẵ','e'=>'èéẹẻẽêềếệểễ','i'=>'ìíịỉĩ','o'=>'òóọỏõôồốộổỗơờớợởỡ',
            'u'=>'ùúụủũưừứựửữ','y'=>'ỳýỵỷỹ','d'=>'đ'];
    $rep = [];
    foreach ($map as $to => $chars) foreach (mb_str_split($chars) as $ch) $rep[$ch] = $to;
    $s = strtr($s, $rep);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    return $s !== '' ? $s : 'muc';
}
function unique_slug($table, $base, $excludeId = 0) {
    $allowed = ['posts', 'pests', 'categories', 'growth_stages'];
    if (!in_array($table, $allowed, true)) throw new InvalidArgumentException('table');
    $slug = $base; $i = 2;
    while (q("SELECT id FROM $table WHERE slug = ? AND id <> ?", [$slug, $excludeId])->fetch()) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}
function excerpt($s, $len = 140) {
    $s = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$s)));
    return mb_strlen($s, 'UTF-8') > $len ? mb_substr($s, 0, $len, 'UTF-8') . '…' : $s;
}
function like_escape($s) { return '%' . addcslashes($s, '%_\\') . '%'; }

// Hiển thị nội dung bài viết an toàn: "## " = tiêu đề, "- " = gạch đầu dòng, dòng trống = đoạn mới
function render_content($text) {
    $html = ''; $para = []; $inList = false;
    $flush = function () use (&$html, &$para) {
        if ($para) { $html .= '<p>' . implode('<br>', $para) . '</p>'; $para = []; }
    };
    $close = function () use (&$html, &$inList) { if ($inList) { $html .= '</ul>'; $inList = false; } };
    foreach (preg_split('/\R/u', trim((string)$text)) as $line) {
        $t = trim($line);
        if ($t === '') { $flush(); $close(); continue; }
        if (preg_match('/^##\s+(.+)$/u', $t, $m)) { $flush(); $close(); $html .= '<h3>' . e($m[1]) . '</h3>'; }
        elseif (preg_match('/^[-*]\s+(.+)$/u', $t, $m)) {
            $flush(); if (!$inList) { $html .= '<ul>'; $inList = true; } $html .= '<li>' . e($m[1]) . '</li>';
        } else { $close(); $para[] = e($t); }
    }
    $flush(); $close();
    return $html;
}

// ---------- Phân trang ----------
function render_pagination($page, $pages, $params = []) {
    if ($pages <= 1) return '';
    $link = function ($p, $label, $active = false, $disabled = false) use ($params) {
        $params['page'] = $p;
        $href = '?' . http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
        return '<li class="page-item' . ($active ? ' active' : '') . ($disabled ? ' disabled' : '') . '">'
             . '<a class="page-link" href="' . e($href) . '">' . $label . '</a></li>';
    };
    $out = '<nav aria-label="Phân trang"><ul class="pagination justify-content-center flex-wrap">';
    $out .= $link(max(1, $page - 1), '&laquo;', false, $page <= 1);
    for ($i = 1; $i <= $pages; $i++) {
        if ($i == 1 || $i == $pages || abs($i - $page) <= 2) $out .= $link($i, $i, $i == $page);
        elseif (abs($i - $page) == 3) $out .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }
    $out .= $link(min($pages, $page + 1), '&raquo;', false, $page >= $pages);
    return $out . '</ul></nav>';
}

// ---------- Upload ảnh ----------
function normalize_files($f) {
    $out = [];
    if (!isset($f['name'])) return $out;
    if (!is_array($f['name'])) return [$f];
    foreach ($f['name'] as $i => $n) {
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i],
                  'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}
function save_uploaded_image($file) {
    $msgs = [UPLOAD_ERR_INI_SIZE => 'Ảnh vượt quá dung lượng cho phép của máy chủ.',
             UPLOAD_ERR_FORM_SIZE => 'Ảnh quá lớn.', UPLOAD_ERR_PARTIAL => 'Ảnh tải lên chưa đầy đủ.'];
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException($msgs[$file['error']] ?? 'Lỗi tải ảnh lên.');
    $c = config();
    if ($file['size'] > $c['upload_max_bytes']) {
        throw new RuntimeException('Ảnh vượt quá ' . round($c['upload_max_bytes'] / 1048576, 1) . ' MB.');
    }
    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG, GIF hoặc WEBP.');
    if (!is_dir($c['upload_dir'])) mkdir($c['upload_dir'], 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $types[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $c['upload_dir'] . $name)) throw new RuntimeException('Không lưu được ảnh.');
    return $name;
}
function delete_image_file($name) {
    if (!$name) return;
    $p = config()['upload_dir'] . basename($name);
    if (is_file($p)) @unlink($p);
}
