<?php
// Editable profile view. Expects $user from controller.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<div class="max-w-4xl mx-auto px-4 py-8">
  <h2 class="text-2xl font-semibold mb-6">Your Profile</h2>

  <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
    <?php if (!empty($_SESSION['profile_success'])): ?>
      <div class="mb-4 p-3 bg-green-900/30 text-green-200 rounded"><?php echo htmlspecialchars($_SESSION['profile_success']); unset($_SESSION['profile_success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['profile_error'])): ?>
      <div class="mb-4 p-3 bg-red-900/30 text-red-200 rounded"><?php echo htmlspecialchars($_SESSION['profile_error']); unset($_SESSION['profile_error']); ?></div>
    <?php endif; ?>

    <form action="/profile" method="POST" class="space-y-6">
      <div>
        <label class="block text-sm text-gray-300 mb-1">Name</label>
        <input type="text" name="fullName" value="<?php echo htmlspecialchars($user['full_name'] ?? $_SESSION['user']['fullName'] ?? ''); ?>" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white" />
      </div>

      <div>
        <label class="block text-sm text-gray-300 mb-1">Email Address</label>
        <input type="text" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-gray-400" />
      </div>

      <div class="grid grid-cols-3 gap-4 mt-4">
        <div class="col-span-1 bg-gray-900 p-4 rounded">
          <div class="text-sm text-gray-400">Credits</div>
          <div class="text-2xl font-bold text-yellow-300"><?php echo htmlspecialchars($user['credits'] ?? 0); ?></div>
        </div>
        <div class="col-span-1 bg-gray-900 p-4 rounded">
          <div class="text-sm text-gray-400">Account Created</div>
          <div class="text-sm"><?php echo htmlspecialchars(isset($user['created_at']) ? date('n/j/Y', strtotime($user['created_at'])) : ''); ?></div>
        </div>
        <div class="col-span-1 bg-gray-900 p-4 rounded">
          <div class="text-sm text-gray-400">Account Type</div>
          <div class="text-sm">User</div>
        </div>
      </div>

      <!-- Transaction History -->
      <div class="mt-8">
        <h3 class="text-xl font-semibold mb-4">Transaction History</h3>
        <div class="bg-gray-900 rounded-lg overflow-hidden">
          <?php if (!empty($transactions)): ?>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-800">
                  <tr>
                    <th class="px-4 py-3 text-left text-gray-300">Date</th>
                    <th class="px-4 py-3 text-left text-gray-300">Description</th>
                    <th class="px-4 py-3 text-left text-gray-300">Type</th>
                    <th class="px-4 py-3 text-right text-gray-300">Amount</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                  <?php foreach ($transactions as $transaction): ?>
                    <tr class="hover:bg-gray-800">
                      <td class="px-4 py-3 text-gray-300">
                        <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($transaction['created_at']))); ?>
                      </td>
                      <td class="px-4 py-3 text-gray-300">
                        <?php echo htmlspecialchars($transaction['description']); ?>
                      </td>
                      <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded <?php 
                          echo $transaction['transaction_type'] === 'purchase' ? 'bg-green-900 text-green-200' : 
                               ($transaction['transaction_type'] === 'usage' ? 'bg-red-900 text-red-200' : 
                               'bg-blue-900 text-blue-200'); 
                        ?>">
                          <?php echo htmlspecialchars(ucfirst($transaction['transaction_type'])); ?>
                        </span>
                      </td>
                      <td class="px-4 py-3 text-right font-medium <?php 
                        echo $transaction['amount'] > 0 ? 'text-green-400' : 'text-red-400'; 
                      ?>">
                        <?php echo htmlspecialchars(($transaction['amount'] > 0 ? '+' : '') . $transaction['amount']); ?> credits
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="px-4 py-8 text-center text-gray-400">
              No transactions found.
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="bg-pink-500 text-white px-4 py-2 rounded">Save Changes</button>
      </div>
    </form>
  </div>
</div>
