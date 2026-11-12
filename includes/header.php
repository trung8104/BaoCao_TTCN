<?php
// =====================================================================
// includes/header.php – the top of EVERY public page
//
// Variables a page can set BEFORE including this file:
//   $page_title  text shown in the browser tab (optional)
//   $active      which menu item is highlighted: 'home', 'posts', 'pests' or 'calendar'
//
// CSS for this file: section "3. HEADER" in assets/css/style.css
// =====================================================================
require_once __DIR__ . '/helpers.php';

$active = $active ?? '';

// The menu. To add a page, add one more line here.
//   key   = the name a page puts in $active
//   label = the text people see
//   href  = the page's path (url() adds the site's base folder for us)
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

    <!-- 1) Bootstrap first, 2) our own style.css second: later CSS wins, so style.css can override Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">

    <!-- Tells the CSS that JavaScript works, so the phone menu may start closed -->
    <script>
        document.documentElement.classList.add('js');
    </script>
</head>

<body>

    <header class="site-header">
        <!-- "container" is Bootstrap's page width. It keeps the header lined up with the page content. -->
        <div class="container site-header__inner">

            <a class="site-header__brand" href="<?= url('public/index.php') ?>">
                <span aria-hidden="true">🍅</span> Sổ tay cà chua
            </a>

            <!-- Phone only: the button that opens and closes the menu (see assets/js/app.js) -->
            <button class="site-header__toggle" type="button" aria-controls="site-menu" aria-expanded="false" aria-label="Mở menu">
                <span class="site-header__toggle-bar"></span>
                <span class="site-header__toggle-bar"></span>
                <span class="site-header__toggle-bar"></span>
            </button>

            <div class="site-header__menu" id="site-menu">
                <nav aria-label="Menu chính">
                    <ul class="site-menu">
                        <?php foreach ($menu as $item): ?>
                            <?php $isActive = ($active === $item['key']); ?>
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

    <!-- Page content goes here. footer.php closes </main>. -->
    <main class="container py-4">
        <?= flash_render() ?>