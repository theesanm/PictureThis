<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payment Success</title>
  <script>
    // Send message to parent window (opener)
    if (window.opener) {
      window.opener.postMessage({
        type: 'payment_success',
        payment_id: '<?php echo htmlspecialchars($_GET['payment_id'] ?? ''); ?>',
        user_id: '<?php echo htmlspecialchars($_GET['user_id'] ?? ''); ?>',
        package_id: '<?php echo htmlspecialchars($_GET['package_id'] ?? ''); ?>'
      }, '*');
    }

    // Auto-close after a short delay
    setTimeout(() => {
      window.close();
    }, 2000);
  </script>
</head>
<body style="background: #1f2937; color: white; font-family: Arial, sans-serif; text-align: center; padding: 50px;">
  <h1 style="color: #10b981;">Payment Successful!</h1>
  <p>Your credits have been added to your account.</p>
  <p>Closing payment window...</p>
</body>
</html>
