<?php
// Generate page - matches the functionality of the picfe app
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Initialize variables with defaults
$user = isset($user) ? $user : (isset($_SESSION['user']) ? $_SESSION['user'] : null);
$settings = isset($settings) && is_array($settings) ? $settings : [];
$recentImages = isset($recentImages) ? $recentImages : [];

$displayCredits = $user['credits'] ?? ($_SESSION['user']['credits'] ?? 0);
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

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left side: Input form -->
    <div class="lg:col-span-1">
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
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
              rows="4"
              class="w-full p-3 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white resize-y min-h-[100px]"
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
              <span id="enhance-text">Enhance Prompt</span>
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

          <!-- Enhanced Prompts -->
          <div id="enhanced-prompts" class="hidden">
            <div class="flex justify-between items-center mb-2">
              <label for="enhanced-prompts-textarea" class="block text-sm font-medium text-gray-300">Enhanced Prompts</label>
              <button type="button" id="close-enhanced-prompts" class="text-gray-400 hover:text-white text-sm" title="Close enhanced prompts">✕</button>
            </div>
            <textarea
              id="enhanced-prompts-textarea"
              rows="6"
              class="w-full p-3 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white resize-y min-h-[120px]"
              placeholder="Enhanced prompts will appear here..."
              readonly
            ></textarea>
            <div class="text-xs text-gray-500 mt-1">Click on any prompt line to use it, or resize this box to see more content</div>
          </div>

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
    <div class="lg:col-span-2">
      <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
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
</div>

<script>
// Wrap all JavaScript in DOM ready event
document.addEventListener('DOMContentLoaded', function() {
  // Toggle guidance function
  window.toggleGuidance = function() {
    const content = document.getElementById('guidance-content');
    const chevron = document.getElementById('guidance-chevron');
    if (content.classList.contains('hidden')) {
      content.classList.remove('hidden');
      chevron.style.transform = 'rotate(0deg)';
    } else {
      content.classList.add('hidden');
      chevron.style.transform = 'rotate(180deg)';
    }
  };

  // Image upload handling
  const image1Input = document.getElementById('image1');
  const image2Input = document.getElementById('image2');
  
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
  const removeImage1Btn = document.getElementById('remove-image1');
  const removeImage2Btn = document.getElementById('remove-image2');
  
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
  const acceptPermissionBtn = document.getElementById('accept-permission');
  const declinePermissionBtn = document.getElementById('decline-permission');
  const closePermissionModalBtn = document.getElementById('close-permission-modal');
  
  if (acceptPermissionBtn) {
    acceptPermissionBtn.addEventListener('click', function() {
      const usagePermission = document.getElementById('usage-permission');
      if (usagePermission) usagePermission.checked = true;
      const permissionModal = document.getElementById('permission-modal');
      if (permissionModal) permissionModal.classList.add('hidden');
    });
  }
  
  if (declinePermissionBtn) {
    declinePermissionBtn.addEventListener('click', function() {
      const usagePermission = document.getElementById('usage-permission');
      if (usagePermission) usagePermission.checked = false;
      const permissionModal = document.getElementById('permission-modal');
      if (permissionModal) permissionModal.classList.add('hidden');
    });
  }
  
  if (closePermissionModalBtn) {
    closePermissionModalBtn.addEventListener('click', function() {
      const permissionModal = document.getElementById('permission-modal');
      if (permissionModal) permissionModal.classList.add('hidden');
    });
  }

  // Close enhanced prompts button
  const closeEnhancedPromptsBtn = document.getElementById('close-enhanced-prompts');
  if (closeEnhancedPromptsBtn) {
    closeEnhancedPromptsBtn.addEventListener('click', function() {
      const enhancedPrompts = document.getElementById('enhanced-prompts');
      if (enhancedPrompts) enhancedPrompts.classList.add('hidden');
    });
  }

  // Enhance prompt functionality
  const enhanceBtn = document.getElementById('enhance-btn');
  if (enhanceBtn) {
    enhanceBtn.addEventListener('click', async function() {
      const prompt = document.getElementById('prompt');
      if (!prompt || !prompt.value.trim()) {
        alert('Please enter a prompt first');
        return;
      }

      const btn = this;
      const text = document.getElementById('enhance-text');
      const spinner = document.getElementById('enhance-spinner');

      if (btn) btn.disabled = true;
      if (text) text.textContent = 'Enhancing...';
      if (spinner) spinner.classList.remove('hidden');

      try {
        console.log('Sending enhance request for prompt:', prompt.value.trim());
        
        // Get fresh CSRF token from meta tag
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
          throw new Error('CSRF token not found. Please refresh the page.');
        }
        const csrfToken = csrfMeta.getAttribute('content');
        if (!csrfToken) {
          throw new Error('Invalid CSRF token. Please refresh the page.');
        }
        console.log('Using CSRF token:', csrfToken.substring(0, 10) + '...');
        
        const response = await fetch('/api/enhance', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
          },
          credentials: 'same-origin',
          body: JSON.stringify({ prompt: prompt.value.trim() })
        });

        console.log('Enhance response status:', response.status);
        console.log('Enhance response headers:', Object.fromEntries(response.headers.entries()));

        if (!response.ok) {
          const errorText = await response.text();
          console.error('Enhance API error response:', errorText);
          console.error('CSRF token used:', csrfToken);
          
          // Check if it's a CSRF token error
          if (errorText.includes('Invalid request')) {
            console.log('CSRF token error detected, attempting to refresh page...');
            alert('Session expired. Refreshing page to continue...');
            window.location.reload();
            return;
          }
          
          throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const result = await response.json();
        console.log('Enhance API success response:', result);

        if (result.success) {
          displayEnhancedPrompts(result.data.enhancedPrompts);
          const enhancedPrompts = document.getElementById('enhanced-prompts');
          if (enhancedPrompts) enhancedPrompts.classList.remove('hidden');

          // Update credit displays if credits were updated
          if (result.data.updatedCredits !== undefined) {
            updateCreditDisplays(result.data.updatedCredits);
          }
        } else {
          alert(result.message || 'Failed to enhance prompt');
        }
      } catch (error) {
        console.error('Error enhancing prompt:', error);
        console.error('Error details:', {
          message: error.message,
          stack: error.stack,
          name: error.name
        });
        
        // Provide more specific error message for JSON parsing errors
        if (error.message.includes('JSON') || error.message.includes('parsing')) {
          alert('Error parsing enhancement response. The AI service returned invalid data. Please try again.');
        } else {
          alert('Error connecting to enhancement service: ' + error.message);
        }
      } finally {
        if (btn) btn.disabled = false;
        if (text) text.textContent = 'Enhance Prompt';
        if (spinner) spinner.classList.add('hidden');
      }
    });
  }

  // Form submission handling
  const generateForm = document.getElementById('generate-form');
  if (generateForm) {
    generateForm.addEventListener('submit', function(e) {
      const image1 = document.getElementById('image1');
      const image2 = document.getElementById('image2');
      const hasImages = (image1 && image1.files[0]) || (image2 && image2.files[0]);
      const usagePermission = document.getElementById('usage-permission');
      const permissionChecked = usagePermission ? usagePermission.checked : false;

      console.log('Form submission - Images detected:', {
        image1: image1 && image1.files[0] ? `${image1.files[0].name} (${(image1.files[0].size / 1024 / 1024).toFixed(2)}MB)` : 'none',
        image2: image2 && image2.files[0] ? `${image2.files[0].name} (${(image2.files[0].size / 1024 / 1024).toFixed(2)}MB)` : 'none',
        hasImages: hasImages,
        permissionChecked: permissionChecked
      });

      if (hasImages && !permissionChecked) {
        e.preventDefault();
        const permissionModal = document.getElementById('permission-modal');
        if (permissionModal) permissionModal.classList.remove('hidden');
        return;
      }

      // Check file sizes before submission
      if (hasImages) {
        const maxSize = 8 * 1024 * 1024; // 8MB (increased from 2MB)
        let tooLarge = false;

        if (image1 && image1.files[0] && image1.files[0].size > maxSize) {
          tooLarge = true;
          console.error(`Image 1 too large: ${(image1.files[0].size / 1024 / 1024).toFixed(2)}MB > 8MB`);
        }
        if (image2 && image2.files[0] && image2.files[0].size > maxSize) {
          tooLarge = true;
          console.error(`Image 2 too large: ${(image2.files[0].size / 1024 / 1024).toFixed(2)}MB > 8MB`);
        }

        if (tooLarge) {
          e.preventDefault();
          alert('One or more images are too large. Maximum size is 8MB per image. Please try smaller images or different formats.');
          return;
        }
      }

      // Show loading state
      const btn = document.getElementById('generate-btn');
      const text = document.getElementById('generate-text');
      const spinner = document.getElementById('generate-spinner');

      if (btn) btn.disabled = true;
      if (text) text.textContent = 'Generating...';
      if (spinner) spinner.classList.remove('hidden');
    });
  }
});

function handleImageUpload(input, index) {
  const file = input.files[0];
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
      console.log(`Compressing image ${index} (${(file.size / 1024 / 1024).toFixed(2)}MB)...`);
      showCompressionStatus(index, true);
      compressImage(file, index, input);
    } else {
      // File is small enough, proceed normally
      displayImagePreview(file, index);
    }
  }
}

function showCompressionStatus(index, show) {
  const placeholder = document.getElementById(`image${index}-placeholder`);
  if (placeholder) {
    if (show) {
      placeholder.innerHTML = `
        <div class="text-center">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-500 mx-auto mb-2"></div>
          <div class="text-xs">Compressing...</div>
        </div>
      `;
    } else {
      placeholder.innerHTML = `
        <svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <div class="text-sm">Upload Image ${index}</div>
      `;
    }
  }
}

function compressImage(file, index, input) {
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  const img = new Image();

  img.onload = function() {
    try {
      // Calculate new dimensions (max 1920px on longest side)
      const maxDimension = 1920;
      let { width, height } = img;

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
          const compressedFile = new File([blob], file.name, {
            type: file.type,
            lastModified: Date.now()
          });

          console.log(`Image ${index} compressed: ${(file.size / 1024 / 1024).toFixed(2)}MB → ${(compressedFile.size / 1024 / 1024).toFixed(2)}MB`);

          // Replace the original file with the compressed one
          const dataTransfer = new DataTransfer();
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
  const reader = new FileReader();
  reader.onload = function(e) {
    const preview = document.getElementById(`image${index}-preview`);
    const img = document.getElementById(`image${index}-img`);
    const placeholder = document.getElementById(`image${index}-placeholder`);
    const removeBtn = document.getElementById(`remove-image${index}`);

    if (img && e.target && e.target.result) img.src = e.target.result;
    if (preview) preview.classList.remove('hidden');
    if (placeholder) placeholder.classList.add('hidden');
    if (removeBtn) removeBtn.classList.remove('hidden');

    // Show permission section
    const permissionSection = document.getElementById('permission-section');
    if (permissionSection) permissionSection.classList.remove('hidden');

    console.log(`Image ${index} ready for upload: ${(file.size / 1024 / 1024).toFixed(2)}MB`);
  };
  reader.readAsDataURL(file);
}

function removeImage(index) {
  const input = document.getElementById(`image${index}`);
  const preview = document.getElementById(`image${index}-preview`);
  const placeholder = document.getElementById(`image${index}-placeholder`);
  const removeBtn = document.getElementById(`remove-image${index}`);

  if (input) input.value = '';
  if (preview) preview.classList.add('hidden');
  if (placeholder) placeholder.classList.remove('hidden');
  if (removeBtn) removeBtn.classList.add('hidden');

  // Hide permission section if no images
  const image1 = document.getElementById('image1');
  const image2 = document.getElementById('image2');
  const hasImage1 = image1 && image1.files[0];
  const hasImage2 = image2 && image2.files[0];
  
  if (!hasImage1 && !hasImage2) {
    const permissionSection = document.getElementById('permission-section');
    if (permissionSection) permissionSection.classList.add('hidden');
  }
}

function displayEnhancedPrompts(prompts) {
  const textarea = document.getElementById('enhanced-prompts-textarea');
  if (!textarea) return;
  
  // Format prompts with numbers and clear separation
  const formattedPrompts = prompts.map((prompt, index) => 
    `${index + 1}. ${prompt}`
  ).join('\n\n');
  
  textarea.value = formattedPrompts;
  
  // Remove any existing click event listeners
  const newTextarea = textarea.cloneNode(true);
  newTextarea.value = formattedPrompts;
  newTextarea.id = 'enhanced-prompts-textarea'; // Ensure ID is preserved
  textarea.parentNode.replaceChild(newTextarea, textarea);
  
  // Make the new textarea clickable to select prompts
  newTextarea.addEventListener('click', function(e) {
    // Get the clicked line by finding the line at the cursor position
    const cursorPosition = this.selectionStart;
    const textBeforeCursor = this.value.substring(0, cursorPosition);
    const lines = this.value.split('\n\n');
    
    // Count how many line breaks are before the cursor
    const lineBreaksBeforeCursor = (textBeforeCursor.match(/\n\n/g) || []).length;
    const clickedLine = lineBreaksBeforeCursor;
    
    if (clickedLine >= 0 && clickedLine < lines.length) {
      const selectedPrompt = lines[clickedLine].replace(/^\d+\.\s*/, '');
      const promptTextarea = document.getElementById('prompt');
      if (promptTextarea) {
        promptTextarea.value = selectedPrompt;
        promptTextarea.focus(); // Focus on the main prompt textarea
        console.log('Selected prompt:', selectedPrompt);
        // Keep the enhanced prompts box visible for reference
      }
    }
  });
}

// Function to update all credit displays on the page
function updateCreditDisplays(newCredits) {
  // Update credit display in generate page header
  const generateCreditElements = document.querySelectorAll('.font-bold');
  generateCreditElements.forEach(element => {
    if (element.textContent.match(/^\d+$/) && element.nextElementSibling && element.nextElementSibling.textContent === 'credits') {
      element.textContent = newCredits;
    }
  });

  // Update credit display in main header (if visible)
  const headerCreditElements = document.querySelectorAll('.text-yellow-300.font-semibold');
  headerCreditElements.forEach(element => {
    if (element.textContent.match(/^\d+$/)) {
      element.textContent = newCredits;
    }
  });

  console.log('Credits updated to:', newCredits);
}

// Update credits on page load if we have a success message (indicating recent transaction)
document.addEventListener('DOMContentLoaded', function() {
  const successMessage = document.querySelector('.bg-green-900\\/20');
  if (successMessage && successMessage.textContent.includes('generated successfully')) {
    // Fetch current credits from server
    fetch('/api/user/credits', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
    .then(response => {
      console.log('Credits response status:', response.status);
      console.log('Credits response headers:', Object.fromEntries(response.headers.entries()));
      return response.text();
    })
    .then(text => {
      console.log('Credits response text:', text);
      try {
        const data = JSON.parse(text);
        if (data.success && data.credits !== undefined) {
          updateCreditDisplays(data.credits);
        }
      } catch (e) {
        console.error('JSON parse error:', e);
        console.error('Response text:', text);
      }
    })
    .catch(error => {
      console.log('Could not fetch updated credits:', error);
    });
  }
});
</script>
