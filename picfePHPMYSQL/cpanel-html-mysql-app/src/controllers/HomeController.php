<?php
class HomeController {
    public function index() {
    include __DIR__ . '/../views/header.php';
    include __DIR__ . '/../views/home.php';
    include __DIR__ . '/../views/footer.php';
    }
}
?>