<?php
class HomeController {
    public function index() {
        include '../src/views/header.php';
        // Render the home view content here
        echo '<h1>Welcome to the Home Page</h1>';
        include '../src/views/footer.php';
    }
}
?>