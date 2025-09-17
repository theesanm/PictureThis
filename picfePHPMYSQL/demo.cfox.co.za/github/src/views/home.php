<?php
// Enhanced Home view - Professional and inviting PHP homepage
// Get some dynamic data for the homepage
$totalUsers = 0;
$totalImages = 0;
try {
    require_once __DIR__ . '/../lib/db.php';
    $pdo = get_db();
    if ($pdo) {
        $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
        $imageCount = $pdo->query("SELECT COUNT(*) as count FROM images WHERE has_usage_permission IS NULL OR has_usage_permission != -1")->fetch(PDO::FETCH_ASSOC);
        $totalUsers = $userCount['count'] ?? 0;
        $totalImages = $imageCount['count'] ?? 0;
    }
} catch (Exception $e) {
    // Fallback values if database connection fails
    $totalUsers = 0;
    $totalImages = 0;
}
?>

<main class="min-h-screen" style="min-height: 100vh; background: #1a202c; color: #e2e8f0; padding: 20px;">
  <!-- Hero Section -->
  <section class="relative overflow-hidden bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 1rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32" style="max-width: 1200px; margin: 0 auto;">
      <div class="text-center">
        <!-- Badge -->
        <div class="inline-flex items-center px-4 py-2 rounded-full bg-purple-500/20 border border-purple-400/30 text-purple-200 text-sm font-medium mb-8" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background: rgba(168, 85, 247, 0.2); border: 1px solid rgba(168, 85, 247, 0.3); color: #fbb6ce; border-radius: 9999px; margin-bottom: 2rem;">
          <span class="w-2 h-2 bg-purple-400 rounded-full mr-2 animate-pulse" style="width: 8px; height: 8px; background: #a855f7; border-radius: 50%; margin-right: 0.5rem;"></span>
          AI-Powered Image Generation
        </div>

        <!-- Main Headline -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 1.5rem; line-height: 1.2;">
          Transform Your Ideas Into
          <span class="bg-gradient-to-r from-pink-400 via-purple-400 to-indigo-400 bg-clip-text text-transparent" style="background: linear-gradient(to right, #f472b6, #a855f7, #60a5fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
            Stunning Images
          </span>
        </h1>

        <!-- Subheadline -->
        <p class="text-lg sm:text-xl text-purple-100 mb-8 max-w-3xl mx-auto leading-relaxed" style="font-size: 1.125rem; color: #e9d5ff; margin-bottom: 2rem; max-width: 48rem; margin-left: auto; margin-right: auto; line-height: 1.6;">
          PictureThis harnesses the power of advanced AI to turn your creative prompts into beautiful,
          high-quality images. From concept to creation in seconds.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12" style="display: flex; flex-direction: column; gap: 1rem; justify-content: center; margin-bottom: 3rem;">
          <a href="/register" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200" style="display: inline-flex; align-items: center; padding: 1rem 2rem; background: linear-gradient(to right, #ec4899, #9333ea); color: white; font-weight: 600; border-radius: 0.75rem; text-decoration: none; margin: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); transition: all 0.2s;">
            <span>Start Creating</span>
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem; margin-left: 0.5rem;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
          </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 max-w-2xl mx-auto" style="display: flex; justify-content: space-around; max-width: 42rem; margin: 0 auto 2rem auto;">
          <div class="text-center" style="text-align: center;">
            <div class="text-3xl font-bold text-white mb-1" style="font-size: 1.875rem; font-weight: 700; color: white; margin-bottom: 0.25rem;"><?php echo number_format($totalImages); ?>+</div>
            <div class="text-purple-200 text-sm" style="color: #e9d5ff; font-size: 0.875rem;">Images Generated</div>
          </div>
          <div class="text-center" style="text-align: center;">
            <div class="text-3xl font-bold text-white mb-1" style="font-size: 1.875rem; font-weight: 700; color: white; margin-bottom: 0.25rem;"><?php echo number_format($totalUsers); ?>+</div>
            <div class="text-purple-200 text-sm" style="color: #e9d5ff; font-size: 0.875rem;">Creative Users</div>
          </div>
          <div class="text-center" style="text-align: center;">
            <div class="text-3xl font-bold text-white mb-1" style="font-size: 1.875rem; font-weight: 700; color: white; margin-bottom: 0.25rem;">∞</div>
            <div class="text-purple-200 text-sm" style="color: #e9d5ff; font-size: 0.875rem;">Possibilities</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Elements -->
    <div class="absolute top-20 left-10 w-20 h-20 bg-purple-500/20 rounded-full blur-xl animate-pulse"></div>
    <div class="absolute bottom-20 right-10 w-32 h-32 bg-pink-500/20 rounded-full blur-xl animate-pulse delay-1000"></div>
  </section>

  <!-- Features Section -->
  <section class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
          Powerful Features for Creative Minds
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Everything you need to bring your creative vision to life with cutting-edge AI technology
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-purple-200 group">
          <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-9 0V1m10 3V1m0 3l1 1v16a2 2 0 01-2 2H6a2 2 0 01-2-2V5l1-1z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Text-to-Image Generation</h3>
          <p class="text-gray-600 leading-relaxed">
            Transform your ideas into stunning visuals with our advanced AI models. Simply describe what you want to see.
          </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-purple-200 group">
          <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Smart Prompt Enhancement</h3>
          <p class="text-gray-600 leading-relaxed">
            Our AI analyzes and enhances your prompts to generate even better results with professional quality.
          </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-purple-200 group">
          <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Image-to-Image</h3>
          <p class="text-gray-600 leading-relaxed">
            Upload existing images and let AI transform them according to your creative vision and specifications.
          </p>
        </div>

        <!-- Feature 4 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-purple-200 group">
          <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Personal Gallery</h3>
          <p class="text-gray-600 leading-relaxed">
            Save and organize all your generated images in a beautiful, searchable gallery for easy access and sharing.
          </p>
        </div>

        <!-- Feature 5 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-purple-200 group">
          <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">High-Quality Output</h3>
          <p class="text-gray-600 leading-relaxed">
            Generate images up to 2K resolution with professional quality suitable for commercial use and printing.
          </p>
        </div>

        <!-- Feature 6 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-purple-200 group">
          <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Flexible Pricing</h3>
          <p class="text-gray-600 leading-relaxed">
            Choose from various credit packages that fit your creative needs. Pay only for what you use.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works Section -->
  <section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
          How It Works
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Creating stunning AI images is as simple as 1-2-3
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Step 1 -->
        <div class="text-center">
          <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-2xl font-bold">
            1
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Describe Your Vision</h3>
          <p class="text-gray-600">
            Write a detailed prompt describing the image you want to create. Be as specific as possible for better results.
          </p>
        </div>

        <!-- Step 2 -->
        <div class="text-center">
          <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-2xl font-bold">
            2
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">AI Generation</h3>
          <p class="text-gray-600">
            Our advanced AI models process your prompt and generate a unique, high-quality image in seconds.
          </p>
        </div>

        <!-- Step 3 -->
        <div class="text-center">
          <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-2xl font-bold">
            3
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Download & Share</h3>
          <p class="text-gray-600">
            Save your creation to your gallery and share it with the world. Use it for any personal or commercial project.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="py-24 bg-gradient-to-r from-purple-600 to-pink-600">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6">
        Ready to Unleash Your Creativity?
      </h2>
      <p class="text-lg text-purple-100 mb-8 max-w-2xl mx-auto">
        Join thousands of creators who are already using PictureThis to bring their ideas to life.
        Start creating stunning AI-generated images today.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="/register" class="inline-flex items-center px-8 py-4 bg-white text-purple-600 font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
          <span>Get Started</span>
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
          </svg>
        </a>
        <a href="/login" class="inline-flex items-center px-8 py-4 bg-purple-500/20 hover:bg-purple-500/30 text-white font-semibold rounded-xl border border-white/20 backdrop-blur-sm transition-all duration-200">
          <span>Sign In</span>
        </a>
      </div>
    </div>
  </section>
</main>
