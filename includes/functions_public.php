<?php
// Truy vấn cho trang người xem (luôn dùng prepared statement)
require_once __DIR__ . '/helpers.php';

const POSTS_PER_PAGE = 9;

function get_categories() {
    return q("SELECT c.*, COUNT(p.id) AS post_count FROM categories c
              LEFT JOIN posts p ON p.category_id = c.id AND p.status = 'published'
              GROUP BY c.id ORDER BY c.sort_order, c.name")->fetchAll();
}
function get_stages() {
    return q("SELECT s.*, COUNT(p.id) AS post_count FROM growth_stages s
              LEFT JOIN posts p ON p.growth_stage_id = s.id AND p.status = 'published'
              GROUP BY s.id ORDER BY s.sort_order, s.name")->fetchAll();
}
function get_symptoms() {
    return q("SELECT * FROM symptoms ORDER BY FIELD(body_part,'leaf','stem','flower','fruit','root','whole_plant'), name")->fetchAll();
}
function body_part_label($k) {
    return ['leaf'=>'Lá','stem'=>'Thân','flower'=>'Hoa','fruit'=>'Quả','root'=>'Rễ','whole_plant'=>'Toàn cây'][$k] ?? $k;
}

// $f: q (từ khóa), category (id), stage (id). Trả về [danh sách, tổng số]
function get_posts($f, $page = 1, $per = POSTS_PER_PAGE) {
    $where = ["p.status = 'published'"]; $params = [];
    if (!empty($f['q'])) {
        $where[] = '(p.title LIKE ? OR p.summary LIKE ? OR p.content LIKE ? OR c.name LIKE ? OR s.name LIKE ?)';
        $l = like_escape($f['q']); array_push($params, $l, $l, $l, $l, $l);
    }
    if (!empty($f['category'])) { $where[] = 'p.category_id = ?'; $params[] = (int)$f['category']; }
    if (!empty($f['stage']))    { $where[] = 'p.growth_stage_id = ?'; $params[] = (int)$f['stage']; }
    $w = implode(' AND ', $where);
    $joins = ' JOIN categories c ON c.id = p.category_id LEFT JOIN growth_stages s ON s.id = p.growth_stage_id';
    $total = (int)q("SELECT COUNT(*) FROM posts p $joins WHERE $w", $params)->fetchColumn();
    $off = max(0, ($page - 1) * $per);
    $rows = q("SELECT p.id, p.title, p.slug, p.summary, p.thumbnail, p.created_at, p.views,
                      c.name AS category_name, s.name AS stage_name
               FROM posts p $joins
               WHERE $w ORDER BY p.created_at DESC, p.id DESC LIMIT " . (int)$per . " OFFSET $off", $params)->fetchAll();
    return [$rows, $total];
}
function get_post_by_slug($slug) {
    return q("SELECT p.*, c.name AS category_name, c.slug AS category_slug, s.name AS stage_name, u.full_name AS author_name
              FROM posts p JOIN categories c ON c.id = p.category_id
              LEFT JOIN growth_stages s ON s.id = p.growth_stage_id
              LEFT JOIN users u ON u.id = p.author_id
              WHERE p.slug = ? AND p.status = 'published'", [$slug])->fetch();
}
function get_post_images($postId) {
    return q("SELECT * FROM images WHERE post_id = ? ORDER BY id", [$postId])->fetchAll();
}
function get_related_posts($post, $limit = 3) {
    return q("SELECT id, title, slug, thumbnail FROM posts
              WHERE status = 'published' AND category_id = ? AND id <> ?
              ORDER BY created_at DESC LIMIT " . (int)$limit, [$post['category_id'], $post['id']])->fetchAll();
}
function count_view($postId) {
    if (!empty($_SESSION['viewed'][$postId])) return;
    q("UPDATE posts SET views = views + 1 WHERE id = ?", [$postId]);
    $_SESSION['viewed'][$postId] = true;
}

// Tra cứu sâu bệnh: q (tên), type (pest|disease), symptoms (mảng id). Xếp theo số triệu chứng khớp.
function search_pests($f) {
    $params = []; $join = ''; $matchSel = '0 AS matched';
    $sym = array_values(array_filter(array_map('intval', $f['symptoms'] ?? [])));
    if ($sym) {
        $in = implode(',', array_fill(0, count($sym), '?'));
        $join = "LEFT JOIN pest_symptoms ps ON ps.pest_id = p.id AND ps.symptom_id IN ($in)";
        $matchSel = 'COUNT(ps.symptom_id) AS matched';
        $params = $sym;
    }
    $where = ['1=1'];
    if (!empty($f['q'])) {
        $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.cause LIKE ? OR p.prevention LIKE ? OR p.treatment LIKE ? OR EXISTS (
            SELECT 1 FROM pest_symptoms psq JOIN symptoms sq ON sq.id = psq.symptom_id
            WHERE psq.pest_id = p.id AND sq.name LIKE ?))';
        $l = like_escape($f['q']); array_push($params, $l, $l, $l, $l, $l, $l);
    }
    if (!empty($f['type']) && in_array($f['type'], ['pest', 'disease'], true)) { $where[] = 'p.type = ?'; $params[] = $f['type']; }
    $w = implode(' AND ', $where);
    $having = $sym ? 'HAVING matched > 0' : '';
    $order = $sym ? 'matched DESC, p.name' : 'p.name';
    $rows = q("SELECT p.id, p.name, p.slug, p.type, p.description, $matchSel,
                      (SELECT file_path FROM images i WHERE i.pest_id = p.id ORDER BY i.id LIMIT 1) AS thumb
               FROM pests p $join WHERE $w GROUP BY p.id $having ORDER BY $order", $params)->fetchAll();
    return [$rows, count($sym)];
}
function get_pest_by_slug($slug) { return q("SELECT * FROM pests WHERE slug = ?", [$slug])->fetch(); }
function get_pest_symptoms($pestId) {
    return q("SELECT s.* FROM symptoms s JOIN pest_symptoms ps ON ps.symptom_id = s.id WHERE ps.pest_id = ? ORDER BY s.name", [$pestId])->fetchAll();
}
function get_pest_images($pestId) { return q("SELECT * FROM images WHERE pest_id = ? ORDER BY id", [$pestId])->fetchAll(); }
function pest_type_label($t) { return $t === 'pest' ? 'Sâu hại' : 'Bệnh'; }
