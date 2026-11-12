// JS dùng chung
document.addEventListener("DOMContentLoaded", function () {
    // Menu điện thoại: nút .site-header__toggle mở/đóng #site-menu (CSS: mục "3. HEADER" trong style.css)
    const menuButton = document.querySelector(".site-header__toggle");
    const menu = document.getElementById("site-menu");
    if (menuButton && menu) {
        menuButton.addEventListener("click", function () {
            const isOpen = menu.classList.toggle("is-open"); // thêm/bớt class is-open
            menuButton.setAttribute("aria-expanded", String(isOpen)); // báo cho trình đọc màn hình
            menuButton.setAttribute(
                "aria-label",
                isOpen ? "Đóng menu" : "Mở menu",
            );
        });
    }
    // Xác nhận trước khi xóa: <form data-confirm="Nội dung hỏi">
    document.querySelectorAll("form[data-confirm]").forEach(function (f) {
        f.addEventListener("submit", function (ev) {
            if (!confirm(f.getAttribute("data-confirm"))) ev.preventDefault();
        });
    });
    // Xem trước ảnh chọn từ máy: <input type="file" data-preview="#id">
    document
        .querySelectorAll("input[type=file][data-preview]")
        .forEach(function (inp) {
            inp.addEventListener("change", function () {
                const box = document.querySelector(
                    inp.getAttribute("data-preview"),
                );
                if (!box) return;
                box.innerHTML = "";
                Array.prototype.forEach.call(inp.files, function (file) {
                    if (!file.type.startsWith("image/")) return;
                    const img = document.createElement("img");
                    img.src = URL.createObjectURL(file);
                    img.style.cssText = "height:70px;margin:4px;border-radius:6px";
                    box.appendChild(img);
                });
            });
        });
});
