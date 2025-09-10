<?php
// Home view - static PHP + Tailwind markup to match the provided design
?>

<main class="min-h-screen bg-gradient-to-b from-purple-900 via-purple-800 to-purple-700 text-white">
  <div class="max-w-6xl mx-auto py-20 px-6 text-center">
    <h2 class="text-4xl font-extrabold mb-4">Transform Your Ideas Into Stunning Images</h2>
    <p class="text-lg mb-8 text-purple-200">PictureThis turns your prompts into beautiful, shareable images. Create, refine and explore.</p>
  <a href="/register" class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-semibold py-3 px-6 rounded-lg shadow">Get Started</a>

    <div class="mt-12 flex justify-center">
        <div class="bg-white rounded-md shadow-lg p-4 w-96 h-96 flex items-center justify-center">
        <img src="/serve-generated-image.php" alt="Sample generated image" class="object-cover h-full w-full rounded-md" />
      </div>
    </div>
  </div>

  <section class="bg-gray-900 py-16">
    <div class="max-w-6xl mx-auto px-6 text-center text-gray-300">
      <h3 class="text-2xl font-bold mb-6 text-white">Powerful Features</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-lg p-6 text-left">
          <h4 class="font-semibold text-white mb-2">Text-to-Image Generation</h4>
          <p class="text-sm">Create images from textual prompts using advanced models.</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-6 text-left">
          <h4 class="font-semibold text-white mb-2">Prompt Enhancement</h4>
          <p class="text-sm">Refine prompts to get better, more consistent results.</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-6 text-left">
          <h4 class="font-semibold text-white mb-2">Image Gallery</h4>
          <p class="text-sm">Save and browse your generated images.</p>
        </div>
      </div>
    </div>
  </section>

  <footer class="bg-gray-900 py-12 mt-12 text-gray-400">
    <div class="max-w-6xl mx-auto px-6 text-center">
      <p>&copy; <?php echo date('Y'); ?> PictureThis</p>
    </div>
  </footer>
</main>
