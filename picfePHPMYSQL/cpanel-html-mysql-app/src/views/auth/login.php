<?php
// Minimal login form ported from picfe login UI
?>
<div class="w-full max-w-md mx-auto">
  <div class="bg-gray-800 shadow-xl rounded-lg px-8 pt-6 pb-8 mb-4">
    <h2 class="text-3xl font-bold mb-6 text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">Welcome Back</h2>

    <?php if (!empty($_SESSION['auth_error'])): ?>
      <div class="mb-4 p-3 bg-red-900/40 border border-red-500 text-red-200 rounded-md text-sm">
        <?php echo htmlspecialchars($_SESSION['auth_error']); unset($_SESSION['auth_error']); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['auth_success'])): ?>
      <div class="mb-4 p-3 bg-green-900/40 border border-green-500 text-green-200 rounded-md text-sm">
        <?php echo htmlspecialchars($_SESSION['auth_success']); unset($_SESSION['auth_success']); ?>
      </div>
    <?php endif; ?>

  <form action="/login" method="POST" class="space-y-6">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
        <div class="relative">
          <input id="email" name="email" type="email" class="block w-full pl-3 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-md text-white" placeholder="you@example.com" required />
        </div>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
        <div class="relative">
          <input id="password" name="password" type="password" class="block w-full pl-3 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-md text-white" placeholder="••••••••" required />
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input id="remember-me" name="remember" type="checkbox" class="h-4 w-4 rounded bg-gray-700 border-gray-500" />
          <label for="remember-me" class="ml-2 block text-sm text-gray-300">Remember me</label>
        </div>
        <div class="text-sm">
          <a href="/forgot-password" class="font-medium text-purple-400">Forgot your password?</a>
        </div>
      </div>

      <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500">Sign in</button>
    </form>

    <!-- Resend Verification Email Section -->
    <div class="mt-6">
      <div class="relative">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-2 bg-gray-800 text-gray-400">Didn't receive verification email?</span>
        </div>
      </div>

      <form action="/resend-verification" method="POST" class="mt-4">
        <div class="flex space-x-2">
          <input
            type="email"
            name="email"
            placeholder="Enter your email"
            class="flex-1 pl-3 pr-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white text-sm"
            required
          />
          <button
            type="submit"
            class="px-4 py-2 bg-purple-600 text-white text-sm rounded-md hover:bg-purple-700"
          >
            Resend
          </button>
        </div>
      </form>
    </div>

    <div class="mt-6">
      <div class="relative">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-2 bg-gray-800 text-gray-400">Don't have an account?</span>
        </div>
      </div>

      <div class="mt-6">
        <a href="/register" class="w-full flex justify-center py-2.5 px-4 border border-gray-600 rounded-md text-sm font-medium text-white bg-gray-700">Create a new account</a>
      </div>
    </div>
  </div>
</div>
