<h1 class="text-2xl font-semibold mb-6">Credit Management</h1>

<!-- Search and Filter Form -->
<div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
  <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <div>
      <label class="block text-sm text-gray-400 mb-1">Search</label>
      <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>"
             placeholder="User name, email, or description"
             class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white">
    </div>
    <div>
      <label class="block text-sm text-gray-400 mb-1">Transaction Type</label>
      <select name="type" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white">
        <option value="">All Types</option>
        <?php foreach ($transactionTypes ?? [] as $type): ?>
          <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($typeFilter ?? '') === $type ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($type); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm text-gray-400 mb-1">From Date</label>
      <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>"
             class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white">
    </div>
    <div>
      <label class="block text-sm text-gray-400 mb-1">To Date</label>
      <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>"
             class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white">
    </div>
    <div class="flex items-end">
      <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
        Search
      </button>
      <?php if (!empty($search) || !empty($typeFilter) || !empty($dateFrom) || !empty($dateTo)): ?>
        <a href="/admin/credits" class="ml-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded">
          Clear
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
    <div class="text-sm text-gray-400">Total Credits Consumed</div>
    <div class="text-2xl font-bold text-red-400"><?php echo number_format(abs($totalConsumed ?? 0)); ?></div>
  </div>
  <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
    <div class="text-sm text-gray-400">Total Credits Added</div>
    <div class="text-2xl font-bold text-green-400"><?php echo number_format($totalAdded ?? 0); ?></div>
  </div>
  <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
    <div class="text-sm text-gray-400">Images Generated</div>
    <div class="text-2xl font-bold text-blue-400"><?php echo number_format($imagesGenerated ?? 0); ?></div>
  </div>
  <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
    <div class="text-sm text-gray-400">Net Credits</div>
    <div class="text-2xl font-bold <?php echo (($totalAdded ?? 0) - abs($totalConsumed ?? 0)) >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
      <?php echo number_format(($totalAdded ?? 0) - abs($totalConsumed ?? 0)); ?>
    </div>
  </div>
</div>

<!-- Results Summary -->
<?php if (!empty($search) || !empty($typeFilter) || !empty($dateFrom) || !empty($dateTo)): ?>
<div class="bg-gray-800 rounded-xl p-4 border border-gray-700 mb-6">
  <div class="text-sm text-gray-400">
    Showing <?php echo count($transactions); ?> of <?php echo number_format($totalRecords ?? 0); ?> transactions
    <?php if (!empty($search)): ?><span class="text-blue-400">matching "<?php echo htmlspecialchars($search); ?>"</span><?php endif; ?>
    <?php if (!empty($typeFilter)): ?><span class="text-green-400">type: <?php echo htmlspecialchars($typeFilter); ?></span><?php endif; ?>
    <?php if (!empty($dateFrom) || !empty($dateTo)): ?>
      <span class="text-purple-400">
        from <?php echo htmlspecialchars($dateFrom ?: 'start'); ?> to <?php echo htmlspecialchars($dateTo ?: 'end'); ?>
      </span>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- Transactions Table -->
<div class="bg-gray-800 rounded-xl border border-gray-700">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead class="text-gray-400 bg-gray-900">
        <tr>
          <th class="px-4 py-3">User</th>
          <th class="px-4 py-3">Type</th>
          <th class="px-4 py-3">Amount</th>
          <th class="px-4 py-3">Description</th>
          <th class="px-4 py-3">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($transactions)): ?>
          <?php foreach ($transactions as $t): ?>
          <tr class="border-t border-gray-700 hover:bg-gray-700/50">
            <td class="px-4 py-3">
              <div class="text-sm font-semibold"><?php echo htmlspecialchars($t['full_name'] ?? 'Unknown'); ?></div>
              <div class="text-xs text-gray-400"><?php echo htmlspecialchars($t['email'] ?? ''); ?></div>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded text-xs font-medium
                <?php
                $type = strtolower($t['transaction_type'] ?? '');
                if ($type === 'admin_added' || $type === 'purchase' || $type === 'topup') {
                  echo 'bg-green-900 text-green-300';
                } elseif ($type === 'consumed' || $type === 'usage') {
                  echo 'bg-red-900 text-red-300';
                } else {
                  echo 'bg-gray-700 text-gray-300';
                }
                ?>">
                <?php echo htmlspecialchars($t['transaction_type'] ?? 'unknown'); ?>
              </span>
            </td>
            <td class="px-4 py-3 font-mono">
              <span class="<?php echo ((int)($t['amount'] ?? 0)) > 0 ? 'text-green-400' : 'text-red-400'; ?>">
                <?php echo ((int)($t['amount'] ?? 0)) > 0 ? '+' : ''; ?><?php echo htmlspecialchars((int)($t['amount'] ?? 0)); ?>
              </span>
            </td>
            <td class="px-4 py-3"><?php echo htmlspecialchars($t['description'] ?? ''); ?></td>
            <td class="px-4 py-3 text-gray-400">
              <?php echo htmlspecialchars(date('M j, Y H:i', strtotime($t['created_at'] ?? ''))); ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
              <?php if (!empty($search) || !empty($typeFilter) || !empty($dateFrom) || !empty($dateTo)): ?>
                No transactions found matching your search criteria.
              <?php else: ?>
                No transactions yet.
              <?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="mt-6 flex items-center justify-between">
  <div class="text-sm text-gray-400">
    Showing page <?php echo $page; ?> of <?php echo $totalPages; ?>
    (<?php echo number_format($totalRecords); ?> total transactions)
  </div>
  <div class="flex items-center space-x-2">
    <?php if ($page > 1): ?>
      <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
         class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded">
        Previous
      </a>
    <?php endif; ?>

    <?php
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);

    if ($startPage > 1): ?>
      <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"
         class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded">1</a>
      <?php if ($startPage > 2): ?><span class="px-2 text-gray-400">...</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
      <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
         class="px-3 py-2 <?php echo $i === $page ? 'bg-blue-600' : 'bg-gray-700 hover:bg-gray-600'; ?> text-white rounded">
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>

    <?php if ($endPage < $totalPages): ?>
      <?php if ($endPage < $totalPages - 1): ?><span class="px-2 text-gray-400">...</span><?php endif; ?>
      <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"
         class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded"><?php echo $totalPages; ?></a>
    <?php endif; ?>

    <?php if ($page < $totalPages): ?>
      <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
         class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded">
        Next
      </a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
