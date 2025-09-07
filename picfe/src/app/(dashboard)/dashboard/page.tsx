'use client';

import React, { useEffect, useState } from 'react';
import Link from 'next/link';
import { useAuth } from '@/lib/auth-context';
import { imagesAPI } from '@/lib/api';
import { motion } from 'framer-motion';
import { Image as ImageIcon, Plus, RefreshCw } from 'lucide-react';

interface Image {
  id: string;
  url: string;
  imageUrl?: string; // For backward compatibility
  prompt?: string;
  created_at: string;
  createdAt?: string; // For backward compatibility
  generationCost?: number;
}

export default function Dashboard() {
  const { user } = useAuth();
  const [recentImages, setRecentImages] = useState<Image[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchRecentImages = async () => {
      try {
        const response = await imagesAPI.getGallery();
        if (response.data.success) {
          // Check where the images are in the response data structure
          const images = response.data.data?.images || response.data.images || [];
          // Only take the latest 4 images
          setRecentImages(images.slice(0, 4));
        } else {
          setError('Failed to load recent images');
        }
      } catch (err) {
        console.error('Error fetching gallery:', err);
        setError('Error loading your images');
      } finally {
        setLoading(false);
      }
    };

    fetchRecentImages();
  }, []);

  return (
    <div>
      {/* Welcome Section */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-2">Welcome, {user?.full_name || 'Creator'}!</h1>
        <p className="text-gray-300">Your AI image generation dashboard awaits.</p>
      </div>

      {/* Stats & Quick Actions */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        {/* Credits */}
        <motion.div 
          className="bg-gray-800 rounded-xl p-6 border border-gray-700"
          whileHover={{ y: -5, transition: { duration: 0.2 } }}
        >
          <h3 className="text-xl font-semibold mb-4">Available Credits</h3>
          <div className="flex items-end justify-between">
            <div>
              <span className="text-4xl font-bold text-yellow-400">{user?.credits || 0}</span>
              <span className="ml-2 text-gray-400">credits</span>
            </div>
            <Link 
              href="/credits"
              className="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-md text-white text-sm font-medium transition-colors"
            >
              Buy More
            </Link>
          </div>
        </motion.div>

        {/* Quick Generate */}
        <motion.div 
          className="bg-gray-800 rounded-xl p-6 border border-gray-700 md:col-span-2"
          whileHover={{ y: -5, transition: { duration: 0.2 } }}
        >
          <h3 className="text-xl font-semibold mb-4">Create New Image</h3>
          <p className="text-gray-400 mb-4">
            Transform your ideas into stunning visual content with AI.
          </p>
          <Link
            href="/generate"
            className="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 rounded-md text-white font-medium hover:opacity-90 transition-opacity"
          >
            <Plus size={18} className="mr-2" /> Generate Image
          </Link>
        </motion.div>
      </div>

      {/* Recent Images */}
      <div className="mb-8">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-2xl font-bold">Recent Creations</h2>
          <Link
            href="/gallery"
            className="text-purple-400 hover:text-purple-300 flex items-center"
          >
            View All
            <svg className="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
            </svg>
          </Link>
        </div>

        {loading ? (
          <div className="flex justify-center items-center h-60">
            <RefreshCw className="w-8 h-8 text-purple-500 animate-spin" />
          </div>
        ) : error ? (
          <div className="bg-red-900/20 border border-red-600 text-red-200 p-4 rounded-md">
            {error}
          </div>
        ) : recentImages.length === 0 ? (
          <div className="bg-gray-800 border border-gray-700 rounded-xl p-8 text-center">
            <div className="flex justify-center mb-4">
              <ImageIcon className="w-12 h-12 text-gray-500" />
            </div>
            <h3 className="text-xl font-medium mb-2">No images yet</h3>
            <p className="text-gray-400 mb-4">
              Start creating amazing AI-generated images today
            </p>
            <Link
              href="/generate"
              className="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 rounded-md text-white font-medium hover:opacity-90 transition-opacity"
            >
              <Plus size={18} className="mr-2" /> Generate Your First Image
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            {recentImages.map((image) => (
              <motion.div 
                key={image.id}
                className="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden"
                whileHover={{ y: -5, transition: { duration: 0.2 } }}
              >
                <div className="aspect-square relative bg-gray-900">
                  {/* Replace with actual image when available */}
                  {(image.imageUrl || image.url) ? (
                    <img 
                      src={image.imageUrl || image.url} 
                      alt={image.prompt || 'Generated image'} 
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <div className="w-full h-full flex items-center justify-center">
                      <ImageIcon className="w-10 h-10 text-gray-700" />
                    </div>
                  )}
                </div>
                <div className="p-4">
                  <p className="text-gray-300 text-sm line-clamp-2">{image.prompt || 'Generated image'}</p>
                  <div className="flex justify-between items-center mt-2">
                    <span className="text-xs text-gray-400">
                      {new Date(image.createdAt || image.created_at || Date.now()).toLocaleDateString()}
                    </span>
                    <span className="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded">
                      -{image.generationCost || 1} credits
                    </span>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        )}
      </div>

      {/* Tips & Inspiration */}
      <div className="bg-gradient-to-r from-purple-900/30 to-pink-900/30 rounded-xl p-6 border border-purple-800/50">
        <h3 className="text-xl font-semibold mb-2">Pro Tips for Better Results</h3>
        <ul className="list-disc pl-5 space-y-2 text-gray-300">
          <li>Be specific about styles: "oil painting," "watercolor," "3D render"</li>
          <li>Mention lighting: "dramatic lighting," "golden hour," "soft diffused light"</li>
          <li>Include composition details: "ultra-wide angle," "aerial view," "close-up"</li>
          <li>Use our prompt enhancement feature for better results</li>
        </ul>
      </div>
    </div>
  );
}
