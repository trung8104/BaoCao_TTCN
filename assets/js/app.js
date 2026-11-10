// JS dùng chung
document.addEventListener('DOMContentLoaded', function () {
  // Xác nhận trước khi xóa: <form data-confirm="Nội dung hỏi">
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
      if (!confirm(f.getAttribute('data-confirm'))) ev.preventDefault();
    });
  });
  // Xem trước ảnh chọn từ máy: <input type="file" data-preview="#id">
  document.querySelectorAll('input[type=file][data-preview]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var box = document.querySelector(inp.getAttribute('data-preview'));
      if (!box) return;
      box.innerHTML = '';
      Array.prototype.forEach.call(inp.files, function (file) {
        if (!file.type.startsWith('image/')) return;
        var img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.cssText = 'height:70px;margin:4px;border-radius:6px';
        box.appendChild(img);
      });
    });
  });
});
