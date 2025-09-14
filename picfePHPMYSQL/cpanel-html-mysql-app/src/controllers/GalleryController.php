<?php
class GalleryController {
    public function index() {
        // Ensure session and auth
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
        
        $images = [];
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
            $userId = (int)$_SESSION['user']['id'];
            try {
                $stmt = $pdo->prepare('SELECT id, prompt, image_url, created_at, generation_cost FROM images WHERE user_id = ? AND (has_usage_permission IS NULL OR has_usage_permission != -1) ORDER BY created_at DESC');
                $stmt->execute([$userId]);
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
                    error_log('Error fetching gallery images: ' . $e->getMessage());
                }
            }
        } else {
            if (!defined('IS_PRODUCTION') || !IS_PRODUCTION) {
                error_log('GalleryController: No user session or user ID');
            }
        }

        // Get current user info
        $user = null;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['user'])) {
            $user = $_SESSION['user'];
        }

        // Extract variables for view
        extract([
            'images' => $images,
            'user' => $user
        ]);

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/gallery.php';
        include __DIR__ . '/../views/footer.php';
    }
}
