<?php
class ProfileController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
        $userId = $_SESSION['user']['id'];

        // Handle POST - update name
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newName = trim($_POST['fullName'] ?? '');
            if ($newName) {
                $stmt = $pdo->prepare('UPDATE users SET full_name = ? WHERE id = ?');
                $stmt->execute([$newName, $userId]);
                // update session copy
                $_SESSION['user']['fullName'] = $newName;
                $_SESSION['profile_success'] = 'Profile updated successfully';
            } else {
                $_SESSION['profile_error'] = 'Name cannot be empty';
            }
            header('Location: /profile');
            exit;
        }

        // Fetch current user row
        $stmt = $pdo->prepare('SELECT id, full_name, email, credits, created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/profile.php';
        include __DIR__ . '/../views/footer.php';
    }
}
