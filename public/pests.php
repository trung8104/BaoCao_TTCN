<?php
// =====================================================================
// public/pests.php – trang TRA CỨU SÂU BỆNH
//
// Bố cục: cột lọc bên trái (dính ngay dưới header) + lưới thẻ kết quả bên phải.
// CSS của trang: mục "7. TRANG TRA CỨU SÂU BỆNH" trong assets/css/style.css
// =====================================================================
require_once __DIR__ . '/../includes/functions_public.php';

$page_title = 'Tra cứu sâu bệnh';
$active = 'pests';
$page_layout = 'wide'; // báo cho header.php: trang này rộng hết màn hình, không dùng khung "container"

// $f = các điều kiện lọc người dùng vừa chọn, lấy từ địa chỉ URL
//      (ví dụ ?q=mốc&type=disease&symptoms[]=3&symptoms[]=5)
//   q         từ khóa tên sâu bệnh
//   type      '' = tất cả | 'pest' = sâu hại | 'disease' = bệnh
//   symptoms  danh sách id các triệu chứng đã tick
$f = ['q' => trim($_GET['q'] ?? ''), 'type' => $_GET['type'] ?? '', 'symptoms' => (array)($_GET['symptoms'] ?? [])];

// $pests = danh sách sâu bệnh tìm được. Mỗi phần tử là một dòng của bảng pests, thêm:
//          'thumb'   = tên file ảnh đầu tiên admin đã tải lên (rỗng nếu chưa có ảnh)
//          'matched' = số triệu chứng khớp với những gì người dùng đã tick
// $nSym  = số triệu chứng đang được chọn
[$pests, $nSym] = search_pests($f);

// $selected = id các triệu chứng đã tick, đổi sang số nguyên để so với id trong CSDL
$selected = array_map('intval', $f['symptoms']);

// $groups = triệu chứng gom theo bộ phận của cây:
//           ['leaf' => [các triệu chứng ở lá], 'stem' => [...], 'flower' => [...], ...]
$groups = [];
foreach (get_symptoms() as $s) {
    $groups[$s['body_part']][] = $s;
}

require __DIR__ . '/../includes/header.php';
?>
<div class="pest-page">

    <!-- ============ CỘT TRÁI: bộ lọc ============ -->
    <aside class="pest-sidebar">
        <!-- Gửi bằng GET: các lựa chọn hiện lên thanh địa chỉ, nên chép link là giữ được kết quả -->
        <form method="get" class="filter-panel">

            <!-- Vùng này cuộn được khi bộ lọc dài; hai nút ở dưới luôn hiện -->
            <div class="filter-panel__scroll">
                <h1 class="filter-panel__title">Tra cứu sâu bệnh</h1>
                <p class="filter-panel__hint">Chọn triệu chứng bạn quan sát được. Kết quả xếp theo số triệu chứng khớp nhiều nhất.</p>

                <div class="filter-field">
                    <label class="filter-field__label" for="q">Tên sâu bệnh</label>
                    <input class="filter-field__input" id="q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Ví dụ: mốc sương">
                </div>

                <div class="filter-field">
                    <label class="filter-field__label" for="type">Loại</label>
                    <select class="filter-field__input" id="type" name="type">
                        <option value="">Tất cả</option>
                        <option value="pest" <?= $f['type'] === 'pest' ? 'selected' : '' ?>>Sâu hại</option>
                        <option value="disease" <?= $f['type'] === 'disease' ? 'selected' : '' ?>>Bệnh</option>
                    </select>
                </div>

                <!-- Bộ lọc triệu chứng: mỗi bộ phận (lá, thân, hoa...) là một khối đóng/mở.
                     <details> có sẵn trong trình duyệt: bấm vào dòng <summary> là mở/đóng, không cần JavaScript. -->
                <div class="symptom-filter" role="group" aria-labelledby="symptom-filter-title">
                    <div class="filter-field__label" id="symptom-filter-title">Triệu chứng</div>

                    <?php foreach ($groups as $part => $list): ?>
                        <?php
                        // $tickedCount = số ô đã tick trong nhóm này (từ lần tra cứu trước)
                        $tickedCount = 0;
                        foreach ($list as $s) {
                            if (in_array((int)$s['id'], $selected, true)) {
                                $tickedCount++;
                            }
                        }
                        ?>
                        <!-- Có ô được tick thì thêm "open" để nhóm tự mở, triệu chứng đã chọn không bị giấu đi -->
                        <details class="symptom-group" <?= $tickedCount > 0 ? 'open' : '' ?>>
                            <summary class="symptom-group__head">
                                <span class="symptom-group__name"><?= e(body_part_label($part)) ?></span>
                                <?php if ($tickedCount > 0): ?>
                                    <!-- Số nhỏ màu xanh = số triệu chứng đã chọn trong nhóm -->
                                    <span class="symptom-group__count" title="Đã chọn <?= $tickedCount ?>"><?= $tickedCount ?></span>
                                <?php endif; ?>
                            </summary>

                            <div class="symptom-group__body">
                                <?php foreach ($list as $s): ?>
                                    <label class="symptom-option" for="s<?= (int)$s['id'] ?>">
                                        <input class="symptom-option__box" type="checkbox" name="symptoms[]"
                                            value="<?= (int)$s['id'] ?>" id="s<?= (int)$s['id'] ?>"
                                            <?= in_array((int)$s['id'], $selected, true) ? 'checked' : '' ?>>
                                        <span class="symptom-option__text"><?= e($s['name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-panel__actions">
                <button class="filter-btn" type="submit">Tra cứu</button>
                <a class="filter-btn filter-btn--ghost" href="<?= url('public/pests.php') ?>">Xóa lựa chọn</a>
            </div>
        </form>
    </aside>

    <!-- ============ CỘT PHẢI: kết quả ============ -->
    <section class="pest-results" aria-label="Kết quả tra cứu">
        <p class="pest-results__count">
            <strong><?= count($pests) ?></strong> kết quả<?= $nSym ? " · đã chọn $nSym triệu chứng" : '' ?>
        </p>

        <?php if (!$pests): ?>
            <p class="pest-empty">Không có sâu bệnh nào khớp. Thử bỏ bớt triệu chứng hoặc đổi từ khóa.</p>
        <?php endif; ?>

        <!-- Lưới thẻ: mỗi thẻ là một liên kết dẫn tới trang chi tiết sâu bệnh -->
        <div class="pest-grid">
            <?php foreach ($pests as $p): ?>
                <a class="pest-card" href="<?= url('public/pest.php?slug=' . rawurlencode($p['slug'])) ?>">

                    <!-- Khung ảnh bìa: ảnh do admin tải lên trong trang quản trị.
                         Chưa có ảnh thì hiện biểu tượng thay thế (🐛 sâu hại, 🍂 bệnh). -->
                    <div class="pest-card__cover">
                        <?php if ($p['thumb']): ?>
                            <img class="pest-card__img" src="<?= e(upload_url($p['thumb'])) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="pest-card__placeholder" aria-hidden="true"><?= $p['type'] === 'pest' ? '🐛' : '🍂' ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="pest-card__body">
                        <div class="pest-card__tags">
                            <span class="pest-card__tag <?= $p['type'] === 'pest' ? 'badge-pest' : 'badge-disease' ?>"><?= e(pest_type_label($p['type'])) ?></span>
                            <?php if ($nSym): ?>
                                <span class="pest-card__tag pest-card__tag--match">Khớp <?= (int)$p['matched'] ?>/<?= $nSym ?></span>
                            <?php endif; ?>
                        </div>
                        <h2 class="pest-card__title"><?= e($p['name']) ?></h2>
                        <p class="pest-card__desc"><?= e(excerpt($p['description'] ?? '', 150)) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>