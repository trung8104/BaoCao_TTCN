// Lịch gợi ý theo ngày gieo, lưu thông tin kế hoạch và tiến độ trên trình duyệt.
// d > date, t > title, n > note
(function () {
  var MILESTONES = {
    common: [
      { d: 0,  t: 'Gieo hạt', n: 'Gieo vào khay hoặc bầu, giá thể tơi xốp, giữ ẩm nhẹ.' },
      { d: 7,  t: 'Hạt nảy mầm, cây con lên đều', n: 'Để nơi nhiều sáng, tưới nhẹ, tránh úng.' },
      { d: 14, t: 'Cây con có lá thật', n: 'Theo dõi sâu bệnh, tỉa bớt nếu gieo dày.' },
      { d: 25, t: 'Chuẩn bị đem cây ra trồng', n: 'Cây con khỏe, khoảng 4–6 lá thật. Cho cây làm quen nắng 2–3 ngày trước khi trồng.' },
      { d: 28, t: 'Trồng cây ra vườn', n: 'Trồng buổi chiều mát, tưới đẫm sau trồng.' }
    ],
    determinate: [
      { d: 40, t: 'Làm giàn / cắm cọc, bón thúc lần 1', n: 'Cắm cọc để cây không đổ, bón thúc theo hướng dẫn phân bón.' },
      { d: 55, t: 'Ra hoa rộ', n: 'Giữ ẩm đều, tránh tưới thất thường để hạn chế rụng hoa, nứt quả.' },
      { d: 70, t: 'Đậu quả, bón thúc lần 2', n: 'Chú ý sâu đục quả và các bệnh trên quả.' },
      { d: 90, t: 'Bắt đầu thu hoạch', n: 'Hái khi quả chuyển màu, hái lúc trời mát.' },
      { d: 110, t: 'Thu hoạch rộ, kết thúc vụ', n: 'Thu gom tàn dư cây để hạn chế mầm bệnh cho vụ sau.' }
    ],
    indeterminate: [
      { d: 38, t: 'Làm giàn cao, buộc dây, bắt đầu tỉa chồi nách', n: 'Tỉa chồi nách thường xuyên, giữ 1–2 thân chính.' },
      { d: 50, t: 'Ra hoa, bón thúc lần 1', n: 'Giữ ẩm đều, hỗ trợ thụ phấn nếu trồng trong nhà lưới.' },
      { d: 65, t: 'Đậu quả, tỉa lá già gốc', n: 'Tỉa lá già giúp thông thoáng, giảm bệnh.' },
      { d: 90, t: 'Bắt đầu thu hoạch', n: 'Thu hoạch kéo dài nhiều đợt.' },
      { d: 130, t: 'Thu hoạch rộ, đánh giá kết thúc vụ', n: 'Cắt ngọn khi cây quá cao, thu gom tàn dư cuối vụ.' }
    ]
  };
  var STORAGE_KEY = 'tomatoGuideCropCalendar';
  var dayNames = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
  function fmt(d) {
    return dayNames[d.getDay()] + ', ' + String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
  }
  function esc(s) { var x = document.createElement('div'); x.textContent = s; return x.innerHTML; }
  function readPlan() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null'); } catch (e) { return null; }
  }
  function savePlan(plan) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(plan)); return true; } catch (e) { return false; }
  }
  var input = document.getElementById('sowDate');
  var cropName = document.getElementById('cropName');
  var variety = document.getElementById('variety');
  var list = document.getElementById('timeline');
  var progress = document.getElementById('calendarProgress');
  function renderPlan(plan, persist) {
    var err = document.getElementById('calcError');
    err.textContent = '';
    list.innerHTML = '';
    if (!plan || !plan.date) { err.textContent = 'Hãy chọn ngày gieo hạt.'; return; }
    if (!/^\d{4}-\d{2}-\d{2}$/.test(plan.date)) { err.textContent = 'Ngày gieo hạt chưa hợp lệ.'; return; }
    if (!MILESTONES[plan.variety]) plan.variety = 'determinate';
    var parts = plan.date.split('-');
    var start = new Date(+parts[0], +parts[1] - 1, +parts[2]);
    if (isNaN(start.getTime())) { err.textContent = 'Ngày gieo hạt chưa hợp lệ.'; return; }
    var items = MILESTONES.common.concat(MILESTONES[plan.variety]).sort(function (a, b) { return a.d - b.d; });
    plan.done = plan.done || {};
    document.getElementById('planTitle').textContent = plan.name || 'Lịch cà chua';
    document.getElementById('totalCount').textContent = '/ ' + items.length + ' công việc';
    items.forEach(function (m, index) {
      var date = new Date(start.getTime()); date.setDate(date.getDate() + m.d);
      var li = document.createElement('li');
      var checked = !!plan.done[index];
      li.className = checked ? 'is-complete' : '';
      li.innerHTML = '<label class="task-check"><input type="checkbox" data-task="' + index + '"' + (checked ? ' checked' : '') + '><span aria-hidden="true"></span><span class="visually-hidden">Đánh dấu ' + esc(m.t) + ' đã hoàn thành</span></label>'
                   + '<div class="timeline-content"><div class="date">' + esc(fmt(date)) + ' <span class="text-muted fw-normal">(ngày thứ ' + m.d + ')</span></div>'
                   + '<div class="fw-semibold task-title">' + esc(m.t) + '</div><div class="text-muted small">' + esc(m.n) + '</div></div>';
      list.appendChild(li);
    });
    input.value = plan.date;
    cropName.value = plan.name || '';
    variety.value = plan.variety;
    progress.hidden = false;
    document.getElementById('calcNote').hidden = false;
    updateProgress(plan.done, items.length);
    if (persist && !savePlan(plan)) err.textContent = 'Không lưu được kế hoạch trên trình duyệt này.';
  }
  function updateProgress(done, total) {
    var count = Object.keys(done || {}).filter(function (key) { return done[key]; }).length;
    document.getElementById('completedCount').textContent = count;
    document.getElementById('totalCount').textContent = '/ ' + total + ' công việc';
  }
  document.getElementById('calcBtn').addEventListener('click', function () {
    var prior = readPlan() || {};
    renderPlan({ name: cropName.value.trim(), date: input.value, variety: variety.value, done: prior.done || {} }, true);
  });
  list.addEventListener('change', function (event) {
    if (!event.target.matches('input[data-task]')) return;
    var plan = readPlan();
    if (!plan) return;
    plan.done = plan.done || {};
    plan.done[event.target.getAttribute('data-task')] = event.target.checked;
    savePlan(plan);
    renderPlan(plan, false);
  });
  document.getElementById('clearPlan').addEventListener('click', function () {
    try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    var today = new Date();
    input.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
    cropName.value = '';
    variety.value = 'determinate';
    renderPlan({ name: '', date: input.value, variety: variety.value, done: {} }, true);
  });
  var saved = readPlan();
  if (saved) renderPlan(saved, false);
  else {
    var today = new Date();
    input.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
    renderPlan({ name: '', date: input.value, variety: variety.value, done: {} }, true);
  }
})();