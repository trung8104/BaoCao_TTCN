// Lịch canh tác tự tính theo ngày gieo. Các mốc (số ngày sau gieo) mang tính tham khảo.
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
  var dayNames = ['Chủ nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
  function fmt(d) {
    return dayNames[d.getDay()] + ', ' + String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
  }
  function esc(s) { var x = document.createElement('div'); x.textContent = s; return x.innerHTML; }

  var input = document.getElementById('sowDate');
  var today = new Date();
  input.value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

  document.getElementById('calcBtn').addEventListener('click', function () {
    var err = document.getElementById('calcError'), list = document.getElementById('timeline');
    err.textContent = ''; list.innerHTML = '';
    if (!input.value) { err.textContent = 'Hãy chọn ngày gieo hạt.'; return; }
    var parts = input.value.split('-');
    var start = new Date(+parts[0], +parts[1] - 1, +parts[2]);
    var type = document.getElementById('variety').value;
    var items = MILESTONES.common.concat(MILESTONES[type]).sort(function (a, b) { return a.d - b.d; });
    items.forEach(function (m) {
      var date = new Date(start.getTime()); date.setDate(date.getDate() + m.d);
      var li = document.createElement('li');
      li.innerHTML = '<div class="date">' + esc(fmt(date)) + ' <span class="text-muted fw-normal">(ngày thứ ' + m.d + ')</span></div>'
                   + '<div class="fw-semibold">' + esc(m.t) + '</div><div class="text-muted small">' + esc(m.n) + '</div>';
      list.appendChild(li);
    });
    document.getElementById('calcNote').hidden = false;
  });
  document.getElementById('calcBtn').click();
})();
