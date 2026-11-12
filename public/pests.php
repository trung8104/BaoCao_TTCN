<?php
require_once __DIR__ . '/../includes/functions_public.php';
$page_title = 'Tra cứu sâu bệnh';
$active = 'pests';
$f = ['q' => trim($_GET['q'] ?? ''), 'type' => $_GET['type'] ?? '', 'symptoms' => (array)($_GET['symptoms'] ?? [])];
[$pests, $nSym] = search_pests($f);
$selected = array_map('intval', $f['symptoms']);
$groups = [];
foreach (get_symptoms() as $s) $groups[$s['body_part']][] = $s;
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-1">Tra cứu sâu bệnh</h1>
<p class="text-muted">Chọn triệu chứng bạn quan sát được. Kết quả xếp theo số triệu chứng khớp nhiều nhất.</p>
<div class="row g-4">
    <div class="col-lg-4">
        <form method="get" class="card">
            <div class="card-body">
                <div class="mb-3"><label class="form-label fw-semibold" for="q">Tên sâu bệnh</label>
                    <input class="form-control" id="q" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Ví dụ: mốc sương">
                </div>
                <div class="mb-3"><label class="form-label fw-semibold" for="type">Loại</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tất cả</option>
                        <option value="pest" <?= $f['type'] === 'pest' ? 'selected' : '' ?>>Sâu hại</option>
                        <option value="disease" <?= $f['type'] === 'disease' ? 'selected' : '' ?>>Bệnh</option>
                    </select>
                </div>
                <!-- Symptom filter: one folding block per body part (leaf, stem, flower...).
           <details> is built into the browser: clicking the <summary> row opens/closes it.
           No JavaScript needed. CSS for this: section "7. PEST LOOKUP PAGE" in style.css -->
                <div class="symptom-filter" role="group" aria-labelledby="symptom-filter-title">
                    <div class="fw-semibold mb-2" id="symptom-filter-title">Triệu chứng</div>

                    <?php foreach ($groups as $part => $list): ?>
                        <?php
                        // How many boxes in this group are already ticked (from a previous search)?
                        $tickedCount = 0;
                        foreach ($list as $s) {
                            if (in_array((int)$s['id'], $selected, true)) {
                                $tickedCount++;
                            }
                        }
                        ?>
                        <!-- "open" is added when something is ticked, so a chosen symptom is never hidden -->
                        <details class="symptom-group" <?= $tickedCount > 0 ? ' open' : '' ?>>
                            <summary class="symptom-group__head">
                                <span class="symptom-group__name"><?= e(body_part_label($part)) ?></span>
                                <?php if ($tickedCount > 0): ?>
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
                <div class="d-grid gap-2 mt-3"><button class="btn btn-danger">Tra cứu</button>
                    <a class="btn btn-outline-secondary" href="<?= url('public/pests.php') ?>">Xóa lựa chọn</a>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-8">
        <p class="text-muted small"><?= count($pests) ?> kết quả<?= $nSym ? " (đã chọn $nSym triệu chứng)" : '' ?></p>
        <?php if (!$pests): ?><div class="alert alert-light border">Không có sâu bệnh nào khớp. Thử bỏ bớt triệu chứng hoặc đổi từ khóa.</div><?php endif; ?>
        <?php foreach ($pests as $p): ?>
            <a class="card card-post mb-3 text-decoration-none text-reset" href="<?= url('public/pest.php?slug=' . rawurlencode($p['slug'])) ?>">
                <div class="row g-0">
                    <?php if ($p['thumb']): ?><div class="col-4 col-md-3"><img class="thumb h-100" src="<?= e(upload_url($p['thumb'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></div><?php endif; ?>
                    <div class="col">
                        <div class="card-body">
                            <span class="badge <?= $p['type'] === 'pest' ? 'badge-pest' : 'badge-disease' ?>"><?= e(pest_type_label($p['type'])) ?></span>
                            <?php if ($nSym): ?><span class="badge text-bg-success">Khớp <?= (int)$p['matched'] ?>/<?= $nSym ?></span><?php endif; ?>
                            <h2 class="h5 mt-2"><?= e($p['name']) ?></h2>
                            <p class="text-muted small mb-0"><?= e(excerpt($p['description'] ?? '', 150)) ?></p>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>