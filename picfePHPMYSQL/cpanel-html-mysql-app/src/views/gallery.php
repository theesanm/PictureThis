<?php
// Gallery page - displays user's generated images
// Variables are extracted from controller: $images, $user
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold">Your Gallery</h2>
        <?php
        $retentionDays = getenv('IMAGE_RETENTION_DAYS') ?: 7;
        ?>
        <p class="text-sm text-gray-400 mt-1">
          Images are automatically cleaned up after <?php echo htmlspecialchars($retentionDays); ?> days to manage storage space
        </p>
      </div>
      <div class="flex items-center gap-2">
        <div class="text-yellow-300">*</div>
        <div class="text-sm">
          <span class="font-bold"><?php echo htmlspecialchars($user['credits'] ?? 0); ?></span>
          <span class="text-gray-400">credits</span>
        </div>
      </div>
    </div>

    <?php if (empty($images) || count($images) === 0): ?>
      <div class="text-center py-12">
        <div class="text-gray-400 mb-4">
          <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-300 mb-2">No images yet</h3>
        <p class="text-gray-500 mb-6">Generate your first image to see it here!</p>
        <a href="/generate" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Generate Image
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($images as $image): ?>
          <div class="bg-gray-700 rounded-lg overflow-hidden border border-gray-600 hover:border-purple-500 transition-colors">
            <!-- Image Thumbnail -->
            <div class="aspect-square relative group">
              <img
                src="<?php echo htmlspecialchars($image['image_url']); ?>"
                alt="<?php echo htmlspecialchars(substr($image['prompt'], 0, 50) . (strlen($image['prompt']) > 50 ? '...' : '')); ?>"
                class="w-full h-full object-cover cursor-pointer"
                loading="lazy"
                onclick="openImageModal('<?php echo addslashes($image['image_url']); ?>', '<?php echo addslashes($image['prompt']); ?>')"
              />

              <!-- Overlay with actions -->
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none">
                <div class="flex gap-2 pointer-events-auto">
                  <!-- Download Button -->
                  <a
                    href="<?php echo htmlspecialchars($image['image_url']); ?>"
                    download="generated_<?php echo $image['id']; ?>.png"
                    class="p-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full transition-colors"
                    title="Download Image"
                  >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </a>

                  <!-- View Full Size Button -->
                  <button
                    onclick="openImageModal('<?php echo addslashes($image['image_url']); ?>', '<?php echo addslashes($image['prompt']); ?>')"
                    class="p-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full transition-colors"
                    title="View Full Size"
                  >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Image Info -->
            <div class="p-4">
              <p class="text-sm text-gray-300 mb-2 line-clamp-2" title="<?php echo htmlspecialchars($image['prompt']); ?>">
                <?php echo htmlspecialchars(substr($image['prompt'], 0, 80) . (strlen($image['prompt']) > 80 ? '...' : '')); ?>
              </p>
              <div class="flex justify-between items-center text-xs text-gray-500">
                <span><?php echo date('M j, Y', strtotime($image['created_at'])); ?></span>
                <span><?php echo htmlspecialchars($image['generation_cost']); ?> credits</span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Image Modal -->
<div id="image-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
  <div class="max-w-4xl max-h-screen p-4">
    <div class="bg-gray-800 rounded-xl overflow-hidden">
      <!-- Modal Header -->
      <div class="flex justify-between items-center p-4 border-b border-gray-700">
        <h3 class="text-lg font-semibold text-white">Generated Image</h3>
        <button id="close-modal" class="text-gray-400 hover:text-white text-2xl">&times;</button>
      </div>

      <!-- Modal Content -->
      <div class="p-4">
        <img id="modal-image" src="" alt="" class="w-full h-auto max-h-96 object-contain mx-auto" />
        <div id="modal-prompt" class="mt-4 text-gray-300 text-sm"></div>
      </div>

      <!-- Modal Footer -->
      <div class="flex justify-end gap-3 p-4 border-t border-gray-700">
        <a id="modal-download" href="" download="" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
          Download
        </a>
        <button id="modal-close" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function openImageModal(imageUrl, prompt) {
  document.getElementById('modal-image').src = imageUrl;
  document.getElementById('modal-prompt').textContent = prompt;
  document.getElementById('modal-download').href = imageUrl;
  document.getElementById('modal-download').download = 'generated_' + Date.now() + '.png';
  document.getElementById('image-modal').classList.remove('hidden');
}

document.getElementById('close-modal').addEventListener('click', () => {
  document.getElementById('image-modal').classList.add('hidden');
});

document.getElementById('modal-close').addEventListener('click', () => {
  document.getElementById('image-modal').classList.add('hidden');
});

// Close modal when clicking outside
document.getElementById('image-modal').addEventListener('click', (e) => {
  if (e.target.id === 'image-modal') {
    document.getElementById('image-modal').classList.add('hidden');
  }
});
</script>
