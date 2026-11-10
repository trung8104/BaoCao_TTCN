<?php
require_once __DIR__ . '/../includes/helpers.php';
$page_title = 'Lịch canh tác'; $active = 'calendar';
require __DIR__ . '/../includes/header.php';
?>
<div class="calendar-intro">
  <span class="eyebrow">KẾ HOẠCH THEO VƯỜN CỦA BẠN</span>
  <h1>Lịch canh tác cà chua</h1>
  <p>Tạo các mốc chăm sóc từ ngày gieo hạt, rồi đánh dấu công việc khi hoàn thành. Kế hoạch được lưu trên trình duyệt này.</p>
</div>
<div class="calendar-form card mb-4"><div class="card-body">
  <div class="row g-3 align-items-end">
    <div class="col-12 col-md-3"><label class="form-label fw-semibold" for="cropName">Tên luống / chậu</label>
      <input class="form-control" type="text" id="cropName" maxlength="50" placeholder="Ví dụ: Cà chua bi ban công"></div>
    <div class="col-12 col-md-3"><label class="form-label fw-semibold" for="sowDate">Ngày gieo hạt</label>
      <input class="form-control" type="date" id="sowDate"></div>
    <div class="col-12 col-md-3"><label class="form-label fw-semibold" for="variety">Nhóm giống</label>
      <select class="form-select" id="variety">
        <option value="determinate">Sinh trưởng hữu hạn (cây thấp)</option>
        <option value="indeterminate">Sinh trưởng vô hạn (cần giàn cao)</option>
      </select></div>
    <div class="col-12 col-md-3 d-grid"><button class="btn btn-danger" id="calcBtn" type="button">Tạo / cập nhật lịch <span aria-hidden="true">→</span></button></div>
  </div>
  <div id="calcError" class="text-danger small mt-2" role="alert"></div>
</div></div>
<div class="calendar-progress" id="calendarProgress" hidden><div><span class="eyebrow">TIẾN ĐỘ CỦA BẠN</span><h2 id="planTitle"></h2></div><div class="progress-count"><strong id="completedCount">0</strong><span id="totalCount">/ 0 công việc</span></div><button class="btn btn-sm btn-outline-secondary" id="clearPlan" type="button">Xóa kế hoạch</button></div>
<ol class="timeline" id="timeline" aria-live="polite"></ol>
<p class="calendar-note small text-muted" id="calcNote" hidden><span>i</span> Các mốc là ước tính để lên kế hoạch. Hãy quan sát cây và điều chỉnh theo thực tế.</p>
<script src="<?= url('assets/js/calendar.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
