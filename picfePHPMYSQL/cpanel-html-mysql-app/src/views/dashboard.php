<?php
// Dashboard view — expects $user and $recentImages from controller
?>
<div class="max-w-6xl mx-auto px-4 py-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($user['full_name'] ?? ($_SESSION['user']['fullName'] ?? 'User')); ?>!</h1>
      <p class="text-gray-400 mt-1">Your AI image generation dashboard awaits.</p>
    </div>
    <div class="flex items-center space-x-4">
      <?php if (!empty($user['is_admin'])): ?>
        <a href="/admin" class="hidden md:inline-block bg-pink-600 text-white px-3 py-2 rounded">Admin Panel</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
      <h3 class="text-sm text-gray-300">Available Credits</h3>
      <div class="mt-4 flex items-center justify-between">
        <div>
          <div class="text-4xl font-extrabold text-yellow-300"><?php echo htmlspecialchars($user['credits'] ?? '0'); ?></div>
          <div class="text-sm text-gray-400">credits</div>
        </div>
        <div>
          <a href="/pricing" class="inline-block bg-gray-700 text-white px-3 py-2 rounded">Buy More</a>
        </div>
      </div>
    </div>

    <div class="md:col-span-2 bg-gray-800 rounded-xl p-6 border border-gray-700 flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold">Create New Image</h3>
        <p class="text-sm text-gray-400">Transform your ideas into stunning visual content with AI.</p>
      </div>
      <div>
        <a href="/generate" class="bg-pink-500 text-white px-4 py-2 rounded">+ Generate Image</a>
      </div>
    </div>
  </div>

  <div class="mb-6">
    <h3 class="text-lg font-semibold mb-3">Recent Creations</h3>
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 min-h-[160px] flex items-center justify-center">
      <?php if (empty($recentImages)): ?>
        <div class="text-center text-gray-400">
          <div class="mb-4">No images yet</div>
          <a href="/generate" class="inline-block bg-pink-500 text-white px-4 py-2 rounded">Generate Your First Image</a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full">
          <?php foreach ($recentImages as $img): ?>
            <div class="bg-gray-900 rounded overflow-hidden border border-gray-700">
              <img src="<?php echo htmlspecialchars($img['image_url'] ?? '/placeholder-image.jpg'); ?>?t=<?php echo $img['id'] ?? time(); ?>" alt="<?php echo htmlspecialchars($img['prompt'] ?? 'Generated image'); ?>" class="w-full h-36 object-cover">
              <div class="p-2 text-sm text-gray-300"><?php echo htmlspecialchars(substr($img['prompt'] ?? 'No description', 0, 60)); ?><?php echo (isset($img['prompt']) && strlen($img['prompt']) > 60) ? '...' : ''; ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="bg-gradient-to-r from-purple-900 to-pink-900 p-6 rounded-xl text-white">
    <h4 class="font-semibold mb-2">Pro Tips for Better Results</h4>
    <ul class="list-disc list-inside text-sm text-purple-100/90">
      <li>Be specific about styles: "oil painting", "watercolor", "3D render"</li>
      <li>Mention lighting: "dramatic lighting", "golden hour", "soft diffused light"</li>
      <li>Include composition details: "ultra-wide angle", "aerial view", "close-up"</li>
      <li>Use our prompt enhancement feature for better results</li>
    </ul>
  </div>
</div>
