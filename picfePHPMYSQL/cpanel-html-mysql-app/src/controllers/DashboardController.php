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

        // Fetch a few recent images for this user (fetch more than needed to account for duplicates)
        $recentImages = [];
        try {
            $stmt = $pdo->prepare('SELECT id, image_url, prompt, created_at FROM images WHERE user_id = ? AND image_url IS NOT NULL AND image_url != "" ORDER BY created_at DESC LIMIT 12');
            $stmt->execute([$userId]);
            $recentImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug logging
            error_log('Dashboard: Found ' . count($recentImages) . ' recent images for user ' . $userId);
            foreach ($recentImages as $img) {
                error_log('Dashboard Image: ID=' . $img['id'] . ', URL=' . substr($img['image_url'], 0, 50) . '...');
            }
            
            // Remove duplicates based on image_url to ensure variety
            $uniqueImages = [];
            $seenUrls = [];
            foreach ($recentImages as $img) {
                if (!in_array($img['image_url'], $seenUrls)) {
                    $uniqueImages[] = $img;
                    $seenUrls[] = $img['image_url'];
                    error_log('Dashboard: Added unique image ID=' . $img['id'] . ', URL=' . substr($img['image_url'], 0, 50) . '...');
                } else {
                    error_log('Dashboard: Skipped duplicate image ID=' . $img['id'] . ', URL=' . substr($img['image_url'], 0, 50) . '...');
                }
            }
            $recentImages = array_slice($uniqueImages, 0, 6); // Take only the first 6 unique images
            
            // Ensure all required fields have values
            foreach ($recentImages as &$img) {
                $img['image_url'] = $img['image_url'] ?? '';
                $img['prompt'] = $img['prompt'] ?? 'Generated image';
            }
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
