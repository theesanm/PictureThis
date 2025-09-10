<div class="max-w-6xl mx-auto py-12">
  <div class="text-center mb-12">
    <h1 class="text-4xl font-bold mb-4">Choose Your Package</h1>
    <p class="text-xl text-gray-400 mb-8">Buy credits to generate AI images and enhance prompts</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
    <?php foreach ($packages as $id => $pkg): ?>
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-purple-500 transition-colors">
        <div class="text-center mb-4">
          <div class="mx-auto text-purple-400 mb-2" style="font-size:32px">📦</div>
          <h3 class="text-lg font-semibold"><?=htmlspecialchars($pkg['name'])?></h3>
        </div>

        <div class="text-center mb-4">
          <div class="text-3xl font-bold text-purple-400 mb-1">R<?=number_format($pkg['price'],2)?></div>
          <div class="text-sm text-gray-400"><?=number_format($pkg['price'] / $pkg['credits'],2)?>c per credit</div>
        </div>

        <button data-package-id="<?=htmlspecialchars($id)?>" class="buy-now w-full bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-90 text-white font-medium py-3 px-4 rounded-lg transition-all">Buy Now</button>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-8 bg-blue-900/20 border border-blue-600 rounded-lg p-6">
    <div class="flex items-start gap-3">
      <div class="text-blue-400 mt-1">✔️</div>
      <div>
        <h3 class="font-semibold text-blue-400 mb-2">Secure Payments</h3>
        <p class="text-sm text-gray-300">All payments are processed securely through PayFast. Your payment information is never stored on our servers.</p>
      </div>
    </div>
  </div>

  <?php if (!empty($transactions)): ?>
  <div class="mt-8 bg-gray-800 rounded-xl p-6 border border-gray-700">
    <h2 class="text-lg font-semibold mb-4">Your recent transactions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
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
    </div>

    <table class="w-full text-left text-sm">
      <thead class="text-gray-400">
        <tr>
          <th>Type</th>
          <th>Amount</th>
          <th>Description</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transactions as $t): ?>
        <tr class="border-t border-gray-700">
          <td class="<?php echo ((int)$t['amount'] > 0) ? 'text-green-400':'text-red-400'; ?>"><?php echo htmlspecialchars($t['transaction_type'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars((int)$t['amount']); ?></td>
          <td><?php echo htmlspecialchars($t['description'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($t['created_at'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <script>
    document.querySelectorAll('.buy-now').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const packageId = btn.getAttribute('data-package-id');
        btn.disabled = true;
        try {
          const res = await fetch('/credits/initiate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ packageId })
          });
          if (!res.ok) {
            const err = await res.json();
            alert(err.message || 'Payment initiation failed');
            btn.disabled = false;
            return;
          }
          const json = await res.json();
          const paymentUrl = json.data.paymentUrl;
          const payfastData = json.data.payfastData;

          // build and submit form
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = paymentUrl;
          Object.entries(payfastData).forEach(([k,v]) => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = k;
            inp.value = v;
            form.appendChild(inp);
          });
          document.body.appendChild(form);
          form.submit();
        } catch (err) {
          console.error(err);
          alert('Payment initiation error');
          btn.disabled = false;
        }
      });
    });
  </script>
</div>
