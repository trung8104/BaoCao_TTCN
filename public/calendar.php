<?php
require_once __DIR__ . '/../includes/helpers.php';
$page_title = 'Lịch canh tác'; $active = 'calendar';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-1">Lịch canh tác gợi ý</h1>
<p class="text-muted">Nhập ngày gieo hạt để xem các mốc chăm sóc dự kiến. Đây là mốc tham khảo, thời gian thực tế phụ thuộc giống, thời tiết và vùng trồng.</p>
<div class="card mb-4"><div class="card-body">
  <div class="row g-3 align-items-end">
    <div class="col-12 col-md-4"><label class="form-label fw-semibold" for="sowDate">Ngày gieo hạt</label>
      <input class="form-control" type="date" id="sowDate"></div>
    <div class="col-12 col-md-4"><label class="form-label fw-semibold" for="variety">Nhóm giống</label>
      <select class="form-select" id="variety">
        <option value="determinate">Sinh trưởng hữu hạn (cây thấp)</option>
        <option value="indeterminate">Sinh trưởng vô hạn (cần giàn cao)</option>
      </select></div>
    <div class="col-12 col-md-4 d-grid"><button class="btn btn-danger" id="calcBtn" type="button">Tính lịch</button></div>
  </div>
  <div id="calcError" class="text-danger small mt-2" role="alert"></div>
</div></div>
<ol class="timeline" id="timeline" aria-live="polite"></ol>
<p class="small text-muted" id="calcNote" hidden>Lưu ý: các mốc là ước tính để lên kế hoạch. Hãy quan sát cây và điều chỉnh theo thực tế.</p>
<script src="<?= url('assets/js/calendar.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
