<?php
// Minimal success page for PayFast return. Expects GET params: payment_id, user_id, package_id
$paymentId = htmlspecialchars($_GET['payment_id'] ?? '');
$userId = htmlspecialchars($_GET['user_id'] ?? '');
$packageId = htmlspecialchars($_GET['package_id'] ?? '');
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payment Success</title>
  <script>
    // Check if we're in an iframe (modal payment)
    const isInIframe = window.parent !== window;

    if (isInIframe) {
      // Send success message to parent and close
      window.parent.postMessage({
        type: 'payment_success',
        payment_id: '<?php echo $paymentId; ?>',
        user_id: '<?php echo $userId; ?>',
        package_id: '<?php echo $packageId; ?>'
      }, '*');

      // Auto-close after showing message
      setTimeout(() => {
        window.location.href = 'about:blank';
      }, 2000);
    }
  </script>
</head>
<body>
  <h1>Payment success</h1>
  <p>Thank you — your payment has been received and will be processed.</p>
  <ul>
    <li>Payment ID: <?php echo $paymentId; ?></li>
    <li>User ID: <?php echo $userId; ?></li>
    <li>Package: <?php echo $packageId; ?></li>
  </ul>
  <p id="status">Checking payment status...</p>
  <p id="credits"></p>
  <p id="devActions" style="margin-top:1rem;">
    <!-- Shown only on localhost to help developers simulate the PayFast ITN -->
    <button id="simulateItn" style="display:none;">Simulate PayFast ITN (dev)</button>
    <span id="simulateResult"></span>
  </p>
  <p style="margin-top:1rem;">
    <button onclick="window.location.href='/pricing'">Return to Pricing</button>
  </p>
  <p>If you don't see your credits shortly, contact support.</p>
  <script>
    (function(){
      const paymentId = encodeURIComponent('<?php echo $paymentId; ?>');
      const userId = encodeURIComponent('<?php echo $userId; ?>');
      const statusEl = document.getElementById('status');
      const creditsEl = document.getElementById('credits');
      let attempts = 0;
      function check() {
        attempts++;
        fetch('/api/payments/status?payment_id=' + paymentId + '&user_id=' + userId)
          .then(r => r.json())
          .then(j => {
            if (!j || !j.success) {
              statusEl.textContent = 'Unable to check payment status.';
              return;
            }
            statusEl.textContent = 'Payment status: ' + j.status;
            if (j.credits !== null) {
              creditsEl.textContent = 'Your current credits: ' + j.credits;
            }
            if (j.status === 'processed' || j.applied > 0) {
              // redirect to dashboard after brief pause
              statusEl.textContent = 'Payment processed — redirecting to pricing...';
              setTimeout(() => { window.location.href = '/pricing'; }, 1200);
              return;
            }
            if (attempts > 5 && j.status === 'pending') {
              statusEl.textContent = 'Payment status: pending — if you completed the payment on PayFast, it may still be processing. You can use the Simulate button to test or wait for automatic processing.';
            }
            if (attempts < 30) setTimeout(check, 2000);
            else statusEl.textContent = 'Still pending — redirecting to pricing...';
          }).catch(e => { statusEl.textContent = 'Error checking status'; });
      }
      check();

      // Redirect to pricing after 30 seconds if still on this page
      setTimeout(() => {
        if (document.getElementById('status').textContent.includes('pending')) {
          window.location.href = '/pricing';
        }
      }, 30000);

        // Show simulate button on localhost or ngrok for testing
        try {
          if (location.hostname === 'localhost' || location.hostname === '127.0.0.1' || location.hostname.includes('ngrok')) {
            const btn = document.getElementById('simulateItn');
            btn.style.display = 'inline-block';
            btn.addEventListener('click', () => {
              btn.disabled = true;
              document.getElementById('simulateResult').textContent = ' Sending...';
              fetch('/api/credits/payfast/test', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ packageId: '<?php echo $packageId; ?>', userId: <?php echo (int)$userId; ?> })
              }).then(r => r.json()).then(j => {
                document.getElementById('simulateResult').textContent = ' ' + (j && j.success ? 'OK' : (j && j.message ? j.message : 'Failed'));
                // Immediately check status once
                setTimeout(check, 400);
              }).catch(e => { document.getElementById('simulateResult').textContent = ' Error'; });
            });
          }
        } catch (e) {}
    })();
  </script>
</body>
</html>
