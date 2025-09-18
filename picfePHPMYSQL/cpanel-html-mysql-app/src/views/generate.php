<?php
// Generate page - matches the functionality of the picfe app
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Cache busting - add timestamp to prevent browser caching
$cacheBust = time();

// Initialize variables with defaults
$user = isset($user) ? $user : (isset($_SESSION['user']) ? $_SESSION['user'] : null);
$settings = isset($settings) && is_array($settings) ? $settings : [];
$recentImages = isset($recentImages) ? $recentImages : [];

// Get fresh credit data from database (like header.php does)
$displayCredits = 0;
if (!empty($_SESSION['user']) || !empty($user)) {
    $userId = $_SESSION['user']['id'] ?? ($user['id'] ?? null);
    if ($userId) {
        try {
            require_once __DIR__ . '/../lib/db.php';
            $pdo = get_db();
            if ($pdo) {
                $stmt = $pdo->prepare('SELECT credits FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$userId]);
                $fresh = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($fresh) {
                    $displayCredits = $fresh['credits'] !== null ? $fresh['credits'] : 0;
                    // Update session with fresh data
                    if (!empty($_SESSION['user'])) {
                        $_SESSION['user']['credits'] = $displayCredits;
                    }
                } else {
                    $displayCredits = $user['credits'] ?? ($_SESSION['user']['credits'] ?? 0);
                }
            } else {
                $displayCredits = $user['credits'] ?? ($_SESSION['user']['credits'] ?? 0);
            }
        } catch (Exception $e) {
            $displayCredits = $user['credits'] ?? ($_SESSION['user']['credits'] ?? 0);
        }
    } else {
        $displayCredits = $user['credits'] ?? ($_SESSION['user']['credits'] ?? 0);
    }
} else {
    $displayCredits = 0;
}

$creditCost = isset($settings['credit_cost_per_image']) ? (int)$settings['credit_cost_per_image'] : 10;
$enhanceCost = isset($settings['enhance_prompt_cost']) ? (int)$settings['enhance_prompt_cost'] : 1;
$enableEnhance = isset($settings['enable_enhance']) ? (bool)$settings['enable_enhance'] : true;

// Get messages from session
$successMessage = $_SESSION['generate_success'] ?? null;
$errorMessage = $_SESSION['generate_error'] ?? null;
$generatedImage = $_SESSION['generated_image'] ?? null;

// Clear session messages
unset($_SESSION['generate_success'], $_SESSION['generate_error'], $_SESSION['generated_image']);
?>
<div class="max-w-6xl mx-auto px-4 py-8">
  <!-- Permission Modal -->
  <div id="permission-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-white">Image Usage Permission</h2>
        <button type="button" id="close-permission-modal" class="text-gray-400 hover:text-white text-2xl">&times;</button>
      </div>
      <div class="text-gray-300 mb-6">
        Before you upload images to our service, we need to confirm that you have the necessary rights and permissions to use these images.
      </div>
      <div class="flex gap-3">
        <button type="button" id="accept-permission" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
          I Accept
        </button>
        <button type="button" id="decline-permission" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg">
          Decline
        </button>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
    <!-- Left side: Input form -->
    <div class="lg:col-span-1 order-2 lg:order-1">
      <div class="bg-gray-800 rounded-xl p-4 md:p-6 border border-gray-700 mb-4 md:mb-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold">Create an Image</h2>
          <div class="flex items-center gap-2">
            <div class="text-yellow-300">*</div>
            <div class="text-sm">
              <span class="font-bold"><?php echo htmlspecialchars($displayCredits); ?></span>
              <span class="text-gray-400">credits</span>
            </div>
          </div>
        </div>

        <!-- Messages -->
        <?php if ($successMessage): ?>
          <div class="mb-6 p-4 bg-green-900/20 border border-green-600 text-green-200 rounded-md">
            <?php echo htmlspecialchars($successMessage); ?>
          </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
          <div class="mb-6 p-4 bg-red-900/20 border border-red-600 text-red-200 rounded-md">
            <?php echo htmlspecialchars($errorMessage); ?>
          </div>
        <?php endif; ?>

        <form id="generate-form" action="/api/generate" method="POST" enctype="multipart/form-data" class="space-y-6">
          <?php echo CSRF::getTokenField(); ?>
          <!-- Text Prompt -->
          <div>
            <label for="prompt" class="block text-sm font-medium text-gray-300 mb-2">
              Text Prompt
            </label>
            <textarea
              id="prompt"
              name="prompt"
              rows="3"
              class="w-full p-3 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white resize-y min-h-[80px] md:min-h-[100px] text-sm md:text-base"
              placeholder="Example: A photorealistic portrait of a young woman with flowing red hair, studio lighting, golden hour, highly detailed skin texture, professional photography, 8K resolution..."
              required
            ></textarea>
          </div>

          <!-- Enhance Prompt Button -->
          <?php if ($enableEnhance): ?>
          <div class="flex gap-3">
            <button
              type="button"
              id="enhance-btn"
              class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              <?php if ($enhanceCost > 0 && $displayCredits < $enhanceCost): ?>disabled<?php endif; ?>
            >
              <span id="enhance-text">Enhance</span>
              <div id="enhance-spinner" class="hidden ml-2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              </div>
              <?php if ($enhanceCost > 0): ?>
                <span class="ml-2 text-xs bg-gray-800 px-2 py-1 rounded">
                  <?php echo htmlspecialchars($enhanceCost); ?> credit<?php echo $enhanceCost != 1 ? 's' : ''; ?>
                </span>
              <?php endif; ?>
            </button>
          </div>    
          <?php endif; ?>

          <!-- Reference Images -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-3">
              Reference Images <span class="text-gray-500">(Optional)</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
              <div class="relative">
                <label for="image1" class="block border-2 border-dashed border-gray-600 rounded-lg p-4 hover:border-purple-500 transition-colors cursor-pointer">
                  <div class="text-center">
                    <div id="image1-preview" class="hidden mb-2">
                      <img id="image1-img" class="w-full h-20 object-cover rounded" />
                    </div>
                    <div id="image1-placeholder" class="text-gray-400">
                      <svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <div class="text-sm">Upload Image 1</div>
                    </div>
                  </div>
                </label>
                <input type="file" id="image1" name="image1" accept="image/*" class="hidden" />
                <button type="button" id="remove-image1" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 text-xs hidden">x</button>
              </div>

              <div class="relative">
                <label for="image2" class="block border-2 border-dashed border-gray-600 rounded-lg p-4 hover:border-purple-500 transition-colors cursor-pointer">
                  <div class="text-center">
                    <div id="image2-preview" class="hidden mb-2">
                      <img id="image2-img" class="w-full h-20 object-cover rounded" />
                    </div>
                    <div id="image2-placeholder" class="text-gray-400">
                      <svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <div class="text-sm">Upload Image 2</div>
                    </div>
                  </div>
                </label>
                <input type="file" id="image2" name="image2" accept="image/*" class="hidden" />
                <button type="button" id="remove-image2" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 text-xs hidden">x</button>
              </div>
            </div>

            <!-- Permission checkbox (shown when images are uploaded) -->
            <div id="permission-section" class="hidden mt-3">
              <label class="flex items-start gap-2">
                <input type="checkbox" id="usage-permission" name="hasUsagePermission" value="true" class="mt-1" />
                <span class="text-sm text-gray-300">
                  I confirm that I have the necessary rights and permissions to use these images
                </span>
              </label>
            </div>
          </div>

          <!-- Generate Button -->
          <div>
            <button
              type="submit"
              id="generate-btn"
              class="w-full bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-90 text-white font-medium py-3 px-4 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              <?php if ($displayCredits < $creditCost): ?>disabled<?php endif; ?>
            >
              <span id="generate-text">Generate Image</span>
              <div id="generate-spinner" class="hidden inline-block ml-2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              </div>
              <span class="ml-2 text-sm bg-gray-900 px-2 py-1 rounded">
                <?php echo htmlspecialchars($creditCost); ?> credits
              </span>
            </button>

            <?php if ($displayCredits < $creditCost): ?>
              <p class="text-red-400 text-sm mt-2">Insufficient credits. You need <?php echo $creditCost; ?> credits.</p>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Prompt Writing Guide -->
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mt-6">
        <div class="bg-gray-700/50 rounded-lg border border-gray-600">
          <div class="flex items-center justify-between p-4 cursor-pointer" onclick="toggleGuidance()">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <h3 class="text-sm font-semibold text-purple-300">Prompt Writing Guide</h3>
            </div>
            <svg id="guidance-chevron" class="w-4 h-4 text-purple-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>

          <div id="guidance-content" class="px-4 pb-4 space-y-3 text-sm text-gray-300">
            <div>
              <h4 class="font-medium text-white mb-1">🎨 Style & Realism</h4>
              <p class="text-xs text-gray-400">Specify the artistic style to help the enhance function optimize for the right model:</p>
              <ul class="mt-1 space-y-1 text-xs">
                <li>• <strong>Photo realistic:</strong> "photorealistic, high detail, professional photography"</li>
                <li>• <strong>Pixar style:</strong> "Pixar animation style, 3D rendered, vibrant colors"</li>
                <li>• <strong>Digital art:</strong> "digital painting, concept art, stylized illustration"</li>
                <li>• <strong>Cartoon:</strong> "cartoon style, animated, vibrant and playful"</li>
              </ul>
            </div>

            <div>
              <h4 class="font-medium text-white mb-1">📸 Composition & Framing</h4>
              <p class="text-xs text-gray-400">Describe camera angle, perspective, and composition:</p>
              <ul class="mt-1 space-y-1 text-xs">
                <li>• <strong>Angle:</strong> "wide angle, close-up, aerial view, eye-level"</li>
                <li>• <strong>Composition:</strong> "centered, rule of thirds, dynamic pose"</li>
                <li>• <strong>Focus:</strong> "sharp focus, shallow depth of field, bokeh background"</li>
              </ul>
            </div>

            <div>
              <h4 class="font-medium text-white mb-1">💡 Lighting & Mood</h4>
              <p class="text-xs text-gray-400">Set the atmosphere and lighting conditions:</p>
              <ul class="mt-1 space-y-1 text-xs">
                <li>• <strong>Lighting:</strong> "golden hour, studio lighting, dramatic shadows"</li>
                <li>• <strong>Mood:</strong> "serene, energetic, mysterious, warm and inviting"</li>
                <li>• <strong>Time:</strong> "sunset, midnight, bright daylight, candlelit"</li>
              </ul>
            </div>

            <div>
              <h4 class="font-medium text-white mb-1">✨ Quality & Details</h4>
              <p class="text-xs text-gray-400">Add technical specifications for better results:</p>
              <ul class="mt-1 space-y-1 text-xs">
                <li>• <strong>Quality:</strong> "highly detailed, 8K, sharp focus, professional"</li>
                <li>• <strong>Medium:</strong> "oil painting, watercolor, digital art, photography"</li>
                <li>• <strong>Resolution:</strong> "ultra high resolution, crisp details, fine textures"</li>
              </ul>
            </div>

            <div class="mt-3 p-3 bg-purple-900/20 border border-purple-600/30 rounded">
              <h4 class="font-medium text-purple-300 mb-2">🚀 Pro Tip for Enhancement</h4>
              <p class="text-xs text-gray-300">
                The enhance button uses AI to transform your prompt into multiple professional versions. Include both your subject AND desired style for the best enhancement results!
              </p>
            </div>

            <div class="mt-2 p-2 bg-blue-900/20 border border-blue-600/30 rounded">
              <h4 class="font-medium text-blue-300 mb-1">💡 Enhancement Examples</h4>
              <div class="text-xs text-gray-300 space-y-1">
                <div><strong>Simple:</strong> "❌cat on a chair"</div>
                <div><strong>Enhance:</strong> "✅A photorealistic image of a cat on a chair, 8K resolution"</div>
                <div class="mt-2"><strong>Simple:</strong> "❌robot in city"</div>
                <div><strong>Enhance:</strong> "✅robot in futuristic city, digital art style cinematic composition"</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Images -->
      <?php if (!empty($recentImages)): ?>
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h3 class="text-lg font-semibold mb-4">Recent Images</h3>
        <div class="space-y-3">
          <?php foreach ($recentImages as $image): ?>
            <div class="flex gap-3 p-3 bg-gray-700 rounded-lg">
              <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="Generated image" class="w-16 h-16 object-cover rounded" />
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-300 truncate"><?php echo htmlspecialchars($image['prompt']); ?></p>
                <p class="text-xs text-gray-500"><?php echo date('M j, H:i', strtotime($image['created_at'])); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Right side: Generated image preview -->
    <div class="lg:col-span-2 order-1 lg:order-2">
      <div class="bg-gray-800 rounded-xl p-4 md:p-6 border border-gray-700">
        <h2 class="text-2xl font-bold mb-6">Generated Image</h2>

        <div id="image-preview" class="bg-gray-900 rounded-lg p-8 flex items-center justify-center min-h-[500px]">
          <?php if ($generatedImage): ?>
            <div class="text-center w-full">
              <img
                src="<?php echo htmlspecialchars($generatedImage['imageUrl'] ?? $generatedImage['image_url']); ?>"
                alt="Generated image"
                class="max-w-full max-h-96 mx-auto rounded-lg shadow-lg mb-4"
              />
              <p class="text-gray-300 mb-4"><?php echo htmlspecialchars($generatedImage['prompt'] ?? ''); ?></p>
              <div class="flex gap-3 justify-center">
                <a
                  href="<?php echo htmlspecialchars($generatedImage['imageUrl'] ?? $generatedImage['image_url']); ?>"
                  download="generated_image_<?php echo time(); ?>.png"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Download
                </a>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center text-gray-300">
              <svg class="mx-auto h-20 w-20 mb-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              
              <h3 class="text-xl font-semibold mb-3 text-white">Ready to Create Amazing AI Images</h3>
              
              <div class="max-w-md mx-auto space-y-4 text-sm">
                <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600">
                  <h4 class="font-medium text-purple-300 mb-2">🎨 Getting Started</h4>
                  <ul class="text-left space-y-1 text-gray-300">
                    <li>• Enter a descriptive prompt in the text box</li>
                    <li>• Optionally upload reference images</li>
                    <li>• Click "Generate Image" to create your artwork</li>
                  </ul>
                </div>
                
                <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600">
                  <h4 class="font-medium text-purple-300 mb-2">💡 Pro Tips</h4>
                  <ul class="text-left space-y-1 text-gray-300">
                    <li>• Use the "Enhance Prompt" button for better results</li>
                    <li>• Be specific about style, lighting, and composition</li>
                    <li>• Try different art styles: realistic, cartoon, digital art</li>
                  </ul>
                </div>
                
                <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600">
                  <h4 class="font-medium text-purple-300 mb-2">⚡ Features Available</h4>
                  <ul class="text-left space-y-1 text-gray-300">
                    <li>• AI-powered prompt enhancement</li>
                    <li>• Reference image support</li>
                    <li>• Multiple art styles and formats</li>
                    <li>• High-resolution image generation</li>
                  </ul>
                </div>
              </div>
              
              <div class="mt-6 text-xs text-gray-500">
                Your generated images will appear here once you create them
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Include Agent Modal -->
  <?php include __DIR__ . '/agent_modal.php'; ?>

</div>

<script>
// Wrap all JavaScript in DOM ready event
document.addEventListener('DOMContentLoaded', function() {
  // Global variables for agent session
  var sessionTimer = null;
  var sessionExpiryTime = null;
  
  // Agent session timeout configuration (in minutes)
  const AGENT_SESSION_TIMEOUT_MINUTES = <?php echo defined('AGENT_SESSION_TIMEOUT_MINUTES') ? AGENT_SESSION_TIMEOUT_MINUTES : 30; ?>;

  // Toggle guidance function
  window.toggleGuidance = function() {
    var content = document.getElementById('guidance-content');
    var chevron = document.getElementById('guidance-chevron');
    if (content.classList.contains('hidden')) {
      content.classList.remove('hidden');
      chevron.style.transform = 'rotate(0deg)';
    } else {
      content.classList.add('hidden');
      chevron.style.transform = 'rotate(180deg)';
    }
  };

  // Image upload handling
  var image1Input = document.getElementById('image1');
  var image2Input = document.getElementById('image2');
  
  if (image1Input) {
    image1Input.addEventListener('change', function(e) {
      handleImageUpload(e.target, 1);
    });
  }
  
  if (image2Input) {
    image2Input.addEventListener('change', function(e) {
      handleImageUpload(e.target, 2);
    });
  }

  // Remove image handlers
  var removeImage1Btn = document.getElementById('remove-image1');
  var removeImage2Btn = document.getElementById('remove-image2');
  
  if (removeImage1Btn) {
    removeImage1Btn.addEventListener('click', function() {
      removeImage(1);
    });
  }
  
  if (removeImage2Btn) {
    removeImage2Btn.addEventListener('click', function() {
      removeImage(2);
    });
  }

  // Permission modal handling
  var acceptPermissionBtn = document.getElementById('accept-permission');
  var declinePermissionBtn = document.getElementById('decline-permission');
  var closePermissionModalBtn = document.getElementById('close-permission-modal');
  
  if (acceptPermissionBtn) {
    acceptPermissionBtn.addEventListener('click', function() {
      var usagePermission = document.getElementById('usage-permission');
      if (usagePermission) usagePermission.checked = true;
      var permissionModal = document.getElementById('permission-modal');
      if (permissionModal) permissionModal.classList.add('hidden');
    });
  }
  
  if (declinePermissionBtn) {
    declinePermissionBtn.addEventListener('click', function() {
      var usagePermission = document.getElementById('usage-permission');
      if (usagePermission) usagePermission.checked = false;
      var permissionModal = document.getElementById('permission-modal');
      if (permissionModal) permissionModal.classList.add('hidden');
    });
  }
  
  if (closePermissionModalBtn) {
    closePermissionModalBtn.addEventListener('click', function() {
      var permissionModal = document.getElementById('permission-modal');
      if (permissionModal) permissionModal.classList.add('hidden');
    });
  }

  // Form submission handling
  var generateForm = document.getElementById('generate-form');
  if (generateForm) {
    generateForm.addEventListener('submit', function(e) {
      var image1 = document.getElementById('image1');
      var image2 = document.getElementById('image2');
      var hasImages = (image1 && image1.files[0]) || (image2 && image2.files[0]);
      var usagePermission = document.getElementById('usage-permission');
      var permissionChecked = usagePermission ? usagePermission.checked : false;

      console.log('Form submission - Images detected:', {
        image1: image1 && image1.files[0] ? image1.files[0].name + ' (' + (image1.files[0].size / 1024 / 1024).toFixed(2) + 'MB)' : 'none',
        image2: image2 && image2.files[0] ? image2.files[0].name + ' (' + (image2.files[0].size / 1024 / 1024).toFixed(2) + 'MB)' : 'none',
        hasImages: hasImages,
        permissionChecked: permissionChecked
      });

      if (hasImages && !permissionChecked) {
        e.preventDefault();
        var permissionModal = document.getElementById('permission-modal');
        if (permissionModal) permissionModal.classList.remove('hidden');
        return;
      }

      // Check file sizes before submission
      if (hasImages) {
        var maxSize = 8 * 1024 * 1024; // 8MB (increased from 2MB)
        var tooLarge = false;

        if (image1 && image1.files[0] && image1.files[0].size > maxSize) {
          tooLarge = true;
          console.error('Image 1 too large: ' + (image1.files[0].size / 1024 / 1024).toFixed(2) + 'MB > 8MB');
        }
        if (image2 && image2.files[0] && image2.files[0].size > maxSize) {
          tooLarge = true;
          console.error('Image 2 too large: ' + (image2.files[0].size / 1024 / 1024).toFixed(2) + 'MB > 8MB');
        }

        if (tooLarge) {
          e.preventDefault();
          alert('One or more images are too large. Maximum size is 8MB per image. Please try smaller images or different formats.');
          return;
        }
      }

      // Show loading state
      var btn = document.getElementById('generate-btn');
      var text = document.getElementById('generate-text');
      var spinner = document.getElementById('generate-spinner');

      if (btn) btn.disabled = true;
      if (text) text.textContent = 'Generating...';
      if (spinner) spinner.classList.remove('hidden');

      // Close agent session if one is active
      if (typeof window.currentAgentSession !== 'undefined' && window.currentAgentSession) {
        closeAgentSessionOnImageGeneration(window.currentAgentSession);
      }
    });
  }

  // Update credits on page load if we have a success message (indicating recent transaction)
  var successMessage = document.querySelector('.bg-green-900\\/20');
  if (successMessage && successMessage.textContent.includes('generated successfully')) {
    // Fetch current credits from server
    fetch('/api/user/credits', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
    .then(function(response) {
      console.log('Credits response status:', response.status);
      console.log('Credits response headers:', Array.from(response.headers.entries()));
      return response.text();
    })
    .then(function(text) {
      console.log('Credits response text:', text);
      try {
        var data = JSON.parse(text);
        if (data.success && data.credits !== undefined) {
          updateCreditDisplays(data.credits);
        }
      } catch (e) {
        console.error('JSON parse error:', e);
        console.error('Response text:', text);
      }
    })
    .catch(function(error) {
      console.log('Could not fetch updated credits:', error);
    });
  }

  // Event listeners
  // DUPLICATE CODE - Commented out to avoid conflicts
  /*
  window.startAgentSession = function(originalPrompt) {
    console.log('startAgentSession called with prompt:', originalPrompt);
    // Convert to promise-based approach instead of async/await
    showAgentModal();

    // Get fresh CSRF token
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
      console.error('CSRF token not found');
      alert('CSRF token not found');
      hideAgentModal();
      return;
    }
    var csrfToken = csrfMeta.getAttribute('content');
    console.log('CSRF token found:', csrfToken ? 'YES' : 'NO');

    console.log('Making API request to /api/prompt-agent/start');
    fetch('/api/prompt-agent/start', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({ prompt: originalPrompt })
    })
    .then(function(response) {
      console.log('API response received, status:', response.status);
      if (!response.ok) {
        // Handle HTTP error responses
        if (response.status === 401) {
          console.log('401 Unauthorized - redirecting to login');
          throw new Error('Authentication required. Please log in to use the agent.');
        } else if (response.status === 400) {
          return response.json().then(function(data) {
            console.log('400 Bad Request, response data:', data);
            throw new Error(data.message || 'Invalid request');
          });
        } else {
          console.log('Other error status:', response.status);
          throw new Error('Server error: ' + response.status);
        }
      }
      return response.json();
    })
    .then(function(result) {
      console.log('API response parsed:', result);
      if (result.success) {
        window.currentAgentSession = result.data.sessionId;
        
        // Parse expiresAt more robustly
        var expiresAtStr = result.data.expiresAt;
        
        // Try to parse MySQL datetime format (YYYY-MM-DD HH:MM:SS)
        if (expiresAtStr && typeof expiresAtStr === 'string') {
          // Replace space with 'T' to make it ISO format, assume UTC
          var isoString = expiresAtStr.replace(' ', 'T') + 'Z';
          sessionEndTime = new Date(isoString).getTime();
          
          // If parsing failed, try alternative methods
          if (isNaN(sessionEndTime)) {
            // Fallback: parse manually
            var parts = expiresAtStr.split(' ');
            if (parts.length === 2) {
              var dateParts = parts[0].split('-');
              var timeParts = parts[1].split(':');
              if (dateParts.length === 3 && timeParts.length === 3) {
                sessionEndTime = new Date(
                  parseInt(dateParts[0]),     // year
                  parseInt(dateParts[1]) - 1, // month (0-based)
                  parseInt(dateParts[2]),     // day
                  parseInt(timeParts[0]),     // hour
                  parseInt(timeParts[1]),     // minute
                  parseInt(timeParts[2])      // second
                ).getTime();
              }
            }
          }
        }
        
        // If still invalid, set to configured timeout from now as fallback
        if (!sessionEndTime || isNaN(sessionEndTime)) {
          console.warn('Failed to parse expiresAt:', expiresAtStr, 'Using fallback');
          sessionEndTime = new Date().getTime() + (AGENT_SESSION_TIMEOUT_MINUTES * 60 * 1000); // Configured timeout
        }

        // Clear loading message and add welcome message
        if (agentMessages) {
          agentMessages.innerHTML = '';
          addMessage('agent', result.data.welcomeMessage);
          scrollToLatestContent();
        }

        // Start session timer
        if (sessionEndTime && sessionEndTime > new Date().getTime()) {
          startSessionTimer();
        } else {
          console.error('Invalid session end time:', sessionEndTime, 'Current time:', new Date().getTime());
          addMessage('system', '⚠️ Session configuration error. Please try again.');
          setTimeout(function() { endAgentSession(); }, 3000);
        }

        // Enable input
        agentInput.disabled = false;
        sendAgentMessage.disabled = false;
        agentInput.focus();

      } else {
        throw new Error(result.message || 'Failed to start agent session');
      }
    })
    .catch(function(error) {
      console.error('Failed to start agent session:', error);
      
      // Show user-friendly error message
      if (error.message.includes('Authentication required')) {
        alert('Please log in to use the AI prompt enhancement agent.');
        // Redirect to login page
        window.location.href = '/login';
      } else if (error.message.includes('Insufficient credits')) {
        alert('You don\'t have enough credits to use the agent. Please purchase more credits.');
        hideAgentModal();
      } else {
        alert('Failed to start agent session: ' + error.message);
        hideAgentModal();
      }
    });
  }
  */

  // DUPLICATE CODE - Commented out to avoid conflicts
  /*
    if (agentModal) {
      agentModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
  }

  function hideAgentModal() {
    if (agentModal) {
      agentModal.classList.add('hidden');
      document.body.style.overflow = 'auto';
    }
  }

  // Helper function to auto-scroll the agent modal to show latest content
  function scrollToLatestContent() {
    setTimeout(function() {
      // First scroll the messages area to the bottom
      if (agentMessages) {
        agentMessages.scrollTop = agentMessages.scrollHeight;
      }

      // Then scroll the refined prompt into view if it's visible
      var agentRefinedPrompt = document.getElementById('agent-refined-prompt');
      if (agentRefinedPrompt && !agentRefinedPrompt.classList.contains('hidden')) {
        agentRefinedPrompt.scrollIntoView({ behavior: 'smooth', block: 'end' });
      }
    }, 100);
  }



  function sendAgentMessageHandler() {
    var message = agentInput.value.trim();
    if (!message || !window.currentAgentSession) return;

    // Disable input while processing
    agentInput.disabled = true;
    sendAgentMessage.disabled = true;

    // Show sending state
    document.getElementById('send-text').textContent = 'Sending...';
    document.getElementById('send-spinner').classList.remove('hidden');

    // Get CSRF token
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta.getAttribute('content');

    fetch('/api/prompt-agent/message', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        sessionId: window.currentAgentSession,
        message: message
      })
    })
    .then(function(response) {
      if (!response.ok) {
        // Handle HTTP error responses
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in again.');
        } else if (response.status === 400) {
          return response.json().then(function(data) {
            throw new Error(data.message || 'Invalid request');
          });
        } else {
          throw new Error('Server error: ' + response.status);
        }
      }
      return response.json();
    })
    .then(function(result) {
      // Add user message to UI
      addMessage('user', message);
      agentInput.value = '';

      if (result.success) {
        // Add agent response
        addMessage('agent', result.data.message);

        // Show refined prompt if provided
        if (result.data.refinedPrompt) {
          showRefinedPrompt(result.data.refinedPrompt);
        }

        // Auto-scroll to show the latest content
        scrollToLatestContent();

        // Update credits display
        if (result.data.creditsUsed) {
          updateCreditDisplays(-result.data.creditsUsed);
        }

      } else {
        if (result.message.includes('rate limit') || result.message.includes('limit')) {
          addMessage('system', '⚠️ ' + result.message);
          scrollToLatestContent();
        } else {
          throw new Error(result.message || 'Failed to send message');
        }
      }
    })
    .catch(function(error) {
      console.error('Failed to send message:', error);
      addMessage('system', '❌ Error: ' + error.message);
      scrollToLatestContent();
    })
    .finally(function() {
      // Re-enable input
      agentInput.disabled = false;
      sendAgentMessage.disabled = false;
      document.getElementById('send-text').textContent = 'Send';
      document.getElementById('send-spinner').classList.add('hidden');
      agentInput.focus();
    });
  }

  function endAgentSession() {
    if (window.currentAgentSession) {
      // Get CSRF token
      var csrfMeta = document.querySelector('meta[name="csrf-token"]');
      var csrfToken = csrfMeta.getAttribute('content');

      fetch('/api/prompt-agent/end', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
          sessionId: window.currentAgentSession
        })
      })
      .catch(function(error) {
        console.error('Failed to end session:', error);
      });
    }

    window.currentAgentSession = null;
    if (sessionTimer) {
      clearInterval(sessionTimer);
      sessionTimer = null;
    }
    hideAgentModal();
  }





  function updateAgentCreditDisplays(creditChange) {
    // Update credit displays (reuse existing function from generate.php)
    if (typeof updateCreditDisplays === 'function') {
      // Get current credits and add the change
      var creditElements = document.querySelectorAll('.font-bold');
      creditElements.forEach(function(element) {
        if (element.textContent.match(/^\d+$/)) {
          var currentCredits = parseInt(element.textContent);
          element.textContent = Math.max(0, currentCredits + creditChange);
        }
      });
    }
  }

  // Cleanup on page unload
  window.addEventListener('beforeunload', function() {
    if (window.currentAgentSession) {
      // Note: This won't actually send the request due to browser limitations
      // The session will be cleaned up by the server based on expiration
      navigator.sendBeacon('/api/prompt-agent/end', JSON.stringify({
        sessionId: window.currentAgentSession
      }));
    }
  });
  */

function handleImageUpload(input, index) {
  var file = input.files[0];
  if (file) {
    // Check if file is an image
    if (!file.type.startsWith('image/')) {
      alert('Please select a valid image file');
      input.value = '';
      return;
    }

    // Check for extremely large files (over 15MB)
    if (file.size > 15 * 1024 * 1024) {
      alert('Image is too large (>15MB). Please choose a smaller image.');
      input.value = '';
      return;
    }

    // If file is larger than 3MB, compress it
    if (file.size > 3 * 1024 * 1024) {
      console.log('Compressing image ' + index + ' (' + (file.size / 1024 / 1024).toFixed(2) + 'MB)...');
      showCompressionStatus(index, true);
      compressImage(file, index, input);
    } else {
      // File is small enough, proceed normally
      displayImagePreview(file, index);
    }
  }
}

function showCompressionStatus(index, show) {
  var placeholder = document.getElementById('image' + index + '-placeholder');
  if (placeholder) {
    if (show) {
      placeholder.innerHTML = '<div class=\"text-center\"><div class=\"animate-spin rounded-full h-6 w-6 border-b-2 border-purple-500 mx-auto mb-2\"></div><div class=\"text-xs\">Compressing...</div></div>';
    } else {
      placeholder.innerHTML = '<svg class=\"mx-auto h-8 w-8 mb-2\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 6v6m0 0v6m0-6h6m-6 0H6\"></path></svg><div class=\"text-sm\">Upload Image ' + index + '</div>';
    }
  }
}

function compressImage(file, index, input) {
  var canvas = document.createElement('canvas');
  var ctx = canvas.getContext('2d');
  var img = new Image();

  img.onload = function() {
    try {
      // Calculate new dimensions (max 1920px on longest side)
      var maxDimension = 1920;
      var width = img.width;
      var height = img.height;

      if (width > height) {
        if (width > maxDimension) {
          height = (height * maxDimension) / width;
          width = maxDimension;
        }
      } else {
        if (height > maxDimension) {
          width = (width * maxDimension) / height;
          height = maxDimension;
        }
      }

      canvas.width = width;
      canvas.height = height;

      // Draw and compress
      ctx.drawImage(img, 0, 0, width, height);

      canvas.toBlob(function(blob) {
        if (blob) {
          // Create a new file from the compressed blob
          var compressedFile = new File([blob], file.name, {
            type: file.type,
            lastModified: Date.now()
          });

          console.log('Image ' + index + ' compressed: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB -> ' + (compressedFile.size / 1024 / 1024).toFixed(2) + 'MB');

          // Replace the original file with the compressed one
          var dataTransfer = new DataTransfer();
          dataTransfer.items.add(compressedFile);
          input.files = dataTransfer.files;

          // Hide compression status and display the preview
          showCompressionStatus(index, false);
          displayImagePreview(compressedFile, index);
        } else {
          console.error('Compression failed for image', index);
          showCompressionStatus(index, false);
          alert('Failed to compress image. Please try a different image.');
          input.value = '';
        }
      }, file.type, 0.85); // 85% quality
    } catch (error) {
      console.error('Error during compression:', error);
      showCompressionStatus(index, false);
      alert('Error compressing image. Please try a different image.');
      input.value = '';
    }
  };

  img.onerror = function() {
    console.error('Failed to load image for compression');
    showCompressionStatus(index, false);
    alert('Failed to load image. Please try a different image.');
    input.value = '';
  };

  img.src = URL.createObjectURL(file);
}

function displayImagePreview(file, index) {
  var reader = new FileReader();
  reader.onload = function(e) {
    var preview = document.getElementById('image' + index + '-preview');
    var img = document.getElementById('image' + index + '-img');
    var placeholder = document.getElementById('image' + index + '-placeholder');
    var removeBtn = document.getElementById('remove-image' + index);

    if (img && e.target && e.target.result) img.src = e.target.result;
    if (preview) preview.classList.remove('hidden');
    if (placeholder) placeholder.classList.add('hidden');
    if (removeBtn) removeBtn.classList.remove('hidden');

    // Show permission section
    var permissionSection = document.getElementById('permission-section');
    if (permissionSection) permissionSection.classList.remove('hidden');

    console.log('Image ' + index + ' ready for upload: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
  };
  reader.readAsDataURL(file);
}

function removeImage(index) {
  var input = document.getElementById('image' + index);
  var preview = document.getElementById('image' + index + '-preview');
  var placeholder = document.getElementById('image' + index + '-placeholder');
  var removeBtn = document.getElementById('remove-image' + index);

  if (input) input.value = '';
  if (preview) preview.classList.add('hidden');
  if (placeholder) placeholder.classList.remove('hidden');
  if (removeBtn) removeBtn.classList.add('hidden');

  // Hide permission section if no images
  var image1 = document.getElementById('image1');
  var image2 = document.getElementById('image2');
  var hasImage1 = image1 && image1.files[0];
  var hasImage2 = image2 && image2.files[0];
  
  if (!hasImage1 && !hasImage2) {
    var permissionSection = document.getElementById('permission-section');
    if (permissionSection) permissionSection.classList.add('hidden');
  }
}

// Function to close agent session when image is generated
function closeAgentSessionOnImageGeneration(sessionId) {
  var prompt = document.getElementById('prompt');
  var finalPrompt = prompt ? prompt.value : '';

  // Get fresh CSRF token
  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  if (!csrfMeta) {
    console.error('CSRF token not found for session closure');
    return;
  }
  var csrfToken = csrfMeta.getAttribute('content');

  fetch('/api/prompt-agent/close-on-generation', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken
    },
    credentials: 'same-origin',
    body: JSON.stringify({
      sessionId: sessionId,
      finalPrompt: finalPrompt
    })
  })
  .then(function(response) {
    if (response.ok) {
      console.log('Agent session closed successfully on image generation');
      // Reset agent session variable
      if (typeof window.currentAgentSession !== 'undefined') {
        window.currentAgentSession = null;
      }
    } else {
      console.error('Failed to close agent session on image generation');
    }
  })
  .catch(function(error) {
    console.error('Error closing agent session:', error);
  });
}

function updateCreditDisplays(newCredits) {
  console.log('=== CREDIT UPDATE START ===');
  console.log('Target credits value:', newCredits, 'Type:', typeof newCredits);

  // Clear any existing credit display elements to prevent duplicates
  var processedElements = new Set();

  // Update credit displays with precise selectors - process in specific order
  var creditSelectors = [
    { selector: '.font-bold', type: 'generate-page' },
    { selector: '.text-yellow-300.font-semibold', type: 'header' },
    { selector: '.text-2xl.font-bold.text-yellow-300', type: 'profile' }
  ];

  creditSelectors.forEach(function(item) {
    var elements = document.querySelectorAll(item.selector);
    console.log('=== ANALYZING', item.type.toUpperCase(), 'ELEMENTS ===');
    console.log('Found', elements.length, 'elements with selector:', item.selector);

    elements.forEach(function(element, index) {
      console.log(item.type, 'element', index, ':', {
        content: element.textContent,
        tagName: element.tagName,
        className: element.className,
        hasCreditsSibling: element.nextElementSibling && element.nextElementSibling.textContent.trim() === 'credits',
        nextSibling: element.nextElementSibling ? element.nextElementSibling.textContent.trim() : 'none'
      });
    });

    elements.forEach(function(element, index) {
      // Skip if we've already processed this element
      if (processedElements.has(element)) {
        console.log('Skipping already processed element', index);
        return;
      }

      console.log('Processing', item.type, 'element', index, 'content:', element.textContent);

      var shouldUpdate = false;

      // For generate page: check for sibling with 'credits' text
      if (item.type === 'generate-page') {
        if (element.textContent.match(/^\d+$/) && element.nextElementSibling &&
            element.nextElementSibling.textContent.trim() === 'credits') {
          shouldUpdate = true;
          console.log('✓ Element', index, 'QUALIFIES for generate page update');
        } else {
          console.log('✗ Element', index, 'does NOT qualify for generate page update');
        }
      }
      // For header and profile: check if element contains only digits
      else if (element.textContent.match(/^\d+$/)) {
        shouldUpdate = true;
        console.log('✓ Element', index, 'QUALIFIES for', item.type, 'update');
      } else {
        console.log('✗ Element', index, 'does NOT qualify for', item.type, 'update');
      }

      if (shouldUpdate) {
        console.log('UPDATING', item.type, 'element', index, 'from', element.textContent, 'to', newCredits);
        console.log('Element details:', element.tagName, element.className, 'HTML:', element.outerHTML);

        // Ensure we're setting a string value
        element.textContent = String(newCredits);

        console.log('AFTER UPDATE - element', index, 'content:', element.textContent);
        processedElements.add(element);
      } else {
        console.log('NOT updating', item.type, 'element', index, '(does not match criteria)');
      }
    });
  });

  console.log('=== CREDIT UPDATE COMPLETE ===');
  console.log('Processed', processedElements.size, 'unique elements');
}

// Agent Modal JavaScript
// Note: currentAgentSession, sessionTimer, and sessionExpiryTime are already declared as global variables

// Add message to chat
// Send message to agent
// Show refined prompt
// Use refined prompt
// Event listeners
});

// Start session timer
// Close agent modal
// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Global variables for agent session
  var sessionTimer = null;
  var sessionExpiryTime = null;
  window.currentAgentSession = null;

  // Agent modal functions
  function startAgentSession(originalPrompt) {
    console.log('startAgentSession called with prompt:', originalPrompt);
    showAgentModal();
    
    // Get fresh CSRF token
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
      console.error('CSRF token not found');
      alert('CSRF token not found');
      closeAgentModal();
      return;
    }
    var csrfToken = csrfMeta.getAttribute('content');
    console.log('CSRF token found:', csrfToken ? 'YES' : 'NO');

    console.log('Making API request to /api/prompt-agent/start');
    fetch('/api/prompt-agent/start', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        prompt: originalPrompt
      })
    })
    .then(function(response) {
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in again.');
        } else if (response.status === 400) {
          return response.json().then(function(data) {
            throw new Error(data.message || 'Invalid request');
          });
        } else {
          throw new Error('Server error: ' + response.status);
        }
      }
      return response.json();
    })
    .then(function(result) {
      console.log('Agent session start result:', result);

      if (result.success) {
        window.currentAgentSession = result.data.sessionId;
        sessionExpiryTime = null;

        var expiresAtStr = result.data.expiresAt;
        console.log('Raw expiresAt:', expiresAtStr);

        if (expiresAtStr) {
          var sessionEndTime = null;
          
          // Check if it's a Unix timestamp (number)
          if (typeof expiresAtStr === 'number' || !isNaN(parseInt(expiresAtStr))) {
            // Unix timestamp in seconds, convert to milliseconds
            sessionEndTime = parseInt(expiresAtStr) * 1000;
            console.log('Parsed Unix timestamp:', new Date(sessionEndTime));
          } else {
            // Try parsing as date string
            try {
              sessionEndTime = new Date(expiresAtStr).getTime();
              console.log('Parsed date string:', new Date(sessionEndTime));
            } catch (e) {
              console.warn('Failed to parse date string, trying other formats');
            }

            // If still failed, try manual parsing
            if (!sessionEndTime || isNaN(sessionEndTime)) {
              try {
                var timeParts = expiresAtStr.split(/[- :]/);
                if (timeParts.length >= 6) {
                  sessionEndTime = new Date(
                    parseInt(timeParts[0]),
                    parseInt(timeParts[1]) - 1,
                    parseInt(timeParts[2]),
                    parseInt(timeParts[3]),
                    parseInt(timeParts[4]),
                    parseInt(timeParts[5])
                  ).getTime();
                }
              } catch (e) {
                console.warn('Failed to parse manual date');
              }
            }
          }
        }
        
        if (!sessionEndTime || isNaN(sessionEndTime)) {
          console.warn('Failed to parse expiresAt:', expiresAtStr, 'Using fallback');
          sessionEndTime = new Date().getTime() + (30 * 60 * 1000);
        }

        // Set the global session expiry time for the timer
        sessionExpiryTime = sessionEndTime;

        var agentMessages = document.getElementById('agent-messages');
        if (agentMessages) {
          agentMessages.innerHTML = '';
          // Add the first agent message
          addMessage('agent', result.data.message);
          // Set the refined prompt in the input field
          var promptInput = document.getElementById('prompt');
          if (promptInput && result.data.refinedPrompt) {
            promptInput.value = result.data.refinedPrompt;
            // Also show the refined prompt in the modal
            showRefinedPrompt(result.data.refinedPrompt);
          }
          scrollToLatestContent();
        }

        // Update credit display if updated credits were provided
        if (result.data.updatedCredits !== null && result.data.updatedCredits !== undefined) {
          console.log('=== BACKEND RESPONSE RECEIVED ===');
          console.log('Raw result.data.updatedCredits:', result.data.updatedCredits);
          console.log('Type of updatedCredits:', typeof result.data.updatedCredits);

          // Ensure it's a number
          var numericCredits = parseInt(result.data.updatedCredits, 10);
          console.log('Parsed numeric credits:', numericCredits);

          console.log('Current credit displays BEFORE update:');
          // Log current credit displays
          document.querySelectorAll('.font-bold').forEach(function(el, idx) {
            if (el.textContent.match(/^\d+$/) && el.nextElementSibling && el.nextElementSibling.textContent.trim() === 'credits') {
              console.log('Generate page credits (element', idx, '):', el.textContent);
            }
          });
          document.querySelectorAll('.text-yellow-300.font-semibold').forEach(function(el, idx) {
            if (el.textContent.match(/^\d+$/)) {
              console.log('Header credits (element', idx, '):', el.textContent);
            }
          });

          console.log('Calling updateCreditDisplays with:', numericCredits);
          updateCreditDisplays(numericCredits);

          console.log('Credit displays AFTER update:');
          // Log credit displays after update
          document.querySelectorAll('.font-bold').forEach(function(el, idx) {
            if (el.textContent.match(/^\d+$/) && el.nextElementSibling && el.nextElementSibling.textContent.trim() === 'credits') {
              console.log('Generate page credits (element', idx, '):', el.textContent);
            }
          });
          document.querySelectorAll('.text-yellow-300.font-semibold').forEach(function(el, idx) {
            if (el.textContent.match(/^\d+$/)) {
              console.log('Header credits (element', idx, '):', el.textContent);
            }
          });
          console.log('=== BACKEND RESPONSE PROCESSING COMPLETE ===');
        } else {
          console.warn('No updatedCredits received from backend');
        }

        if (sessionEndTime && sessionEndTime > new Date().getTime()) {
          startSessionTimer();
        } else {
          console.error('Invalid session end time:', sessionEndTime);
          addMessage('system', '⚠️ Session configuration error. Please try again.');
          scrollToLatestContent();
          setTimeout(function() { closeAgentModal(); }, 3000);
        }

        var agentInput = document.getElementById('agent-input');
        var sendAgentMessage = document.getElementById('send-agent-message');
        if (agentInput) agentInput.disabled = false;
        if (sendAgentMessage) sendAgentMessage.disabled = false;
        if (agentInput) agentInput.focus();

      } else {
        throw new Error(result.message || 'Failed to start agent session');
      }
    })
    .catch(function(error) {
      console.error('Failed to start agent session:', error);
      alert('Failed to start agent session: ' + error.message);
      closeAgentModal();
    });
  }

  function sendAgentMessage() {
    var agentInput = document.getElementById('agent-input');
    var sendAgentMessage = document.getElementById('send-agent-message');
    
    var message = agentInput.value.trim();
    if (!message) return;

    agentInput.disabled = true;
    sendAgentMessage.disabled = true;

    addMessage('agent', '🤔 Thinking...');

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta.getAttribute('content');

    fetch('/api/prompt-agent/message', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
      },
      body: JSON.stringify({
        sessionId: window.currentAgentSession,
        message: message
      })
    })
    .then(function(response) {
      if (!response.ok) {
        if (response.status === 401) {
          throw new Error('Authentication required. Please log in again.');
        } else if (response.status === 400) {
          return response.json().then(function(data) {
            throw new Error(data.message || 'Invalid request');
          });
        } else {
          throw new Error('Server error: ' + response.status);
        }
      }
      return response.json();
    })
    .then(function(result) {
      addMessage('user', message);
      agentInput.value = '';

      if (result.success) {
        addMessage('agent', result.data.message);

        if (result.data.refinedPrompt) {
          showRefinedPrompt(result.data.refinedPrompt);
        }

        // Auto-scroll to show the latest content
        scrollToLatestContent();

        if (result.data.creditsUsed) {
          updateCreditDisplays(-result.data.creditsUsed);
        }

      } else {
        throw new Error(result.message || 'Failed to send message');
      }
    })
    .catch(function(error) {
      console.error('Failed to send message:', error);
      addMessage('system', '❌ Error: ' + error.message);
      scrollToLatestContent();
    })
    .finally(function() {
      agentInput.disabled = false;
      sendAgentMessage.disabled = false;
      agentInput.focus();
    });
  }

  function showRefinedPrompt(prompt) {
    var agentRefinedPrompt = document.getElementById('agent-refined-prompt');
    var refinedPromptText = document.getElementById('refined-prompt-text');
    
    if (!agentRefinedPrompt || !refinedPromptText) return;

    refinedPromptText.textContent = prompt;
    agentRefinedPrompt.classList.remove('hidden');
    
    // Scroll to show the refined prompt
    requestAnimationFrame(function() {
      scrollToLatestContent();
    });
  }

  function useRefinedPrompt() {
    console.log('useRefinedPrompt called - transferring prompt to main input');

    // Get the refined prompt text
    var refinedPromptText = document.getElementById('refined-prompt-text');
    if (!refinedPromptText) {
      console.error('Could not find refined prompt text element');
      closeAgentModal();
      return;
    }

    var prompt = refinedPromptText.textContent;
    console.log('Refined prompt to transfer:', prompt);

    // Set it in the main prompt input
    var mainPromptInput = document.getElementById('prompt');
    if (mainPromptInput) {
      mainPromptInput.value = prompt;
      console.log('Successfully transferred prompt to main input');
    } else {
      console.error('Could not find main prompt input element');
    }

    // Close the modal
    closeAgentModal();
  }

  function startSessionTimer() {
    if (sessionTimer) clearInterval(sessionTimer);

    sessionTimer = setInterval(function() {
      if (!sessionExpiryTime) return;

      var now = Date.now();
      var remaining = Math.max(0, sessionExpiryTime - now);
      var minutes = Math.floor(remaining / 60000);
      var seconds = Math.floor((remaining % 60000) / 1000);

      var sessionTimerDisplay = document.getElementById('agent-session-timer');
      if (sessionTimerDisplay) {
        sessionTimerDisplay.textContent = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
      }

      if (remaining <= 0) {
        clearInterval(sessionTimer);
        sessionTimer = null;
        addMessage('system', '⏰ Session expired. Please start a new session.');
        scrollToLatestContent();
        setTimeout(function() { closeAgentModal(); }, 3000);
      }
    }, 1000);
  }

  function updateCreditDisplays(creditValue) {
    // Update all credit displays that contain only numbers
    var allElements = document.querySelectorAll('*');
    allElements.forEach(function(element) {
      // Check if element contains only digits and has text-yellow-300 class
      if (element.textContent.match(/^\d+$/) && element.classList.contains('text-yellow-300')) {
        // Always treat creditValue as the new absolute credits value
        element.textContent = Math.max(0, creditValue);
      }
      // Also check font-bold elements (generate page)
      else if (element.textContent.match(/^\d+$/) && element.classList.contains('font-bold')) {
        // Always treat creditValue as the new absolute credits value
        element.textContent = Math.max(0, creditValue);
      }
    });
  }

  // Agent modal functions
  function showAgentModal() {
    var modal = document.getElementById('prompt-agent-modal');
    if (modal) {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeAgentModal() {
    var modal = document.getElementById('prompt-agent-modal');
    if (modal) {
      modal.classList.add('hidden');
      document.body.style.overflow = 'auto';
    }
    
    // Clear session timer
    if (sessionTimer) {
      clearInterval(sessionTimer);
      sessionTimer = null;
    }
    
    // Clear session data
    sessionExpiryTime = null;
    window.currentAgentSession = null;
  }

  function addMessage(type, content) {
    var messagesContainer = document.getElementById('agent-messages');
    if (!messagesContainer) return;

    var messageDiv = document.createElement('div');
    messageDiv.className = (type === 'user' ? 'text-right' : 'text-left') + ' mb-4';

    var messageContent = document.createElement('div');
    messageContent.className = 'inline-block max-w-xs lg:max-w-md px-4 py-2 rounded-lg ' + 
      (type === 'user' ? 'bg-purple-600 text-white' : 
       type === 'agent' ? 'bg-gray-700 text-gray-300' : 
       'bg-red-600 text-white');

    messageContent.textContent = content;
    messageDiv.appendChild(messageContent);
    messagesContainer.appendChild(messageDiv);

    // Auto-scroll to bottom using requestAnimationFrame
    requestAnimationFrame(function() {
      var scrollHeight = messagesContainer.scrollHeight;
      var currentScroll = messagesContainer.scrollTop;
      console.log('Auto-scrolling after adding message. Type:', type, 'Scroll height:', scrollHeight, 'Current scroll:', currentScroll);
      // Try scrolling to a large number to ensure it goes to bottom
      messagesContainer.scrollTop = 999999;
    });
  }

  function scrollToLatestContent() {
    var messagesContainer = document.getElementById('agent-messages');
    if (messagesContainer) {
      // Use requestAnimationFrame for smoother scrolling
      requestAnimationFrame(function() {
        var scrollHeight = messagesContainer.scrollHeight;
        var currentScroll = messagesContainer.scrollTop;
        console.log('Scrolling to latest content. Scroll height:', scrollHeight, 'Current scroll:', currentScroll);
        // Try scrolling to a large number to ensure it goes to bottom
        messagesContainer.scrollTop = 999999;
      });
    }
  }

  // Agent modal events
  const enhanceBtn = document.getElementById('enhance-btn');
  if (enhanceBtn) {
    enhanceBtn.addEventListener('click', function() {
      console.log('Enhance button clicked');
      const prompt = document.getElementById('prompt');
      if (!prompt || !prompt.value.trim()) {
        alert('Please enter a prompt first');
        return;
      }
      console.log('Starting agent session with prompt:', prompt.value.trim());
      startAgentSession(prompt.value.trim());
    });
  }

  const closeBtn = document.getElementById('close-agent-modal');
  if (closeBtn) {
    closeBtn.addEventListener('click', closeAgentModal);
  }

  const sendBtn = document.getElementById('send-agent-message');
  if (sendBtn) {
    sendBtn.addEventListener('click', sendAgentMessage);
  }

  const input = document.getElementById('agent-input');
  if (input) {
    input.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        sendAgentMessage();
      }
    });
  }

  const useBtn = document.getElementById('use-refined-prompt');
  if (useBtn) {
    console.log('Found use-refined-prompt button, attaching event listener');
    console.log('Button disabled state:', useBtn.disabled);
    console.log('Button classes:', useBtn.className);
    useBtn.addEventListener('click', function(e) {
      console.log('Button clicked!');
      useRefinedPrompt();
    });
  } else {
    console.log('Could not find use-refined-prompt button');
  }

  // Close modal when clicking outside
  const modal = document.getElementById('prompt-agent-modal');
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeAgentModal();
      }
    });
  }
});

// Log initial credit values on page load
console.log('=== PAGE LOAD CREDIT CHECK ===');
document.querySelectorAll('.font-bold').forEach(function(el, index) {
  if (el.textContent.match(/^\d+$/) && el.nextElementSibling && el.nextElementSibling.textContent.trim() === 'credits') {
    console.log('Initial generate page credits (element', index, '):', el.textContent);
  }
});
document.querySelectorAll('.text-yellow-300.font-semibold').forEach(function(el, index) {
  if (el.textContent.match(/^\d+$/)) {
    console.log('Initial header credits (element', index, '):', el.textContent);
  }
});
document.querySelectorAll('.text-2xl.font-bold.text-yellow-300').forEach(function(el, index) {
  if (el.textContent.match(/^\d+$/)) {
    console.log('Initial profile credits (element', index, '):', el.textContent);
  }
});
console.log('=== PAGE LOAD CREDIT CHECK COMPLETE ===');

</script>

<?php include __DIR__ . '/agent_modal.php'; ?>

</body>
</html>
