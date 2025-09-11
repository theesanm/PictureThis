<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS with error handling -->
    <script>
      // Load Tailwind with fallback
      (function() {
        var script = document.createElement('script');
        script.src = 'https://cdn.tailwindcss.com';
        script.onload = function() {
          // Tailwind loaded successfully
          tailwind.config = { darkMode: 'class' };
          setTimeout(function() {
            document.body.classList.add('tailwind-loaded');
          }, 100);
        };
        script.onerror = function() {
          // Tailwind failed to load, add fallback CSS
          console.log('Tailwind CDN failed, using fallback styles');
          var fallbackCSS = document.createElement('style');
          fallbackCSS.textContent = `
            /* Basic reset and typography */
            * { box-sizing: border-box; }
            body {
              font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
              margin: 0;
              padding: 20px;
              background: #ffffff !important;
              color: #333333 !important;
              line-height: 1.6;
            }

            /* Layout */
            .container, .max-w-7xl, .max-w-6xl { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
            main { min-height: 100vh; }

            /* Header */
            header, .bg-gray-800 { background: #2c3e50 !important; color: #ecf0f1 !important; padding: 1rem; margin-bottom: 2rem; }
            .flex { display: flex !important; }
            .items-center { align-items: center !important; }
            .justify-between { justify-content: space-between !important; }
            .space-x-4 > * + * { margin-left: 1rem !important; }
            .space-x-2 > * + * { margin-left: 0.5rem !important; }

            /* Navigation */
            nav a, .nav a {
              color: #ecf0f1 !important;
              text-decoration: none !important;
              padding: 0.5rem 1rem !important;
              background: #34495e !important;
              border-radius: 4px !important;
              margin: 0.25rem !important;
              display: inline-block !important;
            }
            nav a:hover, .nav a:hover { background: #2980b9 !important; }

            /* Hero section */
            .hero, .bg-gradient-to-br {
              background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
              color: white !important;
              padding: 3rem 1rem !important;
              border-radius: 8px !important;
              margin: 1rem 0 !important;
              text-align: center !important;
            }

            /* Buttons */
            .btn, a[href*="/register"], a[href*="/login"] {
              display: inline-block !important;
              padding: 0.75rem 1.5rem !important;
              background: #3498db !important;
              color: white !important;
              text-decoration: none !important;
              border-radius: 6px !important;
              margin: 0.5rem !important;
              border: none !important;
              cursor: pointer !important;
            }
            .btn:hover { background: #2980b9 !important; }

            /* Text styles */
            h1 { font-size: 2.5rem !important; font-weight: bold !important; margin-bottom: 1rem !important; color: inherit !important; }
            h2 { font-size: 2rem !important; font-weight: bold !important; margin: 2rem 0 1rem 0 !important; color: inherit !important; }
            h3 { font-size: 1.5rem !important; font-weight: bold !important; margin: 1.5rem 0 1rem 0 !important; color: inherit !important; }
            p { margin-bottom: 1rem !important; color: inherit !important; }

            /* Stats */
            .stats { display: flex !important; justify-content: space-around !important; margin: 2rem 0 !important; flex-wrap: wrap !important; }
            .stat { text-align: center !important; margin: 1rem !important; }
            .stat-number { font-size: 2rem !important; font-weight: bold !important; color: #f39c12 !important; }

            /* Footer */
            footer { background: #2c3e50 !important; color: #ecf0f1 !important; padding: 2rem 0 !important; margin-top: 3rem !important; text-align: center !important; }

            /* Responsive */
            @media (max-width: 768px) {
              .flex { flex-direction: column !important; }
              h1 { font-size: 2rem !important; }
              .stats { flex-direction: column !important; }
            }
          `;
          document.head.appendChild(fallbackCSS);
          document.body.classList.add('fallback-loaded');
        };
        document.head.appendChild(script);
      })();
    </script>
    <!-- Custom CSS - Load AFTER Tailwind to allow overrides -->
    <link rel="stylesheet" href="/public/css/style.css">
    <title><?php echo APP_NAME ?? 'PictureThis'; ?></title>
</head>
<body class="bg-gray-900 text-gray-200">
    <?php
    // Initialize user variables to prevent undefined variable errors
    $displayCredits = 0;
    $displayName = 'Guest';
    $user = null;

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in and get their data
    if (!empty($_SESSION['user'])) {
        $user = $_SESSION['user'];
        $displayCredits = $user['credits'] ?? 0;
        $displayName = $user['full_name'] ?? $user['email'] ?? 'User';

        // Try to get fresh data from database
        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            $stmt = $pdo->prepare('SELECT credits, full_name, email FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$user['id']]);
            $fresh = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fresh) {
                $displayCredits = $fresh['credits'] !== null ? $fresh['credits'] : 0;
                $displayName = $fresh['full_name'] ?: $fresh['email'] ?: $displayName;
                // Update session with fresh data
                $_SESSION['user']['credits'] = $displayCredits;
                $_SESSION['user']['full_name'] = $displayName;
            }
        } catch (Exception $e) {
            // Keep session values if database query fails
            error_log('Failed to refresh user data: ' . $e->getMessage());
        }
    }
    ?>

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
                    <?php if (!empty($_SESSION['user'])): ?>
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
                    <?php if (!empty($_SESSION['user'])): ?>
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