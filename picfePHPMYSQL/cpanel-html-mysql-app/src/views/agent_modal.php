<!-- Interactive Prompt Enhancement Agent Modal -->
<div id="prompt-agent-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="bg-gray-800 rounded-xl max-w-4xl w-full mx-4 h-3/4 flex flex-col">
        <!-- Agent Header -->
        <div class="flex justify-between items-center p-4 md:p-6 border-b border-gray-700">
            <h2 class="text-lg md:text-xl font-bold text-white">PictureThis Agent</h2>
            <div class="flex items-center gap-2 md:gap-4">
                <div class="text-xs md:text-sm text-gray-400">
                    <span id="agent-session-timer">30:00</span> remaining
                </div>
                <button id="close-agent-modal" class="text-gray-400 hover:text-white text-xl">&times;</button>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div id="agent-messages" class="flex-1 overflow-y-auto p-3 md:p-6 flex flex-col">
            <!-- Messages will be dynamically added here -->
            <div class="text-center text-gray-400 py-4 md:py-8">
                <div class="animate-pulse">Initializing agent...</div>
            </div>
        </div>

        <!-- Refined Prompt Area -->
        <div id="agent-refined-prompt" class="hidden px-3 md:px-6 pb-2 md:pb-4">
            <div class="border-t border-gray-700 pt-2 md:pt-4">
                <h3 class="text-sm md:text-lg font-semibold text-white mb-2 md:mb-3">Current Refined Prompt</h3>
                <div id="refined-prompt-container" class="bg-gray-700 p-2 md:p-4 rounded-lg border border-gray-600">
                    <div id="refined-prompt-text" class="text-xs md:text-sm text-gray-300 mb-2 md:mb-3 leading-tight"></div>
                    <button
                        id="use-refined-prompt"
                        class="w-full bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-2 md:py-2 text-sm md:text-base rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Use This Prompt for Image Generation
                    </button>
                </div>
            </div>
        </div>

        <!-- Message Input -->
        <div class="p-3 md:p-6 border-t border-gray-700">
            <div class="flex gap-2 md:gap-3">
                <input
                    type="text"
                    id="agent-input"
                    placeholder="Tell me more about your vision..."
                    class="flex-1 p-2 md:p-3 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white text-sm md:text-base"
                    maxlength="500"
                >
                <button
                    id="send-agent-message"
                    class="px-4 md:px-6 py-2 md:py-3 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-600 text-white rounded-lg transition-colors disabled:cursor-not-allowed text-sm md:text-base"
                    disabled
                >
                    <span id="send-text">Send</span>
                    <div id="send-spinner" class="hidden inline-block ml-1 md:ml-2">
                        <div class="animate-spin rounded-full h-3 w-3 md:h-4 md:w-4 border-b-2 border-white"></div>
                    </div>
                </button>
            </div>
            <div class="text-xs text-gray-500 mt-1 md:mt-2">
                <span id="agent-credits-info">Session costs <?php echo htmlspecialchars($enhanceCost ?? 1); ?> credit<?php echo ($enhanceCost ?? 1) != 1 ? 's' : ''; ?> (charged once per session)</span>
            </div>
        </div>
    </div>
</div>

<script>
// Interactive Prompt Enhancement Agent JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Agent modal JavaScript has been moved to generate.php to avoid duplicate DOMContentLoaded handlers
    // This script block is kept for future compatibility but is now empty
});
</script>