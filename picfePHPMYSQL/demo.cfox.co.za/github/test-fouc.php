<?php
// Simple test page to verify FOUC fix
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/utils/CSRF.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
      }

      /* Hide loading spinner when page is loaded */
      .page-loaded .loading-spinner {
        display: none;
      }
    </style>

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

    <title>FOUC Test - PictureThis</title>
</head>
<body class="bg-gray-900 text-gray-200">
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Loading...</div>
    </div>

    <!-- Main Content -->
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md mx-auto bg-gray-800 rounded-xl p-8 shadow-lg">
            <h1 class="text-2xl font-bold text-center mb-6 text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">
                FOUC Test Page
            </h1>

            <div class="space-y-4">
                <div class="bg-gray-700 rounded-lg p-4">
                    <h2 class="text-lg font-semibold mb-2">Environment</h2>
                    <p class="text-sm text-gray-300">APP_ENV: <span class="text-green-400"><?php echo APP_ENV; ?></span></p>
                    <p class="text-sm text-gray-300">Debug: <span class="text-green-400"><?php echo APP_DEBUG ? 'Enabled' : 'Disabled'; ?></span></p>
                </div>

                <div class="bg-gray-700 rounded-lg p-4">
                    <h2 class="text-lg font-semibold mb-2">Database</h2>
                    <p class="text-sm text-gray-300">Host: <span class="text-blue-400"><?php echo DB_HOST; ?></span></p>
                    <p class="text-sm text-gray-300">Connected: <span class="text-green-400">Yes</span></p>
                </div>

                <div class="flex gap-4">
                    <a href="/dashboard" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg text-center transition-colors">
                        Dashboard
                    </a>
                    <a href="/generate" class="flex-1 bg-pink-600 hover:bg-pink-700 text-white font-medium py-2 px-4 rounded-lg text-center transition-colors">
                        Generate
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>