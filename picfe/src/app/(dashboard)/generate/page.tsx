'use client';

import React, { useState, useRef, useEffect } from 'react';
import { useAuth } from '@/lib/auth-context';
import { promptsAPI, imagesAPI, userAPI, publicSettingsAPI } from '@/lib/api';
import { toast } from 'react-toastify';
import { motion } from 'framer-motion';
import { Sparkles, Upload, ArrowDown, RefreshCw, Download, Check, Image as ImageIcon } from 'lucide-react';
import PermissionModal from '@/components/PermissionModal';
import CreditStatus from '@/components/CreditStatus';

// Define types for better type safety
interface UploadedImage {
  file: File;
  preview: string;
  name: string;
}

interface GeneratedImage {
  imageUrl: string;
  prompt: string;
  id?: string;
}

export default function Generate() {
  const { updateUserCredits, user } = useAuth();
  const [uploadedImages, setUploadedImages] = useState<(UploadedImage | null)[]>([null, null]);
  const [prompt, setPrompt] = useState('');
  const [isGenerating, setIsGenerating] = useState(false);
  const [generatedImage, setGeneratedImage] = useState<GeneratedImage | null>(null);
  const [error, setError] = useState('');
  const [enhancedPrompts, setEnhancedPrompts] = useState<string[]>([]);
  const [isEnhancing, setIsEnhancing] = useState(false);
  const [showAllPrompts, setShowAllPrompts] = useState(false);
  const [selectedPrompt, setSelectedPrompt] = useState<string | null>(null);
  const [imagePermission, setImagePermission] = useState(false);
  const [showPermissionModal, setShowPermissionModal] = useState(false);
  const [hasCheckedPermission, setHasCheckedPermission] = useState(false);
  const [globalPermissionStatus, setGlobalPermissionStatus] = useState(false);
  const [isLoadingPermission, setIsLoadingPermission] = useState(true);
  const [creditCostPerImage, setCreditCostPerImage] = useState(10);
  const [enhancedPromptCost, setEnhancedPromptCost] = useState(0);
  const [isLoadingSettings, setIsLoadingSettings] = useState(true);
  const fileInputRefs = [
    useRef<HTMLInputElement>(null), 
    useRef<HTMLInputElement>(null)
  ];
  
      // Fetch system settings
  useEffect(() => {
    const fetchSettings = async () => {
      try {
        setIsLoadingSettings(true);
        const response = await publicSettingsAPI.getSettings();
        
        if (response.data.success && response.data.data) {
          const settings = response.data.data.settings;
          console.log('Settings loaded successfully:', settings);
          setCreditCostPerImage(settings.creditCostPerImage);
          setEnhancedPromptCost(settings.enhancedPromptCost);
        } else {
          console.error('Failed to load settings:', response.data?.message);
        }
      } catch (error: any) {
        console.error('Error loading settings:', error);
        
        // Check if this is a rate limit error
        if (error.response?.status === 429) {
          console.log('Rate limit exceeded for settings, will retry later');
          // Could add retry logic here if needed
        }
      } finally {
        setIsLoadingSettings(false);
      }
    };
    
    fetchSettings();
  }, []);

  // Check if the user has already accepted the image usage permission
  useEffect(() => {
    const checkImagePermission = async () => {
      if (!user) return;
      
      try {
        setIsLoadingPermission(true);
        const response = await userAPI.checkPermission('image_usage');
        
        if (response.data.success) {
          const hasPermission = response.data.data?.hasPermission || false;
          setGlobalPermissionStatus(hasPermission);
          setImagePermission(hasPermission);
          
          // If user hasn't accepted permission yet, show the modal
          if (!hasPermission && !hasCheckedPermission) {
            setShowPermissionModal(true);
          }
        }
      } catch (err) {
        console.error('Error checking image permission:', err);
      } finally {
        setHasCheckedPermission(true);
        setIsLoadingPermission(false);
      }
    };
    
    checkImagePermission();
  }, [user]);
  
  // Handle permission modal acceptance
  const handleAcceptPermission = async () => {
    try {
      const response = await userAPI.updatePermission('image_usage', true);
      
      if (response.data.success) {
        setGlobalPermissionStatus(true);
        setImagePermission(true);
        toast.success('Thank you for accepting the image usage terms');
      }
    } catch (err) {
      console.error('Error updating permission:', err);
      toast.error('Failed to update permission status');
    } finally {
      setShowPermissionModal(false);
    }
  };
  
  // Handle permission modal decline
  const handleDeclinePermission = async () => {
    try {
      await userAPI.updatePermission('image_usage', false);
      setGlobalPermissionStatus(false);
      setImagePermission(false);
      toast.info('You declined the image usage terms. You can still generate images without uploading reference images.');
    } catch (err) {
      console.error('Error updating permission:', err);
    } finally {
      setShowPermissionModal(false);
    }
  };

  const handleImageUpload = (event: React.ChangeEvent<HTMLInputElement>, index: number) => {
    const file = event.target.files?.[0];
    if (file) {
      // If user hasn't accepted image usage terms, prompt them first
      if (!globalPermissionStatus) {
        setShowPermissionModal(true);
        
        // Reset the file input
        if (fileInputRefs[index] && fileInputRefs[index].current) {
          fileInputRefs[index].current.value = '';
        }
        return;
      }
      
      // Validate file type
      if (!file.type.startsWith('image/')) {
        setError('Please select a valid image file');
        return;
      }

      // Validate file size (max 10MB)
      if (file.size > 10 * 1024 * 1024) {
        setError('Image size should be less than 10MB');
        return;
      }

      const reader = new FileReader();
      reader.onload = (e: ProgressEvent<FileReader>) => {
        if (e.target && e.target.result) {
          const newImages = [...uploadedImages];
          newImages[index] = {
            file: file,
            preview: e.target.result.toString(),
            name: file.name
          };
          setUploadedImages(newImages);
          setError('');
        }
      };
      reader.readAsDataURL(file);
    }
  };

  const removeImage = (index: number) => {
    const newImages = [...uploadedImages];
    newImages[index] = null;
    setUploadedImages(newImages);
    
    // Reset file input
    if (fileInputRefs[index] && fileInputRefs[index].current) {
      fileInputRefs[index].current.value = '';
    }
  };

  const handleEnhancePrompt = async () => {
    if (!prompt.trim()) {
      setError('Please enter a prompt to enhance');
      return;
    }

    // Check if user has enough credits for enhanced prompts
    if (enhancedPromptCost > 0 && user?.credits && user.credits < enhancedPromptCost) {
      setError(`Insufficient credits for prompt enhancement. You need ${enhancedPromptCost} credits.`);
      return;
    }

    setIsEnhancing(true);
    setError('');
    setEnhancedPrompts([]);

    try {
      const response = await promptsAPI.enhance(prompt);
      
      if (response.data.success) {
        setEnhancedPrompts(response.data.data?.enhancedPrompts || []);
        
        // Update user credits if cost is applied
        if (enhancedPromptCost > 0 && user?.credits) {
          updateUserCredits(user.credits - enhancedPromptCost);
        }
        
        // Show appropriate success message
        if (response.data.data?.fallback) {
          toast.info(response.data.data.message || 'Using fallback prompts due to service unavailability');
        } else {
          toast.success('Prompt enhanced successfully!');
        }
      } else {
        if (response.data.message && response.data.message.includes('Insufficient credits')) {
          setError(`Insufficient credits for prompt enhancement. You need ${enhancedPromptCost} credits.`);
        } else {
          setError('Failed to enhance prompt');
        }
      }
    } catch (err: any) {
      console.error('Error enhancing prompt:', err);
      if (err.response?.data?.message?.includes('Insufficient credits')) {
        setError(`Insufficient credits for prompt enhancement. You need ${enhancedPromptCost} credits.`);
      } else {
        setError('Error connecting to enhancement service');
      }
    } finally {
      setIsEnhancing(false);
    }
  };

  const selectPrompt = (enhancedPrompt: string) => {
    setPrompt(enhancedPrompt);
    setSelectedPrompt(enhancedPrompt);
    setShowAllPrompts(false);
  };

  const handleGenerateImage = async () => {
    if (!prompt.trim()) {
      setError('Please enter a prompt');
      return;
    }
    
    // Check if permission is required (when images are uploaded)
    const hasImages = uploadedImages.some(img => img !== null);
    if (hasImages && !imagePermission) {
      setError('Please confirm that you have permission to use these images');
      return;
    }
    
    // Check if user has enough credits
    if (user?.credits && user.credits < creditCostPerImage) {
      setError(`Insufficient credits for image generation. You need ${creditCostPerImage} credits.`);
      return;
    }

    setIsGenerating(true);
    setError('');
    setGeneratedImage(null);

    try {
      const formData = new FormData();
      formData.append('prompt', prompt);
      
      // Add reference images if available
      uploadedImages.forEach((img, index) => {
        if (img && img.file) {
          formData.append(`image${index + 1}`, img.file);
        }
      });
      
      // Add permission confirmation if images are uploaded
      if (hasImages) {
        formData.append('hasUsagePermission', String(imagePermission));
      }

      // Using the API utility which already handles headers and error handling
      const response = await imagesAPI.generateImage(formData);
      const data = response.data;
      
      if (data.success) {
        // Handle different response structures safely
        const imageData = data.data?.image || (data as any).image;
        
        // Ensure we have the correct image URL field
        const processedImageData = {
          ...imageData,
          imageUrl: imageData?.image_url || imageData?.imageUrl || imageData?.url
        };
        
        setGeneratedImage(processedImageData);
        
        // Update user credits from the response - handle various response structures
        const remainingCredits = data.data?.creditsRemaining || 
                              (data.data as any)?.remainingCredits || 
                              (data as any).remainingCredits;
                              
        if (remainingCredits !== undefined) {
          updateUserCredits(remainingCredits);
        }
        toast.success('Image generated successfully!');
      } else {
        setError(data.message || 'Failed to generate image');
      }
    } catch (err: any) {
      // Log error details for debugging (only in development)
      if (process.env.NODE_ENV === 'development') {
        console.error('Error generating image:', err);
      }
      
      // Check for specific error responses
      if (err.response?.status === 403 && err.response?.data?.message?.includes('Insufficient credits')) {
        setError(`Insufficient credits for image generation. You need ${creditCostPerImage} credits.`);
      } else if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else if (err.response?.status === 500) {
        setError('Failed to generate image. Please try again later.');
      } else {
        setError('Error connecting to generation service');
      }
    } finally {
      setIsGenerating(false);
    }
  };

  const downloadImage = () => {
    if (!generatedImage || !generatedImage.imageUrl) return;
    
    const link = document.createElement('a');
    link.href = generatedImage.imageUrl;
    link.download = `generated_${Date.now()}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="max-w-6xl mx-auto">
      {/* Permission Modal */}
      <PermissionModal
        isOpen={showPermissionModal}
        onClose={() => setShowPermissionModal(false)}
        onAccept={handleAcceptPermission}
        onDecline={handleDeclinePermission}
        title="Image Usage Permission"
        description="Before you upload images to our service, we need to confirm that you have the necessary rights and permissions to use these images."
        permissionType="image_usage"
      />
      
      <div className="flex flex-col lg:flex-row gap-8">
        {/* Left side: Input form */}
        <div className="w-full lg:w-1/2">
          <div className="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-2xl font-bold">Create an Image</h2>
              {user && !isLoadingSettings && (
                <div className="flex items-center gap-4">
                  <div className="px-3 py-1.5 bg-gray-700 rounded-md border border-gray-600">
                    <CreditStatus 
                      credits={user.credits || 0} 
                      creditCost={creditCostPerImage} 
                    />
                  </div>
                </div>
              )}
            </div>
            
            {error && (
              <div className="mb-6 p-4 bg-red-900/20 border border-red-600 text-red-200 rounded-md text-sm">
                {error}
              </div>
            )}
            
            {/* Text Prompt */}
            <div className="mb-6">
              <label htmlFor="prompt" className="block text-sm font-medium text-gray-300 mb-2">
                Text Prompt
              </label>
              <textarea
                id="prompt"
                value={prompt}
                onChange={(e) => setPrompt(e.target.value)}
                placeholder="Describe the image you want to generate..."
                className="w-full p-3 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white"
                rows={4}
              ></textarea>
              
              {/* Enhance Prompt Button */}
              <button
                onClick={handleEnhancePrompt}
                disabled={isEnhancing || !prompt.trim()}
                className={`mt-2 flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-md text-white text-sm font-medium transition-colors ${
                  isEnhancing || !prompt.trim() ? 'opacity-50 cursor-not-allowed' : ''
                }`}
              >
                {isEnhancing ? (
                  <>
                    <RefreshCw size={16} className="mr-2 animate-spin" /> Enhancing...
                  </>
                ) : (
                  <>
                    <Sparkles size={16} className="mr-2" /> Enhance Prompt
                    {enhancedPromptCost > 0 && (
                      <span className="ml-2 text-xs bg-purple-700 px-2 py-0.5 rounded">
                        {enhancedPromptCost} credits
                      </span>
                    )}
                  </>
                )}
              </button>
            </div>
            
            {/* Enhanced Prompts */}
            {enhancedPrompts.length > 0 && (
              <div className="mb-6 bg-gray-700/50 rounded-md p-4 border border-gray-600">
                <div className="flex justify-between items-center mb-3">
                  <h3 className="font-medium text-purple-400">Enhanced Prompts</h3>
                  <button
                    onClick={() => setShowAllPrompts(!showAllPrompts)}
                    className="text-sm text-gray-400 hover:text-white"
                  >
                    {showAllPrompts ? 'Show Less' : 'Show All'}
                  </button>
                </div>
                
                <div className="space-y-3">
                  {(showAllPrompts ? enhancedPrompts : enhancedPrompts.slice(0, 2)).map((enhancedPrompt, index) => (
                    <div 
                      key={index}
                      onClick={() => selectPrompt(enhancedPrompt)}
                      className={`p-3 rounded-md cursor-pointer transition-all ${
                        selectedPrompt === enhancedPrompt 
                          ? 'bg-purple-600/30 border border-purple-500' 
                          : 'bg-gray-700 hover:bg-gray-600 border border-gray-600'
                      }`}
                    >
                      <div className="flex items-center justify-between">
                        <span className="text-xs text-gray-400">Option {index + 1}</span>
                        {selectedPrompt === enhancedPrompt && (
                          <Check size={16} className="text-purple-400" />
                        )}
                      </div>
                      <p className="text-sm mt-1 line-clamp-2">
                        {enhancedPrompt}
                      </p>
                    </div>
                  ))}
                  
                  {!showAllPrompts && enhancedPrompts.length > 2 && (
                    <button 
                      onClick={() => setShowAllPrompts(true)}
                      className="flex items-center justify-center w-full p-2 bg-gray-700 hover:bg-gray-600 rounded-md text-sm text-gray-300"
                    >
                      <ArrowDown size={16} className="mr-2" /> Show {enhancedPrompts.length - 2} more
                    </button>
                  )}
                </div>
              </div>
            )}
            
            {/* Reference Images */}
            <div className="mb-6">
              <h3 className="text-sm font-medium text-gray-300 mb-3">Reference Images (Optional)</h3>
              <div className="grid grid-cols-2 gap-4">
                {[0, 1].map((index) => (
                  <div key={index} className="bg-gray-700 border border-gray-600 rounded-md overflow-hidden">
                    {uploadedImages[index] ? (
                      <div className="relative">
                        <img 
                          src={uploadedImages[index].preview} 
                          alt={`Reference ${index + 1}`} 
                          className="w-full h-36 object-cover"
                        />
                        <button
                          onClick={() => removeImage(index)}
                          className="absolute top-2 right-2 bg-red-600 rounded-full p-1 hover:bg-red-700"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                        <p className="bg-gray-800 text-gray-300 p-2 text-xs truncate">
                          {uploadedImages[index].name}
                        </p>
                      </div>
                    ) : (
                      <label className="flex flex-col items-center justify-center h-36 cursor-pointer">
                        <Upload size={24} className="text-gray-400 mb-2" />
                        <span className="text-sm text-gray-400">Upload Image {index + 1}</span>
                        <input
                          type="file"
                          ref={fileInputRefs[index]}
                          onChange={(e) => handleImageUpload(e, index)}
                          accept="image/*"
                          className="hidden"
                        />
                      </label>
                    )}
                  </div>
                ))}
              </div>
              <p className="mt-2 text-xs text-gray-400">
                Upload reference images to influence the style and content of your generated image.
              </p>
              
              {/* Image Usage Permission Checkbox - only show when images are uploaded */}
              {uploadedImages.some(img => img !== null) && (
                <div className="mt-4 p-4 bg-gray-700/50 border border-gray-600 rounded-md">
                  <label className="flex items-start cursor-pointer">
                    <input
                      type="checkbox"
                      checked={imagePermission}
                      onChange={(e) => setImagePermission(e.target.checked)}
                      className="mt-1 mr-3 h-4 w-4 rounded accent-purple-500"
                    />
                    <span className="text-sm text-gray-300">
                      I confirm that I have the necessary rights, licenses, and permissions to use these images 
                      in this AI generation service. I understand that I am responsible for ensuring that 
                      my use of these images complies with applicable copyright laws and other legal requirements.
                    </span>
                  </label>
                </div>
              )}
            </div>
            
            {/* Generate Button */}
            <button
              onClick={handleGenerateImage}
              disabled={isGenerating || !prompt.trim()}
              className={`w-full flex items-center justify-center px-5 py-3 bg-gradient-to-r from-purple-600 to-pink-500 rounded-md text-white font-medium hover:opacity-90 transition-opacity ${
                isGenerating || !prompt.trim() ? 'opacity-50 cursor-not-allowed' : ''
              }`}
            >
              {isGenerating ? (
                <>
                  <RefreshCw size={18} className="mr-2 animate-spin" /> Generating...
                </>
              ) : (
                <>
                  <Sparkles size={18} className="mr-2" /> Generate Image 
                  <span className="ml-2 text-xs bg-blue-700 px-2 py-0.5 rounded">
                    {creditCostPerImage} credits
                  </span>
                </>
              )}
            </button>
            <p className="mt-2 text-center text-xs text-gray-400">
              This will consume {creditCostPerImage} credit{creditCostPerImage !== 1 ? 's' : ''} from your account
            </p>
          </div>
        </div>
        
        {/* Right side: Generated image */}
        <div className="w-full lg:w-1/2">
          <div className="bg-gray-800 rounded-xl p-6 border border-gray-700 h-full">
            <h2 className="text-2xl font-bold mb-6">Generated Image</h2>
            
            <div className="bg-gray-700 border border-gray-600 rounded-md overflow-hidden">
              {isGenerating ? (
                <div className="flex flex-col items-center justify-center h-[400px]">
                  <RefreshCw size={40} className="text-purple-500 animate-spin mb-4" />
                  <p className="text-gray-300">Generating your image...</p>
                  <p className="text-sm text-gray-400 mt-2">This may take a few seconds</p>
                </div>
              ) : generatedImage ? (
                <div className="relative">
                  <img 
                    src={generatedImage.imageUrl} 
                    alt="Generated" 
                    className="w-full object-contain max-h-[400px]"
                  />
                  <div className="absolute bottom-4 right-4">
                    <button
                      onClick={downloadImage}
                      className="flex items-center bg-gray-800 bg-opacity-75 hover:bg-opacity-100 p-2 rounded-full transition-all"
                      title="Download Image"
                    >
                      <Download size={20} className="text-white" />
                    </button>
                  </div>
                </div>
              ) : (
                <div className="flex flex-col items-center justify-center h-[400px] text-center">
                  <ImageIcon size={64} className="text-gray-600 mb-4" />
                  <p className="text-gray-300 font-medium">No image generated yet</p>
                  <p className="text-sm text-gray-400 mt-2 max-w-xs">
                    Enter a prompt and click the Generate button to create your image
                  </p>
                </div>
              )}
            </div>
            
            {generatedImage && (
              <div className="mt-4">
                <h3 className="font-medium text-gray-300 mb-2">Prompt Used:</h3>
                <p className="text-sm text-gray-400 bg-gray-700 p-3 rounded-md border border-gray-600">
                  {generatedImage.prompt}
                </p>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
