<h1 class="text-2xl font-semibold mb-6">System Settings</h1>
<div class="bg-gray-800 rounded-xl p-6 border border-gray-700 max-w-2xl">
  <form method="post" action="/admin/settings">
    <h3 class="text-lg font-semibold mb-2">Credit Settings</h3>
    <label class="block text-sm text-gray-400 mb-1">Credit Cost per Image</label>
    <input name="credit_cost" type="number" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white mb-4" value="<?php echo htmlspecialchars($settings['credit_cost_per_image'] ?? 10); ?>" />
    <label class="block text-sm text-gray-400 mb-1">Enhance Prompt Cost</label>
    <input name="enhance_cost" type="number" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white mb-4" value="<?php echo htmlspecialchars($settings['enhance_prompt_cost'] ?? 1); ?>" />

    <div class="flex items-center mb-4">
      <input type="checkbox" name="enable_enhance" id="enable_enhance" value="1" <?php echo (!empty($settings['enable_enhance']) ? 'checked' : ''); ?> class="mr-2" />
      <label for="enable_enhance" class="text-sm text-gray-300">Enable Prompt Enhancement</label>
    </div>

    <h3 class="text-lg font-semibold mb-2">AI Provider Settings</h3>
    <select name="ai_provider" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white mb-4">
      <option <?php echo (($settings['ai_provider'] ?? '') === 'openrouter') ? 'selected' : ''; ?> value="openrouter">OpenRouter</option>
    </select>

    <div class="mt-4">
      <button class="px-4 py-2 bg-blue-600 rounded text-white">Save Settings</button>
      <?php if (!empty($_SESSION['admin_flash'])): ?>
        <span class="ml-3 text-sm text-gray-300"><?php echo htmlspecialchars($_SESSION['admin_flash']); unset($_SESSION['admin_flash']); ?></span>
      <?php endif; ?>
    </div>
  </form>
</div>
