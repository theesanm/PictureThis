'use client';

import React, { useEffect, useState } from 'react';
import { imagesAPI } from '@/lib/api';
import { useAuth } from '@/lib/auth-context';
import { motion } from 'framer-motion';
import { Download, Eye, Loader2, Share2 } from 'lucide-react';
import { toast } from 'react-toastify';
import Image from 'next/image';

// Define our own interface that matches what we're using in this component
interface ImageData {
  id: string;
  imageUrl: string;
  url?: string;  // For backward compatibility 
  prompt?: string;
  createdAt?: string;
  created_at?: string;  // For backward compatibility
}

// Create an adapter for any property name differences
const adaptImageData = (image: any): ImageData => ({
  id: image.id || `img-${Date.now()}`,
  imageUrl: image.imageUrl || image.url || '',
  prompt: image.prompt || '',
  createdAt: image.createdAt || image.created_at || new Date().toISOString()
});

export default function Gallery() {
  const [images, setImages] = useState<ImageData[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedImage, setSelectedImage] = useState<ImageData | null>(null);
  const { isAuthenticated } = useAuth();

  useEffect(() => {
    const fetchGallery = async () => {
      try {
        setLoading(true);
        const response = await imagesAPI.getGallery();
        if (response.data && response.data.success) {
          // Handle both response structures (data.data.images or data.images)
          const rawImages = response.data.data?.images || response.data.images || [];
          // Adapt each image to our format
          const adaptedImages = rawImages.map(adaptImageData);
          setImages(adaptedImages);
        } else {
          toast.error('Failed to load images');
        }
      } catch (error) {
        console.error('Error fetching gallery:', error);
        toast.error('Error loading gallery');
      } finally {
        setLoading(false);
      }
    };

    if (isAuthenticated) {
      fetchGallery();
    }
  }, [isAuthenticated]);

  const handleImageClick = (image: ImageData) => {
    setSelectedImage(image);
  };

  const handleDownload = (image: ImageData) => {
    // Create a download link for the image
    const link = document.createElement('a');
    link.href = image.imageUrl || image.url || '';
    link.download = `generated-image-${image.id}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    toast.success('Image downloading...');
  };

  const handleShare = (image: ImageData) => {
    // Copy image URL to clipboard
    navigator.clipboard.writeText(image.imageUrl || image.url || '')
      .then(() => toast.success('Image URL copied to clipboard'))
      .catch(() => toast.error('Failed to copy URL'));
  };

  const closeModal = () => {
    setSelectedImage(null);
  };

  return (
    <div className="p-6">
      <h1 className="text-3xl font-bold mb-6 text-white">Your Gallery</h1>
      
      {loading ? (
        <div className="flex justify-center items-center h-64">
          <Loader2 className="w-12 h-12 text-purple-500 animate-spin" />
          <span className="ml-4 text-xl text-gray-300">Loading your images...</span>
        </div>
      ) : images.length === 0 ? (
        <div className="text-center py-16">
          <p className="text-xl text-gray-400">Your gallery is empty.</p>
          <p className="text-gray-500 mt-2">Generate some images to see them here!</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          {images.map((image: ImageData) => (
            <motion.div
              key={image.id}
              className="bg-gray-800 rounded-lg overflow-hidden shadow-lg"
              whileHover={{ y: -5, transition: { duration: 0.2 } }}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.4 }}
            >
              <div className="relative aspect-square">
                <Image
                  src={image.imageUrl || image.url || '/placeholder-image.jpg'}
                  alt={image.prompt || 'Generated Image'}
                  fill
                  className="object-cover cursor-pointer"
                  onClick={() => handleImageClick(image)}
                  sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                />
              </div>
              <div className="p-4">
                <p className="text-sm text-gray-300 line-clamp-2 mb-3">
                  {image.prompt || 'Generated image'}
                </p>
                <div className="flex justify-between items-center">
                  <span className="text-xs text-gray-400">
                    {new Date(image.createdAt || image.created_at || '').toLocaleDateString()}
                  </span>
                  <div className="flex space-x-2">
                    <button 
                      onClick={() => handleDownload(image)}
                      className="p-1 rounded hover:bg-gray-700"
                      aria-label="Download image"
                    >
                      <Download className="w-5 h-5 text-gray-400 hover:text-white" />
                    </button>
                    <button 
                      onClick={() => handleShare(image)}
                      className="p-1 rounded hover:bg-gray-700"
                      aria-label="Share image"
                    >
                      <Share2 className="w-5 h-5 text-gray-400 hover:text-white" />
                    </button>
                  </div>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      )}

      {/* Image Modal */}
      {selectedImage && (
        <div className="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-80 p-4" onClick={closeModal}>
          <div className="relative max-w-4xl w-full" onClick={(e) => e.stopPropagation()}>
            <div className="relative bg-gray-900 rounded-lg p-2">
              <button 
                onClick={closeModal}
                className="absolute top-4 right-4 z-10 bg-gray-800 rounded-full p-2 hover:bg-gray-700"
              >
                <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              
              <div className="relative w-full aspect-square">
                <Image
                  src={selectedImage.imageUrl || selectedImage.url || '/placeholder-image.jpg'}
                  alt={selectedImage.prompt || 'Generated Image'}
                  fill
                  className="object-contain"
                  sizes="100vw"
                />
              </div>
              
              <div className="p-4 bg-gray-800">
                <p className="text-gray-200 mb-3">{selectedImage.prompt || 'Generated image'}</p>
                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-400">
                    {new Date(selectedImage.createdAt || selectedImage.created_at || '').toLocaleDateString()}
                  </span>
                  <div className="flex space-x-3">
                    <button 
                      onClick={() => handleDownload(selectedImage)}
                      className="flex items-center space-x-1 px-3 py-1 bg-gray-700 rounded hover:bg-gray-600"
                    >
                      <Download className="w-4 h-4" />
                      <span>Download</span>
                    </button>
                    <button 
                      onClick={() => handleShare(selectedImage)}
                      className="flex items-center space-x-1 px-3 py-1 bg-gray-700 rounded hover:bg-gray-600"
                    >
                      <Share2 className="w-4 h-4" />
                      <span>Share</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}