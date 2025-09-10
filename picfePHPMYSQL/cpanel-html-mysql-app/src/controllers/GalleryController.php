<?php
class GalleryController {
    public function index() {
        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/gallery.php';
        include __DIR__ . '/../views/footer.php';
    }
}
