<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<header class="bg-gray-900 border-b border-gray-800">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
    <div class="flex items-center space-x-4">
      <a href="/dashboard" class="text-sm text-gray-400 hover:text-gray-200">&larr; Return to Dashboard</a>
      <div class="text-white font-semibold">Admin Portal</div>
    </div>
    <div class="text-sm text-gray-400">Logged in as Admin: <?php echo htmlspecialchars($_SESSION['user']['email'] ?? 'Unknown'); ?></div>
  </div>
</header>

<div class="max-w-7xl mx-auto px-6 py-10">
