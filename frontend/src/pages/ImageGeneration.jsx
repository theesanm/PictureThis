import React, { useState, useRef } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../contexts/SimpleAuthContext'

const ImageGeneration = () => {
  const { isAuthenticated, user } = useAuth()
  const [uploadedImages, setUploadedImages] = useState([])
  const [prompt, setPrompt] = useState('')
  const [isGenerating, setIsGenerating] = useState(false)
  const [generatedImage, setGeneratedImage] = useState(null)
  const [error, setError] = useState('')
  const [enhancedPrompts, setEnhancedPrompts] = useState([])
  const [isEnhancing, setIsEnhancing] = useState(false)
  const [showAllPrompts, setShowAllPrompts] = useState(false)
  const fileInputRef1 = useRef(null)
  const fileInputRef2 = useRef(null)

  if (!isAuthenticated) {
    return (
      <div style={{ 
        padding: '20px', 
        textAlign: 'center', 
        backgroundColor: '#0f172a', 
        color: '#f8fafc', 
        minHeight: '100vh' 
      }}>
        <h1 style={{ color: '#ef4444', marginBottom: '2rem' }}>🔒 Access Denied</h1>
        <p style={{ color: '#cbd5e1', marginBottom: '2rem' }}>
          Please sign in to access the image generation feature
        </p>
        <Link to="/login" style={{ 
          backgroundColor: '#2563eb', 
          color: 'white', 
          padding: '0.75rem 1.5rem', 
          borderRadius: '0.5rem',
          textDecoration: 'none',
          fontWeight: '600'
        }}>
          Sign In
        </Link>
      </div>
    )
  }

  const handleImageUpload = (event, index) => {
    const file = event.target.files[0]
    if (file) {
      // Validate file type
      if (!file.type.startsWith('image/')) {
        setError('Please select a valid image file')
        return
      }

      // Validate file size (max 10MB)
      if (file.size > 10 * 1024 * 1024) {
        setError('Image size should be less than 10MB')
        return
      }

      const reader = new FileReader()
      reader.onload = (e) => {
        const newImages = [...uploadedImages]
        newImages[index] = {
          file: file,
          preview: e.target.result,
          name: file.name
        }
        setUploadedImages(newImages)
        setError('')
      }
      reader.readAsDataURL(file)
    }
  }

  const removeImage = (index) => {
    const newImages = [...uploadedImages]
    newImages[index] = null
    setUploadedImages(newImages)
    
    // Reset file input
    if (index === 0 && fileInputRef1.current) {
      fileInputRef1.current.value = ''
    }
    if (index === 1 && fileInputRef2.current) {
      fileInputRef2.current.value = ''
    }
  }

  const handleEnhancePrompt = async () => {
    if (!prompt.trim()) {
      setError('Please enter a prompt to enhance')
      return
    }

    setIsEnhancing(true)
    setError('')
    setEnhancedPrompts([])

    try {
      const response = await fetch('http://localhost:3011/api/prompts/enhance', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ prompt })
      })

      if (!response.ok) {
        throw new Error(`Enhancement failed: ${response.status}`)
      }

      const result = await response.json()
      setEnhancedPrompts(result.data.enhancedPrompts)
      
    } catch (error) {
      console.error('Enhancement error:', error)
      setError('Failed to enhance prompt. Please try again.')
    } finally {
      setIsEnhancing(false)
    }
  }

  const selectEnhancedPrompt = (selectedPrompt) => {
    // Handle both string and object formats
    const promptText = typeof selectedPrompt === 'string' ? selectedPrompt : selectedPrompt.prompt || selectedPrompt
    setPrompt(promptText)
    setEnhancedPrompts([])
    setShowAllPrompts(false)
  }

  const handleGenerate = async () => {
    if (!prompt.trim() && uploadedImages.filter(img => img).length === 0) {
      setError('Please enter a prompt or upload at least one image')
      return
    }

    setIsGenerating(true)
    setError('')
    setGeneratedImage(null)

    try {
      // Prepare form data for API call
      const formData = new FormData()
      formData.append('prompt', prompt)
      formData.append('userId', user.id)

      // Add uploaded images if any
      uploadedImages.forEach((image, index) => {
        if (image && image.file) {
          formData.append(`image${index + 1}`, image.file)
        }
      })

      // Make API call to backend
      const response = await fetch('http://localhost:3011/api/images/generate', {
        method: 'POST',
        body: formData,
        headers: {
          // Don't set Content-Type header for FormData, let browser set it
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })

      if (!response.ok) {
        throw new Error(`API Error: ${response.status} ${response.statusText}`)
      }

      const result = await response.json()
      
      // Use the actual generated image from the API
      if (result.success && result.data && result.data.image) {
        setGeneratedImage({
          url: result.data.image.image_url,
          prompt: prompt,
          timestamp: new Date().toISOString(),
          id: result.data.image.id,
          uploadedFiles: result.data.uploadedFiles || 0
        })
      } else {
        throw new Error(result.message || result.error || 'Image generation failed')
      }

    } catch (error) {
      console.error('Generation error:', error)
      setError(error.message || 'Failed to generate image. Please try again.')
    } finally {
      setIsGenerating(false)
    }
  }

  const handleCancel = () => {
    setPrompt('')
    setUploadedImages([])
    setGeneratedImage(null)
    setError('')
    if (fileInputRef1.current) fileInputRef1.current.value = ''
    if (fileInputRef2.current) fileInputRef2.current.value = ''
  }

  const handleDownload = () => {
    if (generatedImage) {
      // Create download link
      const link = document.createElement('a')
      link.href = generatedImage.url
      link.download = `picture-this-${generatedImage.id}.jpg`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    }
  }

  const handleSave = async () => {
    if (generatedImage) {
      try {
        // TODO: Implement save to user gallery
        alert('Image saved to your gallery! (Feature coming soon)')
      } catch (error) {
        setError('Failed to save image')
      }
    }
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div style={{ 
        maxWidth: '1200px', 
        margin: '0 auto', 
        backgroundColor: '#0f172a', 
        color: '#f8fafc', 
      minHeight: '100vh' 
    }}>
      {/* Header */}
      <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
        <h1 style={{ color: '#60a5fa', marginBottom: '1rem' }}>🎨 AI Image Generation Studio</h1>
        <p style={{ color: '#cbd5e1' }}>
          Upload reference images and describe what you want to create
        </p>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '2rem', alignItems: 'start' }}>
        
        {/* Left Panel - Input Controls */}
        <div style={{ 
          backgroundColor: '#1e293b',
          padding: '2rem',
          borderRadius: '1rem',
          boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.3)',
          border: '1px solid #475569'
        }}>
          <h2 style={{ color: '#e2e8f0', marginBottom: '1.5rem', fontSize: '1.25rem' }}>
            🖼️ Create Your Image
          </h2>

          {/* Image Upload Section */}
          <div style={{ marginBottom: '2rem' }}>
            <h3 style={{ color: '#cbd5e1', marginBottom: '1rem', fontSize: '1rem' }}>
              Reference Images (Optional)
            </h3>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
              
              {/* Upload Box 1 */}
              <div style={{ position: 'relative' }}>
                <div
                  onClick={() => fileInputRef1.current?.click()}
                  style={{
                    border: '2px dashed #475569',
                    borderRadius: '0.5rem',
                    padding: '2rem 1rem',
                    textAlign: 'center',
                    cursor: 'pointer',
                    backgroundColor: uploadedImages[0] ? '#334155' : '#1e293b',
                    transition: 'all 0.2s',
                    minHeight: '150px',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center'
                  }}
                  onMouseOver={(e) => e.target.style.borderColor = '#60a5fa'}
                  onMouseOut={(e) => e.target.style.borderColor = '#475569'}
                >
                  {uploadedImages[0] ? (
                    <div style={{ position: 'relative' }}>
                      <img 
                        src={uploadedImages[0].preview} 
                        alt="Upload 1"
                        style={{ 
                          maxWidth: '100%', 
                          maxHeight: '120px', 
                          borderRadius: '0.25rem',
                          objectFit: 'cover'
                        }}
                      />
                      <button
                        onClick={(e) => {
                          e.stopPropagation()
                          removeImage(0)
                        }}
                        style={{
                          position: 'absolute',
                          top: '-8px',
                          right: '-8px',
                          backgroundColor: '#dc2626',
                          color: 'white',
                          border: 'none',
                          borderRadius: '50%',
                          width: '24px',
                          height: '24px',
                          cursor: 'pointer',
                          fontSize: '12px'
                        }}
                      >
                        ×
                      </button>
                    </div>
                  ) : (
                    <div>
                      <div style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>📁</div>
                      <p style={{ color: '#94a3b8', fontSize: '0.875rem' }}>
                        Click to upload<br/>Image 1
                      </p>
                    </div>
                  )}
                </div>
                <input
                  ref={fileInputRef1}
                  type="file"
                  accept="image/*"
                  onChange={(e) => handleImageUpload(e, 0)}
                  style={{ display: 'none' }}
                />
              </div>

              {/* Upload Box 2 */}
              <div style={{ position: 'relative' }}>
                <div
                  onClick={() => fileInputRef2.current?.click()}
                  style={{
                    border: '2px dashed #475569',
                    borderRadius: '0.5rem',
                    padding: '2rem 1rem',
                    textAlign: 'center',
                    cursor: 'pointer',
                    backgroundColor: uploadedImages[1] ? '#334155' : '#1e293b',
                    transition: 'all 0.2s',
                    minHeight: '150px',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'center',
                    alignItems: 'center'
                  }}
                  onMouseOver={(e) => e.target.style.borderColor = '#60a5fa'}
                  onMouseOut={(e) => e.target.style.borderColor = '#475569'}
                >
                  {uploadedImages[1] ? (
                    <div style={{ position: 'relative' }}>
                      <img 
                        src={uploadedImages[1].preview} 
                        alt="Upload 2"
                        style={{ 
                          maxWidth: '100%', 
                          maxHeight: '120px', 
                          borderRadius: '0.25rem',
                          objectFit: 'cover'
                        }}
                      />
                      <button
                        onClick={(e) => {
                          e.stopPropagation()
                          removeImage(1)
                        }}
                        style={{
                          position: 'absolute',
                          top: '-8px',
                          right: '-8px',
                          backgroundColor: '#dc2626',
                          color: 'white',
                          border: 'none',
                          borderRadius: '50%',
                          width: '24px',
                          height: '24px',
                          cursor: 'pointer',
                          fontSize: '12px'
                        }}
                      >
                        ×
                      </button>
                    </div>
                  ) : (
                    <div>
                      <div style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>📁</div>
                      <p style={{ color: '#94a3b8', fontSize: '0.875rem' }}>
                        Click to upload<br/>Image 2
                      </p>
                    </div>
                  )}
                </div>
                <input
                  ref={fileInputRef2}
                  type="file"
                  accept="image/*"
                  onChange={(e) => handleImageUpload(e, 1)}
                  style={{ display: 'none' }}
                />
              </div>
            </div>
            <p style={{ color: '#94a3b8', fontSize: '0.75rem', marginTop: '0.5rem' }}>
              Max 2 images, 10MB each. Supported: JPG, PNG, GIF
            </p>
          </div>

          {/* Prompt Input */}
          <div style={{ marginBottom: '2rem' }}>
            <label style={{ 
              display: 'block', 
              color: '#374151', 
              marginBottom: '0.5rem',
              fontSize: '1rem',
              fontWeight: '500'
            }}>
              ✨ Describe your image
            </label>
            <textarea
              value={prompt}
              onChange={(e) => setPrompt(e.target.value)}
              placeholder="Describe what you want to create... e.g., 'A majestic mountain landscape at sunset with purple clouds'"
              style={{
                width: '100%',
                minHeight: '120px',
                padding: '0.75rem',
                border: '1px solid #d1d5db',
                borderRadius: '0.5rem',
                fontSize: '1rem',
                resize: 'vertical',
                fontFamily: 'inherit'
              }}
              maxLength={500}
            />
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '0.25rem' }}>
              <p style={{ color: '#6b7280', fontSize: '0.75rem' }}>
                {prompt.length}/500 characters
              </p>
              <button
                onClick={handleEnhancePrompt}
                disabled={isEnhancing || !prompt.trim()}
                style={{
                  backgroundColor: isEnhancing ? '#9ca3af' : '#7c3aed',
                  color: 'white',
                  border: 'none',
                  padding: '0.5rem 1rem',
                  borderRadius: '0.375rem',
                  fontSize: '0.75rem',
                  fontWeight: '500',
                  cursor: isEnhancing ? 'not-allowed' : 'pointer',
                  transition: 'background-color 0.2s'
                }}
              >
                {isEnhancing ? '✨ Enhancing...' : '🚀 Enhance Prompt'}
              </button>
            </div>
          </div>

          {/* Enhanced Prompts Display */}
          {enhancedPrompts.length > 0 && (
            <div style={{ 
              marginBottom: '2rem',
              backgroundColor: '#475569',
              border: '1px solid #64748b',
              borderRadius: '0.5rem',
              padding: '1rem'
            }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem' }}>
                <h4 style={{ color: '#e2e8f0', fontSize: '0.875rem', fontWeight: '600' }}>
                  🎯 Enhanced Prompts ({enhancedPrompts.length})
                </h4>
                {enhancedPrompts.length > 3 && (
                  <button
                    onClick={() => setShowAllPrompts(!showAllPrompts)}
                    style={{
                      backgroundColor: 'transparent',
                      color: '#2563eb',
                      border: 'none',
                      fontSize: '0.75rem',
                      fontWeight: '500',
                      cursor: 'pointer',
                      textDecoration: 'underline'
                    }}
                  >
                    {showAllPrompts ? 'Show Less' : `View All ${enhancedPrompts.length}`}
                  </button>
                )}
              </div>
              
              <div style={{ display: 'grid', gap: '0.75rem' }}>
                {(showAllPrompts ? enhancedPrompts : enhancedPrompts.slice(0, 3)).map((enhancedPrompt, index) => (
                  <div
                    key={index}
                    onClick={() => selectEnhancedPrompt(enhancedPrompt.prompt || enhancedPrompt)}
                    style={{
                      backgroundColor: '#334155',
                      border: '1px solid #475569',
                      borderRadius: '0.375rem',
                      padding: '0.75rem',
                      cursor: 'pointer',
                      transition: 'all 0.2s',
                      fontSize: '0.875rem',
                      lineHeight: '1.4'
                    }}
                    onMouseOver={(e) => {
                      e.target.style.borderColor = '#60a5fa'
                      e.target.style.backgroundColor = '#475569'
                    }}
                    onMouseOut={(e) => {
                      e.target.style.borderColor = '#475569'
                      e.target.style.backgroundColor = '#334155'
                    }}
                  >
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.25rem' }}>
                      <span style={{ color: '#94a3b8', fontSize: '0.75rem', fontWeight: '500' }}>
                        Option {index + 1}
                      </span>
                      {enhancedPrompt.rating && (
                        <span style={{ 
                          color: '#10b981', 
                          fontSize: '0.75rem', 
                          fontWeight: '600',
                          backgroundColor: '#064e3b',
                          padding: '0.125rem 0.375rem',
                          borderRadius: '0.25rem'
                        }}>
                          ★ {enhancedPrompt.rating}
                        </span>
                      )}
                    </div>
                    <p style={{ color: '#e2e8f0', margin: '0' }}>
                      {enhancedPrompt.prompt || enhancedPrompt}
                    </p>
                  </div>
                ))}
              </div>
              
              <p style={{ 
                color: '#6b7280', 
                fontSize: '0.75rem', 
                marginTop: '0.75rem',
                fontStyle: 'italic'
              }}>
                💡 Click any prompt to use it for generation
              </p>
            </div>
          )}

          {/* Error Display */}
          {error && (
            <div style={{
              backgroundColor: '#fef2f2',
              border: '1px solid #fecaca',
              color: '#dc2626',
              padding: '0.75rem',
              borderRadius: '0.5rem',
              marginBottom: '1rem',
              fontSize: '0.875rem'
            }}>
              ⚠️ {error}
            </div>
          )}

          {/* Action Buttons */}
          <div style={{ display: 'flex', gap: '1rem' }}>
            <button
              onClick={handleGenerate}
              disabled={isGenerating || (!prompt.trim() && uploadedImages.filter(img => img).length === 0)}
              style={{
                flex: 1,
                backgroundColor: isGenerating ? '#9ca3af' : '#2563eb',
                color: 'white',
                border: 'none',
                padding: '0.75rem 1.5rem',
                borderRadius: '0.5rem',
                fontSize: '1rem',
                fontWeight: '600',
                cursor: isGenerating ? 'not-allowed' : 'pointer',
                transition: 'background-color 0.2s'
              }}
            >
              {isGenerating ? '🎨 Generating...' : '🚀 Generate Image'}
            </button>
            <button
              onClick={handleCancel}
              disabled={isGenerating}
              style={{
                backgroundColor: '#6b7280',
                color: 'white',
                border: 'none',
                padding: '0.75rem 1.5rem',
                borderRadius: '0.5rem',
                fontSize: '1rem',
                fontWeight: '600',
                cursor: isGenerating ? 'not-allowed' : 'pointer'
              }}
            >
              Cancel
            </button>
          </div>
        </div>

        {/* Right Panel - Generated Image Display */}
        <div style={{ 
          backgroundColor: '#1e293b',
          padding: '2rem',
          borderRadius: '1rem',
          boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.3)',
          border: '1px solid #475569',
          minHeight: '600px'
        }}>
          <h2 style={{ color: '#e2e8f0', marginBottom: '1.5rem', fontSize: '1.25rem' }}>
            🖼️ Generated Result
          </h2>

          {isGenerating ? (
            <div style={{ 
              display: 'flex', 
              flexDirection: 'column',
              alignItems: 'center', 
              justifyContent: 'center',
              minHeight: '400px',
              textAlign: 'center'
            }}>
              <div style={{
                width: '60px',
                height: '60px',
                border: '4px solid #e5e7eb',
                borderTop: '4px solid #2563eb',
                borderRadius: '50%',
                animation: 'spin 1s linear infinite',
                marginBottom: '1rem'
              }}></div>
              <p style={{ color: '#6b7280', fontSize: '1.125rem' }}>Creating your image...</p>
              <p style={{ color: '#9ca3af', fontSize: '0.875rem', marginTop: '0.5rem' }}>
                This may take a few moments
              </p>
            </div>
          ) : generatedImage ? (
            <div>
              <div style={{ textAlign: 'center', marginBottom: '1.5rem' }}>
                <img 
                  src={generatedImage.url} 
                  alt="Generated"
                  style={{ 
                    maxWidth: '100%', 
                    maxHeight: '400px',
                    borderRadius: '0.5rem',
                    border: '1px solid #e5e7eb',
                    objectFit: 'contain'
                  }}
                />
              </div>
              
              {/* Image Info */}
              <div style={{ 
                backgroundColor: '#334155', 
                padding: '1rem', 
                borderRadius: '0.5rem',
                marginBottom: '1.5rem'
              }}>
                <p style={{ color: '#e2e8f0', fontSize: '0.875rem', marginBottom: '0.5rem' }}>
                  <strong>Prompt:</strong> {generatedImage.prompt}
                </p>
                <p style={{ color: '#94a3b8', fontSize: '0.75rem' }}>
                  Generated: {new Date(generatedImage.timestamp).toLocaleString()}
                </p>
              </div>

              {/* Action Buttons */}
              <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
                <button
                  onClick={handleDownload}
                  style={{
                    flex: 1,
                    backgroundColor: '#059669',
                    color: 'white',
                    border: 'none',
                    padding: '0.75rem 1rem',
                    borderRadius: '0.5rem',
                    fontSize: '0.875rem',
                    fontWeight: '600',
                    cursor: 'pointer',
                    minWidth: '120px'
                  }}
                >
                  💾 Download
                </button>
                <Link
                  to="/gallery"
                  style={{
                    flex: 1,
                    backgroundColor: '#7c3aed',
                    color: 'white',
                    border: 'none',
                    padding: '0.75rem 1rem',
                    borderRadius: '0.5rem',
                    fontSize: '0.875rem',
                    fontWeight: '600',
                    cursor: 'pointer',
                    textDecoration: 'none',
                    textAlign: 'center',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    minWidth: '120px'
                  }}
                >
                  🎨 View Gallery
                </Link>
              </div>
            </div>
          ) : (
            <div style={{ 
              display: 'flex', 
              flexDirection: 'column',
              alignItems: 'center', 
              justifyContent: 'center',
              minHeight: '400px',
              textAlign: 'center',
              color: '#9ca3af'
            }}>
              <div style={{ fontSize: '4rem', marginBottom: '1rem' }}>🎨</div>
              <p style={{ fontSize: '1.125rem', marginBottom: '0.5rem' }}>
                Your generated image will appear here
              </p>
              <p style={{ fontSize: '0.875rem' }}>
                Upload images and/or enter a prompt to get started
              </p>
            </div>
          )}
        </div>
      </div>

      {/* Back to Dashboard */}
      <div style={{ textAlign: 'center', marginTop: '2rem' }}>
        <Link to="/dashboard" style={{ 
          color: '#2563eb', 
          textDecoration: 'underline',
          fontSize: '0.875rem'
        }}>
          ← Back to Dashboard
        </Link>
      </div>
    </div>
    </div>
  )
}
export default ImageGeneration
