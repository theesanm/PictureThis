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

        <button data-package-id="<?=htmlspecialchars($id)?>" class="buy-now w-full bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-90 text-white font-medium py-3 px-4 rounded-lg transition-all" <?php if (empty($_SESSION['user'])): ?>onclick="window.location.href='/login'"<?php endif; ?>>Buy Now</button>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-8 bg-blue-900/20 border border-blue-600 rounded-lg p-6">
    <div class="flex items-start gap-3">
      <div class="text-blue-400 mt-1">✔️</div>
      <div>
        <h3 class="font-semibold text-blue-400 mb-2">Secure Payments</h3>
        <p class="text-sm text-gray-300">All payments are processed securely through PayFast. Your payment information is never stored on our servers.</p>
        <?php if (empty($_SESSION['user'])): ?>
        <p class="text-sm text-yellow-400 mt-2">⚠️ <a href="/login" class="underline hover:text-yellow-300">Please log in</a> to purchase credits.</p>
        <?php endif; ?>
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
        // Check if user is logged in
        <?php if (empty($_SESSION['user'])): ?>
        // User is not logged in, redirect to login
        window.location.href = '/login';
        return;
        <?php endif; ?>

        const packageId = btn.getAttribute('data-package-id');
        btn.disabled = true;
        btn.textContent = 'Processing...';

        try {
          const res = await fetch('/api/credits/initiate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ packageId })
          });
          if (!res.ok) {
            const err = await res.json();
            alert(err.message || 'Payment initiation failed');
            btn.disabled = false;
            btn.textContent = 'Buy Now';
            return;
          }
          const json = await res.json();
          const paymentUrl = json.data.paymentUrl;
          const payfastData = json.data.payfastData;

          // Show payment modal with popup
          showPaymentModal(paymentUrl, payfastData);

          btn.disabled = false;
          btn.textContent = 'Buy Now';
        } catch (err) {
          console.error(err);
          alert('Payment initiation error');
          btn.disabled = false;
          btn.textContent = 'Buy Now';
        }
      });
    });

    function showPaymentModal(paymentUrl, payfastData) {
      // Create modal HTML
      const modal = document.createElement('div');
      modal.id = 'payment-modal';
      modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
      modal.innerHTML = `
        <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-white">Complete Your Payment</h2>
            <button id="close-modal" class="text-gray-400 hover:text-white text-2xl">&times;</button>
          </div>
          <div class="text-center">
            <div class="text-6xl mb-4">🔒</div>
            <h3 class="text-lg font-semibold text-white mb-4">Secure Payment Processing</h3>
            <p class="text-gray-300 mb-6">A secure payment window will open. Please complete your payment there.</p>
            <div class="flex gap-4 justify-center">
              <button id="open-payment-popup" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Open Payment Window
              </button>
              <button id="close-modal-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                Cancel
              </button>
            </div>
            <p class="text-sm text-gray-400 mt-4">This modal will close automatically when payment is complete.</p>
          </div>
        </div>
      `;

      document.body.appendChild(modal);

      // Create hidden form
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = paymentUrl;
      form.target = 'payfast-payment';
      form.style.display = 'none';

      Object.entries(payfastData).forEach(([k, v]) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = k;
        inp.value = v;
        form.appendChild(inp);
      });

      document.body.appendChild(form);

      // Store popup reference
      let paymentPopup = null;

      // Function to open payment popup
      const openPaymentPopup = () => {
        paymentPopup = window.open(
          'about:blank',
          'payfast-payment',
          'width=800,height=600,scrollbars=yes,resizable=yes,status=yes,location=yes,toolbar=no,menubar=no'
        );

        if (paymentPopup) {
          form.submit();
          paymentPopup.focus();

          // Monitor popup for closure
          const checkClosed = setInterval(() => {
            if (paymentPopup.closed) {
              clearInterval(checkClosed);
              // Check payment status when popup closes
              checkPaymentStatus();
            }
          }, 1000);

          // Start polling for payment status
          setTimeout(() => {
            const pollStatus = () => {
              fetch('/api/payments/status?payment_id=' + encodeURIComponent(form.querySelector('input[name="m_payment_id"]').value))
                .then(response => response.json())
                .then(data => {
                  if (data.success && (data.status === 'processed' || data.applied > 0)) {
                    modal.remove();
                    showNotification('Payment successful! Credits have been added to your account.', 'success');
                    setTimeout(() => {
                      window.location.reload();
                    }, 2000);
                  } else if (data.status === 'cancelled') {
                    modal.remove();
                    showNotification('Payment was cancelled.', 'info');
                  } else {
                    setTimeout(pollStatus, 2000);
                  }
                })
                .catch(error => {
                  console.error('Error checking payment status:', error);
                  setTimeout(pollStatus, 2000);
                });
            };
            pollStatus();
          }, 5000);
        } else {
          // Popup blocked
          modal.querySelector('.text-center').innerHTML = `
            <div class="text-6xl mb-4">🚫</div>
            <h3 class="text-lg font-semibold text-white mb-4">Popup Blocked</h3>
            <p class="text-gray-300 mb-6">Please allow popups for this site and try again.</p>
            <div class="flex gap-4 justify-center">
              <button onclick="form.target='_blank'; form.submit();" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Open in New Tab
              </button>
              <button id="close-modal-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                Close
              </button>
            </div>
          `;
        }
      };

      // Function to check payment status
      const checkPaymentStatus = () => {
        const paymentId = form.querySelector('input[name="m_payment_id"]').value;
        const checkStatus = () => {
          fetch('/api/payments/status?payment_id=' + encodeURIComponent(paymentId))
            .then(response => response.json())
            .then(data => {
              if (data.success && (data.status === 'processed' || data.applied > 0)) {
                modal.remove();
                showNotification('Payment successful! Credits have been added to your account.', 'success');
                setTimeout(() => {
                  window.location.reload();
                }, 2000);
              } else if (data.status === 'cancelled') {
                modal.remove();
                showNotification('Payment was cancelled.', 'info');
              } else {
                setTimeout(checkStatus, 2000);
              }
            })
            .catch(error => {
              console.error('Error checking payment status:', error);
              setTimeout(checkStatus, 2000);
            });
        };
        setTimeout(checkStatus, 1000);
      };

      // Event listeners
      document.getElementById('close-modal').addEventListener('click', () => {
        if (paymentPopup && !paymentPopup.closed) {
          paymentPopup.close();
        }
        modal.remove();
        if (form && form.parentNode) {
          form.parentNode.removeChild(form);
        }
      });

      document.getElementById('close-modal-btn').addEventListener('click', () => {
        if (paymentPopup && !paymentPopup.closed) {
          paymentPopup.close();
        }
        modal.remove();
        if (form && form.parentNode) {
          form.parentNode.removeChild(form);
        }
      });

      document.getElementById('open-payment-popup').addEventListener('click', openPaymentPopup);

      // Auto-open after short delay
      setTimeout(openPaymentPopup, 500);

      // Listen for messages from popup
      window.addEventListener('message', handlePaymentMessage);
    }



    function handlePaymentMessage(event) {
      // For security, you might want to check event.origin
      if (event.data && typeof event.data === 'object') {
        if (event.data.type === 'payment_success') {
          // Payment successful - close modal and refresh page
          const modal = document.getElementById('payment-modal');
          if (modal) modal.remove();

          // Show success message and refresh
          showNotification('Payment successful! Credits have been added to your account.', 'success');
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        } else if (event.data.type === 'payment_cancelled') {
          // Payment cancelled - close modal
          const modal = document.getElementById('payment-modal');
          if (modal) modal.remove();

          showNotification('Payment was cancelled.', 'info');
        }
      }
    }

    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
        type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'
      }`;
      notification.textContent = message;
      document.body.appendChild(notification);

      setTimeout(() => {
        notification.remove();
      }, 5000);
    }
  </script>
</div>
