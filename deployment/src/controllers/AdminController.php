<?php
class AdminController {
    protected function ensureAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        // Check is_admin flag in DB
        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user']['id']]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($r) || empty($r['is_admin'])) {
            http_response_code(403);
            echo '<h1>403 Forbidden</h1>';
            exit;
        }
    }

    public function index() {
        $this->ensureAdmin();
    // Include global header (contains <head> and Tailwind) then admin sub-header
    include __DIR__ . '/../views/header.php';
    include __DIR__ . '/../views/admin/header.php';
    include __DIR__ . '/../views/admin/index.php';
    include __DIR__ . '/../views/footer.php';
    }

    public function users() {
        $this->ensureAdmin();
        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
        // Handle POST actions: update, delete, toggle_admin, add_credits
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $targetId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            if ($targetId > 0) {
                try {
                    if ($action === 'delete') {
                        // prevent deleting yourself
                        if (!empty($_SESSION['user']) && $_SESSION['user']['id'] == $targetId) {
                            $_SESSION['admin_flash'] = 'Cannot delete the currently logged in admin.';
                        } else {
                            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                            $stmt->execute([$targetId]);
                            $_SESSION['admin_flash'] = 'User deleted.';
                        }
                    } elseif ($action === 'toggle_admin') {
                        $stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ? LIMIT 1');
                        $stmt->execute([$targetId]);
                        $r = $stmt->fetch(PDO::FETCH_ASSOC);
                        $new = empty($r) ? 0 : ($r['is_admin'] ? 0 : 1);
                        $stmt = $pdo->prepare('UPDATE users SET is_admin = ? WHERE id = ?');
                        $stmt->execute([$new, $targetId]);
                        $_SESSION['admin_flash'] = $new ? 'User promoted to admin.' : 'User demoted from admin.';
                    } elseif ($action === 'add_credits') {
                        $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
                        if ($amount !== 0) {
                            $stmt = $pdo->prepare('UPDATE users SET credits = COALESCE(credits,0) + ? WHERE id = ?');
                            $stmt->execute([$amount, $targetId]);
                            // Log the credit addition
                            try {
                                $adminEmail = $_SESSION['user']['email'] ?? 'admin';
                                $desc = "Admin added {$amount} credits (via admin: {$adminEmail})";
                                // Insert using existing schema columns: transaction_type, stripe_payment_id is null
                                $ins = $pdo->prepare('INSERT INTO credit_transactions (user_id, transaction_type, amount, stripe_payment_id, description, created_at) VALUES (?, ?, ?, NULL, ?, NOW())');
                                $ins->execute([$targetId, 'admin_added', $amount, $desc]);
                            } catch (Exception $e) {
                                // ignore logging failures but continue
                            }
                            $_SESSION['admin_flash'] = 'Credits updated.';
                        } else {
                            $_SESSION['admin_flash'] = 'Invalid credit amount.';
                        }
                    } elseif ($action === 'update') {
                        $name = trim($_POST['full_name'] ?? '');
                        $email = trim($_POST['email'] ?? '');
                        $credits = isset($_POST['credits']) ? (int)$_POST['credits'] : null;
                        $fields = [];
                        $params = [];
                        if ($name !== '') { $fields[] = 'full_name = ?'; $params[] = $name; }
                        if ($email !== '') { $fields[] = 'email = ?'; $params[] = $email; }
                        if ($credits !== null) { $fields[] = 'credits = ?'; $params[] = $credits; }
                        if (!empty($fields)) {
                            // if credits is being explicitly set, capture previous value for logging
                            $prevCredits = null;
                            if ($credits !== null) {
                                $q = $pdo->prepare('SELECT credits FROM users WHERE id = ? LIMIT 1');
                                $q->execute([$targetId]);
                                $rr = $q->fetch(PDO::FETCH_ASSOC);
                                $prevCredits = $rr ? (int)$rr['credits'] : 0;
                            }
                            $params[] = $targetId;
                            $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);

                            // log credit adjustment if changed
                            if ($credits !== null) {
                                try {
                                    $delta = $credits - ($prevCredits ?? 0);
                                    $adminId = $_SESSION['user']['id'] ?? null;
                                    $adminEmail = $_SESSION['user']['email'] ?? 'admin';
                                    $desc = "Admin set credits to {$credits} (previous: {$prevCredits}) by {$adminEmail}";
                                    $ins = $pdo->prepare('INSERT INTO credit_transactions (user_id, transaction_type, amount, stripe_payment_id, description, created_at) VALUES (?, ?, ?, NULL, ?, NOW())');
                                    $ins->execute([$targetId, 'admin_adjust', $delta, $desc]);
                                } catch (Exception $e) {
                                    // ignore logging failures
                                }
                            }

                            $_SESSION['admin_flash'] = 'User updated.';
                        } else {
                            $_SESSION['admin_flash'] = 'Nothing to update.';
                        }
                    }
                } catch (Exception $e) {
                    $_SESSION['admin_flash'] = 'Error: ' . $e->getMessage();
                }
            }
            // redirect to avoid form resubmission
            header('Location: /admin/users');
            exit;
        }

        // GET: optional edit id or view id
        $edit_user = null;
        if (isset($_GET['edit'])) {
            $id = (int)$_GET['edit'];
            if ($id > 0) {
                $stmt = $pdo->prepare('SELECT id, full_name, email, credits, is_admin, created_at FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$id]);
                $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }

        $users = $pdo->query('SELECT id, full_name, email, credits, is_admin, created_at FROM users ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/header.php';
        include __DIR__ . '/../views/admin/header.php';
        // provide $users and $edit_user to the view
        include __DIR__ . '/../views/admin/users.php';
        include __DIR__ . '/../views/footer.php';
    }

    public function credits() {
        $this->ensureAdmin();
        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
    $transactions = $pdo->query('SELECT t.*, u.full_name, u.email FROM credit_transactions t LEFT JOIN users u ON u.id = t.user_id ORDER BY t.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
    // Totals (use actual column name transaction_type)
    $totalAdded = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM credit_transactions WHERE transaction_type IN ('admin_added','purchase','topup')")->fetchColumn();
    $totalConsumed = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM credit_transactions WHERE transaction_type IN ('consumed','usage')")->fetchColumn();
    $imagesGenerated = $pdo->query("SELECT COUNT(*) FROM images WHERE 1")->fetchColumn();
    include __DIR__ . '/../views/header.php';
    include __DIR__ . '/../views/admin/header.php';
    // provide $transactions, $totalAdded, $totalConsumed, $imagesGenerated to view
    include __DIR__ . '/../views/admin/credits.php';
    include __DIR__ . '/../views/footer.php';
    }

    public function settings() {
        $this->ensureAdmin();
        require_once __DIR__ . '/../lib/db.php';
        $pdo = get_db();
        // Helper: detect schema shape and load settings into $settings
        $settings = [];
        $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN,0);

        // Handle saving settings
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $credit_cost = isset($_POST['credit_cost']) ? (int)$_POST['credit_cost'] : 10;
            $enhance_cost = isset($_POST['enhance_cost']) ? (int)$_POST['enhance_cost'] : 1;
            $enable_enhance = isset($_POST['enable_enhance']) ? 1 : 0;
            $ai_provider = isset($_POST['ai_provider']) ? $_POST['ai_provider'] : 'openrouter';

            if (in_array('k', $cols)) {
                // key/value table
                $pdo->prepare("INSERT INTO settings (k, v) VALUES ('credit_cost_per_image', ?) ON DUPLICATE KEY UPDATE v = VALUES(v)")->execute([$credit_cost]);
                $pdo->prepare("INSERT INTO settings (k, v) VALUES ('enhance_prompt_cost', ?) ON DUPLICATE KEY UPDATE v = VALUES(v)")->execute([$enhance_cost]);
                $pdo->prepare("INSERT INTO settings (k, v) VALUES ('enable_enhance', ?) ON DUPLICATE KEY UPDATE v = VALUES(v)")->execute([$enable_enhance]);
                $pdo->prepare("INSERT INTO settings (k, v) VALUES ('ai_provider', ?) ON DUPLICATE KEY UPDATE v = VALUES(v)")->execute([$ai_provider]);
            } else {
                // column-per-setting table: update single row (id=1) or insert if empty
                $hasRow = $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
                if ($hasRow) {
                    $stmt = $pdo->prepare('UPDATE settings SET credit_cost_per_image = ?, enhanced_prompt_cost = ?, enhanced_prompt_enabled = ?, ai_provider = ?');
                    $stmt->execute([$credit_cost, $enhance_cost, $enable_enhance, $ai_provider]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO settings (credit_cost_per_image, enhanced_prompt_cost, enhanced_prompt_enabled, ai_provider) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$credit_cost, $enhance_cost, $enable_enhance, $ai_provider]);
                }
            }

            $_SESSION['admin_flash'] = 'Settings saved.';
            header('Location: /admin/settings'); exit;
        }

        if (in_array('k', $cols)) {
            $rows = $pdo->query('SELECT k,v FROM settings')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $settings[$r['k']] = $r['v'];
        } else {
            $row = $pdo->query('SELECT * FROM settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                // map columns straightforwardly
                $settings['credit_cost_per_image'] = $row['credit_cost_per_image'] ?? null;
                $settings['max_free_credits'] = $row['max_free_credits'] ?? null;
                $settings['enhance_prompt_cost'] = $row['enhanced_prompt_cost'] ?? null;
                $settings['enable_enhance'] = $row['enhanced_prompt_enabled'] ?? null;
                $settings['ai_provider'] = $row['ai_provider'] ?? null;
            }
        }

    include __DIR__ . '/../views/header.php';
    include __DIR__ . '/../views/admin/header.php';
    include __DIR__ . '/../views/admin/settings.php';
    include __DIR__ . '/../views/footer.php';
    }

    public function analytics() {
        $this->ensureAdmin();
    include __DIR__ . '/../views/header.php';
    include __DIR__ . '/../views/admin/header.php';
    include __DIR__ . '/../views/admin/analytics.php';
    include __DIR__ . '/../views/footer.php';
    }
}
