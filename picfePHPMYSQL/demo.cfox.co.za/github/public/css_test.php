<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { darkMode: 'class' };
      setTimeout(function() {
        document.body.classList.add('tailwind-loaded');
      }, 100);
    </script>
    <link rel="stylesheet" href="./css/style.css">
    <title>CSS Test</title>
</head>
<body class="bg-blue-500 text-white p-8">
    <h1 class="text-3xl font-bold mb-4">CSS Loading Test</h1>
    <p class="mb-4">If you see this styled page, both Tailwind and custom CSS are working!</p>
    <div class="bg-green-500 p-4 rounded-lg mb-4">
        <h2 class="text-xl">Success!</h2>
        <p>CSS is loading correctly from ./css/style.css</p>
    </div>
    <p class="text-sm opacity-75">Test completed at: <?php echo date('Y-m-d H:i:s'); ?></p>
</body>
</html>
