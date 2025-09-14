<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Check Your Email</h2>
                <p class="text-gray-600 mb-6">We've sent you a verification link to complete your registration.</p>
            </div>

            <?php if (!empty($_SESSION['auth_error'])): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?php echo htmlspecialchars($_SESSION['auth_error']); unset($_SESSION['auth_error']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['auth_success'])): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <?php echo htmlspecialchars($_SESSION['auth_success']); unset($_SESSION['auth_success']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">What happens next?</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Click the verification link in your email</li>
                                <li>Your account will be activated immediately</li>
                                <li>You can then log in and start creating images</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Didn't receive the email?</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>Check your spam/junk folder</li>
                                <li>The email may take a few minutes to arrive</li>
                                <li>Make sure you entered the correct email address</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resend Verification Email Form -->
            <form action="/resend-verification" method="POST" class="space-y-4">
                <?php echo CSRF::getTokenField(); ?>
                <input type="hidden" name="return_url" value="/check-email">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Resend verification email
                    </label>
                    <div class="flex space-x-2">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter your email address"
                            value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>"
                        >
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Resend
                        </button>
                    </div>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="/login" class="text-sm text-indigo-600 hover:text-indigo-500">
                    ← Back to login
                </a>
            </div>
        </div>
    </div>
</div>