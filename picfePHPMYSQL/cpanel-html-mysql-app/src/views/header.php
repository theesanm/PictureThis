<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS - Load FIRST to prevent conflicts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      // Simple Tailwind configuration
      tailwind.config = {
        darkMode: 'class'
      };

      // Mark body when Tailwind loads
      setTimeout(function() {
        document.body.classList.add('tailwind-loaded');
      }, 100);
    </script>
    <!-- Custom CSS - Load AFTER Tailwind to allow overrides -->
    <link rel="stylesheet" href="/public/css/style.css">
    <title><?php echo APP_NAME ?? 'PictureThis'; ?></title>
</head>
<body class="bg-gray-900 text-gray-200">
    <header class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-6xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="/" class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">PT</div>
                        <span class="font-semibold">PictureThis</span>
                    </a>
                    
                    <!-- Desktop Navigation -->
                    <nav class="hidden md:flex items-center space-x-2">
                        <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
                        <?php if (!empty($_SESSION['user']) || !empty($user)): ?>
                            <a href="/dashboard" class="px-3 py-1 rounded bg-gray-900 text-gray-100 hover:bg-gray-700">Dashboard</a>
                            <a href="/generate" class="px-3 py-1 rounded hover:bg-gray-700">Generate</a>
                            <a href="/gallery" class="px-3 py-1 rounded hover:bg-gray-700">Gallery</a>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- Desktop User Info -->
                <div class="hidden md:flex items-center space-x-4">
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

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden flex items-center px-3 py-2 border rounded text-gray-200 border-gray-400 hover:text-white hover:border-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="md:hidden hidden mt-4 pb-4 border-t border-gray-700 pt-4">
                <nav class="flex flex-col space-y-2">
                    <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
                    <?php if (!empty($_SESSION['user']) || !empty($user)): ?>
                        <a href="/dashboard" class="px-3 py-2 rounded bg-gray-900 text-gray-100 hover:bg-gray-700">Dashboard</a>
                        <a href="/generate" class="px-3 py-2 rounded hover:bg-gray-700">Generate</a>
                        <a href="/gallery" class="px-3 py-2 rounded hover:bg-gray-700">Gallery</a>
                        
                        <!-- Mobile User Info -->
                        <div class="border-t border-gray-700 pt-4 mt-4">
                            <div class="flex items-center space-x-3 mb-3">
                                <a href="/profile" class="flex items-center space-x-2 px-2 py-1 bg-gray-900 rounded hover:bg-gray-700">
                                    <span class="w-6 h-6 rounded-full bg-yellow-400 flex items-center justify-center text-gray-900 font-bold text-sm">💳</span>
                                    <span class="text-yellow-300 font-semibold"><?php echo htmlspecialchars($displayCredits); ?></span>
                                </a>
                            </div>
                            <a href="/profile" class="block px-3 py-2 text-sm hover:underline"><?php echo htmlspecialchars($displayName); ?></a>
                            <a href="/logout" class="block px-3 py-2 text-sm text-pink-400 hover:underline">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="px-3 py-2 rounded bg-purple-600 text-white">Login</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.add('hidden');
                    }
                });
                
                // Close mobile menu when clicking on a menu item
                mobileMenu.addEventListener('click', function(event) {
                    if (event.target.tagName === 'A') {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>