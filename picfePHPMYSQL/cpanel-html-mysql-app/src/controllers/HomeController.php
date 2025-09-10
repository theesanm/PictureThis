<?php
class HomeController {
    public function index() {
    include __DIR__ . '/../views/header.php';
    include __DIR__ . '/../views/home.php';
    include __DIR__ . '/../views/footer.php';
    }

    public function about() {
        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/about.php';
        include __DIR__ . '/../views/footer.php';
    }

    public function privacy() {
        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/privacy.php';
        include __DIR__ . '/../views/footer.php';
    }

    public function terms() {
        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/terms.php';
        include __DIR__ . '/../views/footer.php';
    }
}
?>