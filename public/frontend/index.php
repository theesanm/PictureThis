<?php
// public/frontend/index.php - lightweight client replica of React homepage
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>PictureThis — Replica</title>
    <!-- Tailwind CDN for quick styling (suitable for prototype on cPanel) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/style.css">
  </head>
  <body class="bg-gray-900 text-gray-100">
    <header class="sticky top-0 z-50 bg-gradient-to-r from-gray-800 to-gray-900 border-b border-gray-700">
      <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <img src="/placeholder-image.jpg" alt="logo" class="w-10 h-10 rounded-md object-cover">
          <h1 class="text-xl font-semibold">PictureThis</h1>
        </div>
        <nav>
          <a href="#" class="px-3 py-2 hover:bg-gray-800 rounded">Gallery</a>
          <a href="#" class="px-3 py-2 hover:bg-gray-800 rounded">Generate</a>
          <a href="#" class="px-3 py-2 hover:bg-gray-800 rounded">Pricing</a>
        </nav>
      </div>
    </header>

    <main class="max-w-6xl mx-auto p-6">
      <section class="mb-6">
        <h2 class="text-2xl font-semibold mb-4">Gallery</h2>
        <div id="gallery" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
      </section>
    </main>

    <script src="/frontend/app.js"></script>
  </body>
</html>
