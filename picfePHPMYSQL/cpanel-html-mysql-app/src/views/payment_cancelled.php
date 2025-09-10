<?php
// Minimal cancelled page for PayFast return.
$paymentId = htmlspecialchars($_GET['payment_id'] ?? '');
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payment Cancelled</title>
  <script>
    // Check if we're in an iframe (modal payment)
    const isInIframe = window.parent !== window;

    if (isInIframe) {
      // Send cancel message to parent and close
      window.parent.postMessage({
        type: 'payment_cancelled',
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
  <h1>Payment Cancelled</h1>
  <p>Your payment was cancelled or not completed.</p>
  <p>Payment ID: <?php echo $paymentId; ?></p>
  <p>You will be redirected to the pricing page in <span id="countdown">5</span> seconds.</p>
  <p><button onclick="window.location.href='/pricing'">Return to Pricing Now</button></p>

  <script>
    let countdown = 5;
    const countdownEl = document.getElementById('countdown');
    const timer = setInterval(() => {
      countdown--;
      countdownEl.textContent = countdown;
      if (countdown <= 0) {
        clearInterval(timer);
        window.location.href = '/pricing';
      }
    }, 1000);
  </script>
</body>
</html>
