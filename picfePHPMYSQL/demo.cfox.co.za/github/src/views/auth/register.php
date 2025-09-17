<?php
// Minimal register form ported from picfe register UI
require_once __DIR__ . '/../../utils/CSRF.php';
?>
<div class="w-full max-w-md mx-auto">
  <div class="bg-gray-800 shadow-xl rounded-lg px-8 pt-6 pb-8 mb-4">
    <h2 class="text-3xl font-bold mb-6 text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">Create Account</h2>

    <?php if (!empty($_SESSION['auth_error'])): ?>
      <div class="mb-4 p-3 bg-red-900/40 border border-red-500 text-red-200 rounded-md text-sm">
        <?php echo htmlspecialchars($_SESSION['auth_error']); unset($_SESSION['auth_error']); ?>
      </div>
    <?php endif; ?>

  <form action="/register" method="POST" class="space-y-6">
      <?php echo CSRF::getTokenField(); ?>
      <div>
        <label for="fullName" class="block text-sm font-medium text-gray-300 mb-1">Full Name</label>
        <input id="fullName" name="fullName" type="text" class="block w-full pl-3 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-md text-white" placeholder="John Doe" required />
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
        <input id="email" name="email" type="email" class="block w-full pl-3 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-md text-white" placeholder="you@example.com" required />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
        <input id="password" name="password" type="password" class="block w-full pl-3 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-md text-white" placeholder="••••••••" required minlength="8" />
      </div>

      <div>
        <label for="confirmPassword" class="block text-sm font-medium text-gray-300 mb-1">Confirm Password</label>
        <input id="confirmPassword" name="confirmPassword" type="password" class="block w-full pl-3 pr-3 py-2.5 bg-gray-700 border border-gray-600 rounded-md text-white" placeholder="••••••••" required minlength="8" />
      </div>

      <div class="flex items-center">
        <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 rounded bg-gray-700 border-gray-500" />
        <label for="terms" class="ml-2 block text-sm text-gray-300">I agree to the <a href="/terms" class="text-purple-400">Terms of Service</a> and <a href="/privacy" class="text-purple-400">Privacy Policy</a></label>
      </div>

      <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-pink-500">Create Account</button>
    </form>

    <div class="mt-6 text-center">
      <p class="text-sm text-gray-400">Already have an account? <a href="/login" class="font-medium text-purple-400">Sign in</a></p>
    </div>
  </div>
</div>
