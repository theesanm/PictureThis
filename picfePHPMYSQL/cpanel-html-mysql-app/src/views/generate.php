<?php
// Generate page — left: create form, right: generated image preview. Uses $user from controller for credits display.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$displayCredits = $user['credits'] ?? ($_SESSION['user']['credits'] ?? 0);
$creditCost = isset($settings['credit_cost_per_image']) ? (int)$settings['credit_cost_per_image'] : 10;
$enhanceCost = isset($settings['enhance_prompt_cost']) ? (int)$settings['enhance_prompt_cost'] : 1;
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1 bg-gray-800 rounded-xl p-6 border border-gray-700">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Create an Image</h3>
        <div class="text-sm text-yellow-300 font-bold"><?php echo htmlspecialchars($displayCredits); ?> credits</div>
      </div>

      <form action="/generate" method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block text-sm text-gray-300 mb-1">Text Prompt</label>
          <textarea name="prompt" rows="4" class="w-full bg-gray-700 border border-gray-600 rounded p-3 text-white" placeholder="Describe the image you want to generate..."></textarea>
        </div>

        <div>
          <button type="button" class="inline-flex items-center px-3 py-1 bg-purple-600 text-white rounded">
            Enhance Prompt
            <?php if (!empty($enhanceCost) && (int)$enhanceCost > 0): ?>
              <span class="ml-2 text-xs bg-gray-800 px-2 py-1 rounded"><?php echo htmlspecialchars($enhanceCost); ?> credit<?php echo $enhanceCost != 1 ? 's' : ''; ?></span>
            <?php endif; ?>
          </button>
        </div>

        <div>
          <label class="block text-sm text-gray-300 mb-2">Reference Images (Optional)</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="border-2 border-dashed border-gray-600 rounded p-6 flex items-center justify-center text-gray-400">Upload Image 1
              <input type="file" name="image1" accept="image/*" class="hidden" />
            </label>
            <label class="border-2 border-dashed border-gray-600 rounded p-6 flex items-center justify-center text-gray-400">Upload Image 2
              <input type="file" name="image2" accept="image/*" class="hidden" />
            </label>
          </div>
        </div>

        <div>
          <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white py-3 rounded">
            Generate Image
            <?php if (!empty($creditCost) && (int)$creditCost > 0): ?>
              <span class="ml-2 text-sm bg-gray-900 px-2 py-1 rounded"><?php echo htmlspecialchars($creditCost); ?> credits</span>
            <?php endif; ?>
          </button>
        </div>
      </form>
    </div>

    <div class="md:col-span-2 bg-gray-800 rounded-xl p-6 border border-gray-700">
      <h3 class="text-lg font-semibold mb-4">Generated Image</h3>
      <div class="bg-gray-900 rounded p-8 flex items-center justify-center min-h-[360px] text-gray-500">
        <div class="text-center">
          <div class="mb-4"><svg class="mx-auto" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="M21 15l-5-5L5 21"></path></svg></div>
          <div class="text-sm">No image generated yet</div>
          <div class="text-xs text-gray-400">Enter a prompt and click the Generate button to create your image</div>
        </div>
      </div>
    </div>
  </div>
</div>
