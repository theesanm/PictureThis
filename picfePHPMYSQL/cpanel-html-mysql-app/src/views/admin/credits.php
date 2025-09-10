<h1 class="text-2xl font-semibold mb-6">Credit Management</h1>
<div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-gray-900 p-4 rounded">
      <div class="text-sm text-gray-400">Total Credits Consumed</div>
  <div class="text-xl font-bold"><?php echo number_format($totalConsumed ?? 0); ?></div>
    </div>
    <div class="bg-gray-900 p-4 rounded">
      <div class="text-sm text-gray-400">Total Credits Added</div>
  <div class="text-xl font-bold"><?php echo number_format($totalAdded ?? 0); ?></div>
    </div>
    <div class="bg-gray-900 p-4 rounded">
      <div class="text-sm text-gray-400">Images Generated</div>
  <div class="text-xl font-bold"><?php echo number_format($imagesGenerated ?? 0); ?></div>
    </div>
    <div class="bg-gray-900 p-4 rounded">
      <div class="text-sm text-gray-400">Credit Cost per Image</div>
  <div class="text-xl font-bold"><?php echo number_format($creditCostPerImage ?? 10); ?></div>
    </div>
  </div>

  <table class="w-full text-left text-sm">
    <thead class="text-gray-400">
      <tr>
        <th>User</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Description</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($transactions)): foreach ($transactions as $t): ?>
      <tr class="border-t border-gray-700">
        <td>
          <div class="text-sm font-semibold"><?php echo htmlspecialchars($t['full_name'] ?? 'Unknown'); ?></div>
          <div class="text-xs text-gray-400"><?php echo htmlspecialchars($t['email'] ?? ''); ?></div>
        </td>
  <td class="<?php echo (strtolower($t['transaction_type'] ?? '') === 'admin_added' || (int)($t['amount'] ?? 0) > 0) ? 'text-green-400' : 'text-red-400'; ?>"><?php echo htmlspecialchars($t['transaction_type'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars((int)($t['amount'] ?? 0)); ?></td>
        <td><?php echo htmlspecialchars($t['description'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($t['created_at'] ?? ''); ?></td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="5" class="py-6 text-center text-gray-400">No transactions yet</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
