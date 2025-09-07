import React, { useState, useEffect } from 'react';
import { useAuth } from '../contexts/SimpleAuthContext';

const Gallery = () => {
  const [images, setImages] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [selectedImage, setSelectedImage] = useState(null);
  const { isAuthenticated } = useAuth();

  useEffect(() => {
    fetchGalleryImages();
  }, []);

  const fetchGalleryImages = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('No authentication token found');
      }
      
      const response = await fetch('http://localhost:3011/api/images/gallery', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      const data = await response.json();
      console.log('Gallery API response:', data);
      
      if (data.success) {
        console.log('Images received:', data.images);
        console.log('Image URLs:', data.images?.map(img => img.imageUrl));
        setImages(data.images);
      } else {
        setError(data.message || 'Failed to fetch gallery images');
      }
    } catch (err) {
      console.error('Error fetching gallery:', err);
      setError('Failed to load gallery images');
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
  };

  const openImageModal = (image) => {
    setSelectedImage(image);
  };

  const closeImageModal = () => {
    setSelectedImage(null);
  };

  const downloadImage = async (imageUrl, prompt) => {
    try {
      const response = await fetch(imageUrl);
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none';
      a.href = url;
      a.download = `generated-image-${prompt.substring(0, 30).replace(/[^a-z0-9]/gi, '_').toLowerCase()}.png`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (err) {
      console.error('Error downloading image:', err);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 p-8">
        <div className="max-w-6xl mx-auto">
          <div className="text-center">
            <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-400 mx-auto"></div>
            <p className="mt-4 text-slate-300">Loading your gallery...</p>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 p-8">
        <div className="max-w-6xl mx-auto">
          <div className="text-center">
            <div className="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-4">
              {error}
            </div>
            <button 
              onClick={fetchGalleryImages}
              className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
            >
              Retry
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 p-8">
      <div className="max-w-6xl mx-auto">
        <div className="text-center mb-8">
          <h1 className="text-4xl font-bold text-slate-100 mb-2">
            🖼️ My Gallery
          </h1>
          <p className="text-slate-300">
            View and manage all your generated images
          </p>
          <div className="mt-4">
            <span className="bg-blue-900 text-blue-200 px-3 py-1 rounded-full text-sm">
              {images.length} image{images.length !== 1 ? 's' : ''} generated
            </span>
          </div>
        </div>

        {images.length === 0 ? (
          <div className="text-center py-12">
            <div className="text-6xl mb-4">🎨</div>
            <h3 className="text-xl font-semibold text-slate-200 mb-2">No images yet</h3>
            <p className="text-slate-400 mb-6">
              Generate your first AI image to see it appear here!
            </p>
            <a 
              href="/generate" 
              className="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors"
            >
              Generate Your First Image
            </a>
          </div>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            {images.map((image) => (
              <div 
                key={image.id} 
                className="bg-slate-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 cursor-pointer group border border-slate-700"
                onClick={() => openImageModal(image)}
              >
                                <div className="aspect-square overflow-hidden bg-slate-700 relative">
                  <img
                    src={image.imageUrl}
                    alt={image.prompt}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300 relative z-10"
                    onLoad={(e) => {
                      console.log('Image loaded successfully:', image.imageUrl);
                      e.target.style.opacity = '1';
                    }}
                    onError={(e) => {
                      console.error('Image failed to load:', image.imageUrl);
                      e.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjMzM0MTU1Ii8+CjxwYXRoIGQ9Ik03NSA3NUgxMjVWMTI1SDc1Vjc1WiIgZmlsbD0iIzQ3NTU2OSIvPgo8cGF0aCBkPSJNODcuNSA5NkM5MS42NDIxIDk2IDk1IDkyLjY0MjEgOTUgODguNUM5NSA4NC4zNTc5IDkxLjY0MjEgODEgODcuNSA4MUM4My4zNTc5IDgxIDgwIDg0LjM1NzkgODAgODguNUM4MCA5Mi42NDIxIDgzLjM1NzkgOTYgODcuNSA5NloiIGZpbGw9IiM2NDc0OGIiLz4KPHBhdGggZD0iTTgwIDExNUw5NSAxMDBMMTEwIDExNUw5NSAxMDBMMTEwIDExNUg4MFoiIGZpbGw9IiM2NDc0OGIiLz4KPC9zdmc+';
                    }}
                    style={{ opacity: 1 }}
                  />
                  <div className="absolute inset-0 opacity-0 group-hover:opacity-100 group-hover:bg-black group-hover:bg-opacity-30 transition-all duration-300 flex items-center justify-center z-20">
                    <div className="text-white transition-opacity duration-300 pointer-events-none">
                      <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                      </svg>
                    </div>
                  </div>
                </div>
                
                <div className="p-3">
                  <p className="text-xs text-slate-300 line-clamp-2 mb-2">
                    {image.prompt.length > 50 ? `${image.prompt.substring(0, 50)}...` : image.prompt}
                  </p>
                  <div className="text-xs text-slate-500">
                    {formatDate(image.createdAt)}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

                {/* Image Modal */}
        {selectedImage && (
          <div className="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4" onClick={closeImageModal}>
            <div className="bg-slate-800 rounded-lg max-w-4xl max-h-full overflow-auto border border-slate-600" onClick={(e) => e.stopPropagation()}>
              <div className="relative">
                <button
                  onClick={closeImageModal}
                  className="absolute top-4 right-4 z-10 bg-black bg-opacity-70 text-white rounded-full p-2 hover:bg-opacity-90 transition-all"
                >
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
                
                <img
                  src={selectedImage.imageUrl}
                  alt={selectedImage.prompt}
                  className="w-full h-auto max-h-[70vh] object-contain"
                />
                
                <div className="p-6">
                  <h3 className="text-lg font-semibold text-slate-100 mb-3">Image Details</h3>
                  
                  <div className="space-y-3">
                    <div>
                      <label className="text-sm font-medium text-slate-300">Prompt:</label>
                      <p className="text-slate-100 mt-1">{selectedImage.prompt}</p>
                    </div>
                    
                    <div className="flex justify-between items-center text-sm">
                      <div>
                        <label className="text-sm font-medium text-slate-300">Created:</label>
                        <p className="text-slate-100">{formatDate(selectedImage.createdAt)}</p>
                      </div>
                      {selectedImage.generationCost && (
                        <div className="bg-green-900 text-green-200 px-3 py-1 rounded-full">
                          Cost: ${selectedImage.generationCost}
                        </div>
                      )}
                    </div>
                    
                    <div className="flex gap-3 mt-4">
                      <button
                        onClick={() => downloadImage(selectedImage.imageUrl, selectedImage.prompt)}
                        className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download
                      </button>
                      <button
                        onClick={() => {
                          navigator.clipboard.writeText(selectedImage.prompt);
                          // Optional: Show a toast notification
                        }}
                        className="flex-1 bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700 transition-colors flex items-center justify-center gap-2"
                        title="Copy prompt to clipboard"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Copy Prompt
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default Gallery;
