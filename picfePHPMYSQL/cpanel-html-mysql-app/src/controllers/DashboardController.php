<?php
class DashboardController {
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

        $userId = $_SESSION['user']['id'];
        // Refresh user info from DB (credits etc)
        $stmt = $pdo->prepare('SELECT id, full_name, email, credits FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            session_unset();
            session_destroy();
            header('Location: /login');
            exit;
        }

        // Fetch a few recent images for this user
        $recentImages = [];
        try {
            $stmt = $pdo->prepare('SELECT id, filename, prompt, created_at FROM images WHERE user_id = ? ORDER BY created_at DESC LIMIT 6');
            $stmt->execute([$userId]);
            $recentImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // ignore if images table missing or query fails
            error_log('Could not fetch recent images: ' . $e->getMessage());
            $recentImages = [];
        }

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/dashboard.php';
        include __DIR__ . '/../views/footer.php';
    }
}
