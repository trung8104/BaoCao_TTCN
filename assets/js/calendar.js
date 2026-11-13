// Lịch gợi ý theo ngày gieo, lưu thông tin kế hoạch và tiến độ trên trình duyệt.
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
  var selectedIndex = -1;
  var input = document.getElementById('sowDate');
  var cropNames = document.getElementById('cropNames');
  var variety = document.getElementById('variety');
  var list = document.getElementById('timeline');
  var progress = document.getElementById('calendarProgress');
  var error = document.getElementById('calcError');
  var planDropdown = document.getElementById('planDropdown');
  var planOptions = document.getElementById('planOptions');
  var selectedPlanLabel = document.getElementById('selectedPlanLabel');

  function fmt(d) {
    return dayNames[d.getDay()] + ', ' + String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
  }
  function esc(s) { var x = document.createElement('div'); x.textContent = s; return x.innerHTML; }
  function readPlans() {
    try {
      var saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
      if (!saved) return [];
      var plans = Array.isArray(saved) ? saved : [saved];
      return plans.filter(function (plan) { return plan && typeof plan === 'object'; }).map(function (plan) {
        if (!plan.name) plan.name = 'Lịch cà chua';
        if (!plan.done || typeof plan.done !== 'object') plan.done = {};
        return plan;
      });
    } catch (e) { return []; }
  }
  function savePlans(plans) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(plans)); return true; } catch (e) { return false; }
  }
  function getItems(plan) {
    var type = MILESTONES[plan.variety] ? plan.variety : 'determinate';
    return MILESTONES.common.concat(MILESTONES[type]).sort(function (a, b) { return a.d - b.d; });
  }
  function updateProgress(done, total) {
    var count = Object.keys(done || {}).filter(function (key) { return done[key]; }).length;
    document.getElementById('completedCount').textContent = count;
    document.getElementById('totalCount').textContent = '/ ' + total + ' công việc';
  }
  function renderPlans(plans, index) {
    list.innerHTML = '';
    document.getElementById('calcNote').hidden = true;
    error.textContent = '';
    if (!plans.length) {
      selectedIndex = -1;
      progress.hidden = true;
      planDropdown.open = false;
      planOptions.innerHTML = '';
      return;
    }
    selectedIndex = Math.max(0, Math.min(index, plans.length - 1));
    planDropdown.open = false;
    planOptions.innerHTML = '';
    plans.forEach(function (plan, planIndex) {
      var name = plan.name || 'Lịch cà chua';
      var row = document.createElement('div');
      row.className = 'calendar-plan-option';
      row.setAttribute('role', 'listitem');
      var chooseButton = document.createElement('button');
      chooseButton.type = 'button';
      chooseButton.className = 'calendar-plan-choice' + (planIndex === selectedIndex ? ' is-selected' : '');
      chooseButton.setAttribute('data-plan-index', planIndex);
      chooseButton.setAttribute('aria-pressed', planIndex === selectedIndex ? 'true' : 'false');
      chooseButton.textContent = name;
      var deleteButton = document.createElement('button');
      deleteButton.type = 'button';
      deleteButton.className = 'calendar-plan-delete';
      deleteButton.setAttribute('data-delete-index', planIndex);
      deleteButton.setAttribute('aria-label', 'Xóa kế hoạch ' + name);
      deleteButton.title = 'Xóa kế hoạch này';
      deleteButton.textContent = '×';
      row.appendChild(chooseButton);
      row.appendChild(deleteButton);
      planOptions.appendChild(row);
    });
    var plan = plans[selectedIndex];
    var selectedName = plan.name || 'Lịch cà chua';
    selectedPlanLabel.textContent = selectedName;
    document.getElementById('planTitle').textContent = selectedName;
    progress.hidden = false;
    if (!plan.date || !/^\d{4}-\d{2}-\d{2}$/.test(plan.date)) {
      error.textContent = 'Ngày gieo hạt của kế hoạch chưa hợp lệ.';
      return;
    }
    var parts = plan.date.split('-');
    var start = new Date(+parts[0], +parts[1] - 1, +parts[2]);
    if (isNaN(start.getTime()) || start.getFullYear() !== +parts[0] || start.getMonth() !== +parts[1] - 1 || start.getDate() !== +parts[2]) {
      error.textContent = 'Ngày gieo hạt của kế hoạch chưa hợp lệ.';
      return;
    }
    var items = getItems(plan);
    plan.done = plan.done || {};
    items.forEach(function (milestone, taskIndex) {
      var date = new Date(start.getTime());
      date.setDate(date.getDate() + milestone.d);
      var li = document.createElement('li');
      var checked = !!plan.done[taskIndex];
      li.className = checked ? 'is-complete' : '';
      li.innerHTML = '<label class="task-check"><input type="checkbox" data-task="' + taskIndex + '"' + (checked ? ' checked' : '') + '><span aria-hidden="true"></span><span class="visually-hidden">Đánh dấu ' + esc(milestone.t) + ' đã hoàn thành</span></label>'
                   + '<div class="timeline-content"><div class="date">' + esc(fmt(date)) + ' <span class="text-muted fw-normal">(ngày thứ ' + milestone.d + ')</span></div>'
                   + '<div class="fw-semibold task-title">' + esc(milestone.t) + '</div><div class="text-muted small">' + esc(milestone.n) + '</div></div>';
      list.appendChild(li);
    });
    document.getElementById('calcNote').hidden = false;
    updateProgress(plan.done, items.length);
  }
  function todayString() {
    var today = new Date();
    return today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
  }

  document.getElementById('calcBtn').addEventListener('click', function () {
    var names = cropNames.value.split(/\r?\n/).map(function (name) { return name.trim(); }).filter(Boolean);
    var uniqueNames = [];
    var seen = {};
    names.forEach(function (name) {
      var key = name.toLowerCase();
      if (!seen[key]) { seen[key] = true; uniqueNames.push(name); }
    });
    if (!uniqueNames.length) { error.textContent = 'Hãy nhập ít nhất một tên luống hoặc chậu.'; return; }
    if (uniqueNames.some(function (name) { return name.length > 50; })) {
      error.textContent = 'Mỗi tên luống hoặc chậu không được dài quá 50 ký tự.';
      return;
    }
    if (!input.value) { error.textContent = 'Hãy chọn ngày gieo hạt.'; return; }
    var parts = input.value.split('-');
    var date = new Date(+parts[0], +parts[1] - 1, +parts[2]);
    if (parts.length !== 3 || isNaN(date.getTime()) || date.getFullYear() !== +parts[0] || date.getMonth() !== +parts[1] - 1 || date.getDate() !== +parts[2]) {
      error.textContent = 'Ngày gieo hạt chưa hợp lệ.';
      return;
    }

    var plans = readPlans();
    var firstIndex = plans.length;
    uniqueNames.forEach(function (name) {
      var key = name.toLowerCase();
      var planIndex = plans.findIndex(function (plan) { return (plan.name || '').toLowerCase() === key; });
      if (planIndex < 0) {
        planIndex = plans.length;
        plans.push({ name: name, date: input.value, variety: variety.value, done: {} });
      } else {
        plans[planIndex] = { name: name, date: input.value, variety: variety.value, done: plans[planIndex].done || {} };
      }
      if (planIndex < firstIndex) firstIndex = planIndex;
    });
    if (!savePlans(plans)) { error.textContent = 'Không lưu được kế hoạch trên trình duyệt này.'; return; }
    renderPlans(plans, firstIndex);
  });

  function fillForm(plan) {
    if (!plan) return;
    cropNames.value = plan.name || '';
    input.value = plan.date || '';
    variety.value = MILESTONES[plan.variety] ? plan.variety : 'determinate';
  }

  function deletePlanAt(index) {
    var plans = readPlans();
    if (index < 0 || index >= plans.length) return;
    var previousIndex = selectedIndex;
    var selectedWasDeleted = index === previousIndex;
    plans.splice(index, 1);
    if (!savePlans(plans)) {
      error.textContent = 'Không xóa được kế hoạch trên trình duyệt này.';
      return;
    }
    if (!plans.length) {
      cropNames.value = '';
      input.value = todayString();
      variety.value = 'determinate';
      renderPlans([], -1);
      return;
    }
    var nextIndex = selectedWasDeleted ? Math.min(index, plans.length - 1) : (index < previousIndex ? previousIndex - 1 : previousIndex);
    renderPlans(plans, nextIndex);
    if (selectedWasDeleted) fillForm(plans[nextIndex]);
  }

  planOptions.addEventListener('click', function (event) {
    var deleteButton = event.target.closest('button[data-delete-index]');
    if (deleteButton) {
      deletePlanAt(Number(deleteButton.getAttribute('data-delete-index')));
      return;
    }
    var chooseButton = event.target.closest('button[data-plan-index]');
    if (!chooseButton) return;
    var plans = readPlans();
    selectedIndex = Number(chooseButton.getAttribute('data-plan-index'));
    renderPlans(plans, selectedIndex);
    fillForm(plans[selectedIndex]);
  });

  list.addEventListener('change', function (event) {
    if (!event.target.matches('input[data-task]')) return;
    var plans = readPlans();
    var plan = plans[selectedIndex];
    if (!plan) return;
    plan.done = plan.done || {};
    plan.done[event.target.getAttribute('data-task')] = event.target.checked;
    if (!savePlans(plans)) {
      error.textContent = 'Không lưu được tiến độ trên trình duyệt này.';
      return;
    }
    renderPlans(plans, selectedIndex);
  });

  document.getElementById('clearPlans').addEventListener('click', function () {
    if (!savePlans([])) {
      error.textContent = 'Không xóa được kế hoạch trên trình duyệt này.';
      return;
    }
    cropNames.value = '';
    input.value = todayString();
    variety.value = 'determinate';
    renderPlans([], -1);
  });

  var savedPlans = readPlans();
  input.value = todayString();
  if (savedPlans.length) {
    renderPlans(savedPlans, 0);
    cropNames.value = savedPlans[0].name || '';
    input.value = savedPlans[0].date || input.value;
    variety.value = MILESTONES[savedPlans[0].variety] ? savedPlans[0].variety : 'determinate';
  }
})();
