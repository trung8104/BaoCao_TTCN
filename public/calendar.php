<?php
require_once __DIR__ . '/../includes/helpers.php';
$page_title = 'Lịch canh tác'; $active = 'calendar';
require __DIR__ . '/../includes/header.php';
?>
<div class="calendar-intro">
  <span class="eyebrow">KẾ HOẠCH THEO VƯỜN CỦA BẠN</span>
  <h1>Lịch canh tác cà chua</h1>
  <p>Tạo hoặc cập nhật lịch cho nhiều luống, chậu cùng lúc, rồi theo dõi tiến độ từng kế hoạch. Dữ liệu được lưu trên trình duyệt này.</p>
</div>
<div class="calendar-form card mb-4"><div class="card-body">
  <div class="row g-3 align-items-end">
    <div class="col-12 col-md-6"><label class="form-label fw-semibold" for="cropNames">Tên luống / chậu</label>
      <textarea class="form-control" id="cropNames" rows="2" maxlength="1000" placeholder="Ví dụ:&#10;Cà chua bi ban công&#10;Luống cà chua số 2"></textarea>
      <div class="form-text">Mỗi dòng một tên. Tên đã có sẽ được cập nhật và giữ nguyên tiến độ đã đánh dấu.</div></div>
    <div class="col-12 col-md-3"><label class="form-label fw-semibold" for="sowDate">Ngày gieo hạt</label>
      <input class="form-control" type="date" id="sowDate"></div>
    <div class="col-12 col-md-3"><label class="form-label fw-semibold" for="variety">Nhóm giống</label>
      <select class="form-select" id="variety">
        <option value="determinate">Sinh trưởng hữu hạn (cây thấp)</option>
        <option value="indeterminate">Sinh trưởng vô hạn (cần giàn cao)</option>
      </select></div>
    <div class="col-12 d-grid d-sm-flex justify-content-sm-end"><button class="btn btn-danger" id="calcBtn" type="button">Tạo / cập nhật lịch <span aria-hidden="true">→</span></button></div>
  </div>
  <div id="calcError" class="text-danger small mt-2" role="alert"></div>
</div></div>
<div class="calendar-progress" id="calendarProgress" hidden>
  <div class="calendar-plan-heading"><span class="eyebrow">TIẾN ĐỘ KẾ HOẠCH ĐANG XEM</span><h2 id="planTitle"></h2></div>
  <div class="calendar-plan-picker">
    <span class="form-label small fw-semibold">Chọn luống / chậu</span>
    <details class="calendar-plan-dropdown" id="planDropdown">
      <summary id="selectedPlanLabel">Chọn kế hoạch</summary>
      <div class="calendar-plan-options" id="planOptions" role="list"></div>
    </details>
  </div>
  <div class="progress-count"><strong id="completedCount">0</strong><span id="totalCount">/ 0 công việc</span></div>
  <button class="btn btn-sm btn-outline-danger" id="clearPlans" type="button">Xóa tất cả kế hoạch</button>
</div>
<ol class="timeline" id="timeline" aria-live="polite"></ol>
<p class="calendar-note small text-muted" id="calcNote" hidden><span>i</span> Các mốc là ước tính để lên kế hoạch. Hãy quan sát cây và điều chỉnh theo thực tế.</p>
<script src="<?= url('assets/js/calendar.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
