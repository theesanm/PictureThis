<?php
// Handle database operations BEFORE any HTML output to prevent headers already sent error
require_once __DIR__ . '/../utils/CSRF.php';
$displayCredits = 0;
$displayName = 'User';

// Check if user is logged in and get fresh data from database
if (!empty($_SESSION['user']) || !empty($user)) {
    $userId = $_SESSION['user']['id'] ?? ($user['id'] ?? null);
    if ($userId) {
        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            if ($pdo) {
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <?php echo CSRF::getTokenMeta(); ?>

    <!-- Critical CSS - Prevent FOUC -->
    <style>
      /* Critical above-the-fold styles to prevent white flash */
      body {
        background: #111827 !important;
        color: #e5e7eb !important;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        margin: 0;
        padding: 0;
        min-height: 100vh;
      }

      /* Ensure header is visible immediately */
      header {
        background: #1f2937 !important;
        border-bottom: 1px solid #374151 !important;
      }

      /* Prevent layout shifts */
      * {
        box-sizing: border-box;
      }

      /* Loading spinner - only show if needed */
      .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #111827;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 1;
        transition: opacity 0.3s ease-out;
      }

      .loading-overlay.hidden {
        opacity: 0;
        pointer-events: none;
      }

      .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #374151;
        border-top: 4px solid #8b5cf6;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }

      .loading-text {
        color: #9ca3af;
        font-size: 0.875rem;
        margin-top: 1rem;
      }
    </style>

    <!-- Preload Tailwind CSS for faster loading -->
    <link rel="preload" href="https://cdn.tailwindcss.com" as="script">

    <!-- Tailwind CSS - Load synchronously to prevent FOUC -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      // Configure Tailwind immediately
      tailwind.config = { darkMode: 'class' };

      // Remove loading overlay once everything is ready
      document.addEventListener('DOMContentLoaded', function() {
        // Small delay to ensure Tailwind is fully loaded
        setTimeout(function() {
          const overlay = document.querySelector('.loading-overlay');
          if (overlay) {
            overlay.classList.add('hidden');
            // Remove from DOM after transition
            setTimeout(function() {
              overlay.remove();
            }, 300);
          }
        }, 200);
      });
    </script>

    <!-- Custom CSS - Load AFTER Tailwind to allow overrides -->
    <link rel="stylesheet" href="/public/css/style.css">

    <!-- Favicon and Icons -->
    <link rel="icon" type="image/svg+xml" href="/public/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/public/favicon.ico">
    <link rel="apple-touch-icon" href="/public/favicon.svg">
    <link rel="manifest" href="/public/site.webmanifest">

    <title><?php echo (defined('APP_NAME') ? APP_NAME : 'PictureThis'); ?></title>
</head>
<body class="bg-gray-900 text-gray-200">
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>
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
                        <?php // session_start() removed - handled in index.php ?>
                        <?php if (!empty($_SESSION['user']) || !empty($user)): ?>
                            <a href="/dashboard" class="px-3 py-1 rounded bg-gray-900 text-gray-100 hover:bg-gray-700">Dashboard</a>
                            <a href="/generate" class="px-3 py-1 rounded hover:bg-gray-700">Generate</a>
                            <a href="/gallery" class="px-3 py-1 rounded hover:bg-gray-700">Gallery</a>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- Desktop User Info -->
                <div class="hidden md:flex items-center space-x-4">
                    <?php // session_start() removed - handled in index.php ?>
                    <?php if (!empty($_SESSION['user']) || !empty($user)): ?>
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
                <button type="button" id="mobile-menu-button" class="md:hidden flex items-center px-3 py-2 border rounded text-gray-200 border-gray-400 hover:text-white hover:border-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation Menu -->
                    <div id="mobile-menu" class="md:hidden hidden mt-4 pb-4 border-t border-gray-700 pt-4">
                <nav class="flex flex-col space-y-2">
                    <?php // session_start() removed - handled in index.php ?>
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