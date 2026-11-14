<?php
// =====================================================================
// includes/header.php – phần ĐẦU của MỌI trang công khai
//
// Biến mà mỗi trang có thể đặt TRƯỚC khi include file này:
//   $page_title   chữ hiện trên tab trình duyệt (không bắt buộc)
//   $active       mục menu được tô sáng: 'home', 'posts', 'pests' hoặc 'calendar'
//   $page_layout  'wide'  = nội dung rộng hết màn hình (hiện chỉ trang Tra cứu sâu bệnh dùng)
//                 bỏ trống = nội dung nằm trong khung giữa như các trang khác
//
// CSS của file này: mục "3. HEADER" trong assets/css/style.css
// =====================================================================
require_once __DIR__ . '/helpers.php';

// Dấu ?? nghĩa là "nếu biến chưa được đặt thì dùng giá trị phía sau"
$active = $active ?? '';
$page_layout = $page_layout ?? '';
$isWide = ($page_layout === 'wide'); // true nếu trang yêu cầu bố cục rộng

// Danh sách menu. Muốn thêm trang mới: thêm một dòng vào đây.
//   key   = tên mà trang gán cho $active
//   label = chữ người dùng nhìn thấy
//   href  = đường dẫn của trang (hàm url() tự thêm thư mục gốc của website)
$menu = [
    ['key' => 'home',     'label' => 'Trang chủ',        'href' => 'public/index.php'],
    ['key' => 'posts',    'label' => 'Bài viết',         'href' => 'public/posts.php'],
    ['key' => 'pests',    'label' => 'Tra cứu sâu bệnh', 'href' => 'public/pests.php'],
    ['key' => 'calendar', 'label' => 'Lịch canh tác',    'href' => 'public/calendar.php'],
];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? e($page_title) . ' | ' : '' ?>Sổ tay canh tác cà chua</title>

    <!-- Nạp Bootstrap TRƯỚC, style.css của nhóm SAU: CSS nạp sau sẽ thắng, nên style.css ghi đè được Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">

    <!-- Báo cho CSS biết trình duyệt có chạy JavaScript (nếu có, menu điện thoại mới được ẩn lúc đầu) -->
    <script>
        document.documentElement.classList.add('js');
    </script>
</head>

<body>

    <header class="site-header">
        <!-- "container" là khung giữa của Bootstrap, giúp header thẳng hàng với nội dung trang -->
        <div class="container site-header__inner">

            <a class="site-header__brand" href="<?= url('public/index.php') ?>">
                <span aria-hidden="true">🍅</span> Sổ tay cà chua
            </a>

            <!-- Chỉ hiện trên điện thoại: nút mở/đóng menu (code ở assets/js/app.js) -->
            <button class="site-header__toggle" type="button" aria-controls="site-menu" aria-expanded="false" aria-label="Mở menu">
                <span class="site-header__toggle-bar"></span>
                <span class="site-header__toggle-bar"></span>
                <span class="site-header__toggle-bar"></span>
            </button>

            <div class="site-header__menu" id="site-menu">
                <nav aria-label="Menu chính">
                    <ul class="site-menu">
                        <?php foreach ($menu as $item): ?>
                            <?php $isActive = ($active === $item['key']); // true nếu đây là trang đang xem 
                            ?>
                            <li>
                                <a class="site-menu__link<?= $isActive ? ' is-active' : '' ?>"
                                    href="<?= url($item['href']) ?>"
                                    <?= $isActive ? 'aria-current="page"' : '' ?>><?= e($item['label']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <form class="site-search" role="search" action="<?= url('public/search.php') ?>" method="get">
                    <label class="visually-hidden" for="site-search-input">Tìm kiếm</label>
                    <input class="site-search__input" id="site-search-input" type="search" name="q"
                        placeholder="Tìm bài viết, sâu bệnh…" value="<?= e($_GET['q'] ?? '') ?>">
                    <button class="site-search__button" type="submit">Tìm</button>
                </form>
            </div>

        </div>
    </header>

    <?php $flash = flash_render(); // thông báo nhanh (đã lưu/đã xóa...), chỉ hiện một lần 
    ?>
    <!-- Nội dung trang nằm trong <main>. footer.php sẽ đóng thẻ </main>.
         Bố cục thường: "container py-4" (khung giữa có đệm trên/dưới).
         Bố cục rộng ($page_layout = 'wide'): "page-wide" (không khung, nội dung chạm hai mép màn hình). -->
    <main class="<?= $isWide ? 'page-wide' : 'container py-4' ?>">
        <?php if ($flash !== ''): ?>
            <!-- Ở bố cục rộng, thông báo được đặt vào khung giữa cho khỏi dính mép màn hình -->
            <div class="<?= $isWide ? 'container pt-3' : '' ?>"><?= $flash ?></div>
        <?php endif; ?>