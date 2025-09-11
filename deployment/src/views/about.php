<?php
// About view - About Us page with the same theme as the app
?>

<main class="min-h-screen bg-gradient-to-b from-purple-900 via-purple-800 to-purple-700 text-white">
  <div class="max-w-6xl mx-auto py-20 px-6">
    <div class="text-center mb-16">
      <h1 class="text-5xl font-extrabold mb-6">About PictureThis</h1>
      <p class="text-xl text-purple-200 max-w-3xl mx-auto">
        We're on a mission to democratize AI-powered image generation, making creative tools accessible to everyone.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
      <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-8">
        <h2 class="text-3xl font-bold mb-6 text-white">Our Story</h2>
        <p class="text-gray-300 mb-4">
          PictureThis was born from the belief that everyone should have access to powerful AI tools for creative expression.
          We started with a simple idea: what if anyone could transform their thoughts into stunning visual art?
        </p>
        <p class="text-gray-300 mb-4">
          Our platform combines cutting-edge AI technology with an intuitive interface, allowing users to generate,
          enhance, and refine images from simple text prompts or existing images.
        </p>
        <p class="text-gray-300">
          Whether you're a professional designer, content creator, or just someone with a creative spark,
          PictureThis empowers you to bring your imagination to life.
        </p>
      </div>

      <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-8">
        <h2 class="text-3xl font-bold mb-6 text-white">Our Technology</h2>
        <div class="space-y-4">
          <div class="flex items-start space-x-3">
            <div class="w-2 h-2 bg-purple-400 rounded-full mt-2 flex-shrink-0"></div>
            <div>
              <h3 class="font-semibold text-white">Advanced AI Models</h3>
              <p class="text-gray-300 text-sm">Powered by state-of-the-art AI models for high-quality image generation</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <div class="w-2 h-2 bg-purple-400 rounded-full mt-2 flex-shrink-0"></div>
            <div>
              <h3 class="font-semibold text-white">Prompt Enhancement</h3>
              <p class="text-gray-300 text-sm">Intelligent prompt refinement to improve your results</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <div class="w-2 h-2 bg-purple-400 rounded-full mt-2 flex-shrink-0"></div>
            <div>
              <h3 class="font-semibold text-white">Image-to-Image</h3>
              <p class="text-gray-300 text-sm">Transform existing images with AI-powered enhancement</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <div class="w-2 h-2 bg-purple-400 rounded-full mt-2 flex-shrink-0"></div>
            <div>
              <h3 class="font-semibold text-white">Credit System</h3>
              <p class="text-gray-300 text-sm">Flexible pricing with transparent credit usage</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-8 mb-16">
      <h2 class="text-3xl font-bold mb-8 text-white text-center">Why Choose PictureThis?</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
          <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">🎨</span>
          </div>
          <h3 class="font-semibold text-white mb-2">Creative Freedom</h3>
          <p class="text-gray-300 text-sm">Express your creativity without technical barriers</p>
        </div>
        <div class="text-center">
          <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">⚡</span>
          </div>
          <h3 class="font-semibold text-white mb-2">Fast Generation</h3>
          <p class="text-gray-300 text-sm">Get results in seconds, not hours</p>
        </div>
        <div class="text-center">
          <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">🔒</span>
          </div>
          <h3 class="font-semibold text-white mb-2">Privacy First</h3>
          <p class="text-gray-300 text-sm">Your data and creations stay private</p>
        </div>
      </div>
    </div>

    <div class="text-center">
      <h2 class="text-3xl font-bold mb-6 text-white">Ready to Create?</h2>
      <p class="text-xl text-purple-200 mb-8">Join thousands of creators using PictureThis to bring their ideas to life.</p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="/register" class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-semibold py-3 px-8 rounded-lg shadow-lg transition-colors">
          Get Started Free
        </a>
        <a href="/generate" class="inline-block bg-transparent border-2 border-purple-400 hover:bg-purple-400 text-white font-semibold py-3 px-8 rounded-lg transition-colors">
          Try Generator
        </a>
      </div>
    </div>
  </div>
</main>
