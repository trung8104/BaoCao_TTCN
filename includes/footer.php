<?php
// =====================================================================
// includes/footer.php – the bottom of EVERY public page
// (it closes the <main> that header.php opened)
//
// CSS for this file: section "4. FOOTER" in assets/css/style.css
// =====================================================================
?>
</main>

<footer class="site-footer">
    <div class="container site-footer__inner">

        <div class="site-footer__about">
            <strong class="site-footer__brand"><span aria-hidden="true">🍅</span> Sổ tay cà chua</strong>
            <p>Sổ tay hướng dẫn canh tác cà chua – Báo cáo thực tập cơ sở.</p>
        </div>

        <nav class="site-footer__column" aria-label="Khám phá">
            <h2 class="site-footer__title">Khám phá</h2>
            <ul>
                <li><a href="<?= url('public/posts.php') ?>">Bài viết</a></li>
                <li><a href="<?= url('public/pests.php') ?>">Tra cứu sâu bệnh</a></li>
                <li><a href="<?= url('public/calendar.php') ?>">Lịch canh tác</a></li>
            </ul>
        </nav>

        <div class="site-footer__column">
            <h2 class="site-footer__title">Quản trị</h2>
            <ul>
                <li><a href="<?= url('admin/login.php') ?>">Đăng nhập quản trị</a></li>
            </ul>
            <!-- TODO (ask the team): add a contact e-mail here if the project has one -->
        </div>

    </div>
    <div class="site-footer__bottom">© <?= date('Y') ?> Sổ tay cà chua</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>

</html>