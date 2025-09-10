<footer class="mt-12 border-t border-gray-800 bg-gray-900 text-gray-400">
    <div class="max-w-6xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
            <div class="text-white font-semibold mb-2">PictureThis</div>
            <div class="text-sm">Generate stunning AI images from text or image prompts</div>
        </div>

        <div class="text-sm">
            <div class="font-semibold text-white mb-2">Platform</div>
            <ul class="space-y-2">
                <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
                <?php if (!empty($_SESSION['user']) || !empty($user ?? null)): ?>
                    <li><a href="/generate" class="hover:underline">Generate Images</a></li>
                    <li><a href="/gallery" class="hover:underline">Gallery</a></li>
                <?php endif; ?>
                <li><a href="/pricing" class="hover:underline">Pricing</a></li>
            </ul>
        </div>

        <div class="text-sm">
            <div class="font-semibold text-white mb-2">Company</div>
            <ul class="space-y-2">
                <li><a href="/about" class="hover:underline">About</a></li>
                <li><a href="/privacy" class="hover:underline">Privacy Policy</a></li>
                <li><a href="/terms" class="hover:underline">Terms of Service</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-gray-800 py-6">
        <div class="max-w-6xl mx-auto px-4 text-sm text-gray-500 flex items-center justify-between">
            <div>&copy; <?php echo date('Y'); ?> PictureThis. All rights reserved.</div>
            <div class="flex items-center space-x-4">
                <a href="#" class="hover:text-white">Twitter</a>
                <a href="#" class="hover:text-white">Instagram</a>
                <a href="#" class="hover:text-white">GitHub</a>
            </div>
        </div>
    </div>
</footer>