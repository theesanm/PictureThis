<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <!-- Tailwind CDN for quick styling (suitable for cPanel/static PHP) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Your Application Title</title>
</head>
<body class="bg-gray-900 text-gray-200">
    <header class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="/" class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">PT</div>
                    <span class="font-semibold">PictureThis</span>
                </a>
                <nav class="flex items-center space-x-2">
                    <a href="/dashboard" class="px-3 py-1 rounded bg-gray-900 text-gray-100">Dashboard</a>
                    <a href="/generate" class="px-3 py-1 rounded hover:bg-gray-700">Generate</a>
                    <a href="/gallery" class="px-3 py-1 rounded hover:bg-gray-700">Gallery</a>
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
                <?php if (!empty($_SESSION['user']) || !empty($user)): ?>
                    <?php
                    // Prefer a fresh read from DB for credits and name to avoid stale session values.
                    $displayCredits = 0;
                    $displayName = 'User';
                    $userId = $_SESSION['user']['id'] ?? ($user['id'] ?? null);
                    if ($userId) {
                        try {
                            require_once __DIR__ . '/../lib/db.php';
                            $pdo = get_db();
                            $stmt = $pdo->prepare('SELECT credits, full_name, email FROM users WHERE id = ? LIMIT 1');
                            $stmt->execute([$userId]);
                            $fresh = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($fresh) {
                                $displayCredits = $fresh['credits'] !== null ? $fresh['credits'] : 0;
                                $displayName = $fresh['full_name'] ?: $fresh['email'] ?: ($user['full_name'] ?? $_SESSION['user']['email'] ?? 'User');
                                // update session cache lightly
                                if (!empty($_SESSION['user'])) {
                                    $_SESSION['user']['credits'] = $displayCredits;
                                    $_SESSION['user']['full_name'] = $displayName;
                                }
                            }
                        } catch (Exception $e) {
                            // fallback to session values on error
                            $displayCredits = $user['credits'] ?? ($_SESSION['user']['credits'] ?? 0);
                            $displayName = $user['full_name'] ?? ($_SESSION['user']['full_name'] ?? ($_SESSION['user']['email'] ?? 'User'));
                        }
                    } else {
                        $displayCredits = $user['credits'] ?? ($_SESSION['user']['credits'] ?? 0);
                        $displayName = $user['full_name'] ?? ($_SESSION['user']['full_name'] ?? ($_SESSION['user']['email'] ?? 'User'));
                    }
                    ?>
                    <div class="flex items-center space-x-3">
                        <a href="/profile" class="flex items-center space-x-2 px-2 py-1 bg-gray-900 rounded hover:bg-gray-700">
                            <span class="w-6 h-6 rounded-full bg-yellow-400 flex items-center justify-center text-gray-900 font-bold text-sm">💳</span>
                            <span class="text-yellow-300 font-semibold"><?php echo htmlspecialchars($displayCredits); ?></span>
                        </a>
                        <a href="/profile" class="text-sm hover:underline"><?php echo htmlspecialchars($displayName); ?></a>
                        <a href="/logout" class="text-sm text-pink-400 hover:underline">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="/login" class="px-3 py-1 rounded bg-purple-600 text-white">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>