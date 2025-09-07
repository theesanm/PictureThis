'use client';

import React from 'react';
import Link from 'next/link';
import Image from 'next/image';
import { motion } from 'framer-motion';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { Wand2, Sparkles, Send, Database } from 'lucide-react';

export default function Home() {
  return (
    <div className="min-h-screen flex flex-col bg-gray-900 text-white">
      <Header />
      
      <main className="flex-1">
        {/* Hero Section */}
        <section className="relative overflow-hidden">
          <div className="absolute inset-0 bg-gradient-to-br from-purple-800/30 to-pink-600/30 pointer-events-none" />
          <div 
            className="absolute inset-0 opacity-30" 
            style={{
              backgroundImage: 'url("data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC" fill-opacity="0.2"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
            }}
          />
          
          <div className="container mx-auto px-4 py-24 md:py-32 relative z-10">
            <div className="text-center max-w-4xl mx-auto">
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6 }}
              >
                <h1 className="text-4xl md:text-6xl font-bold mb-6 bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-500">
                  Transform Your Ideas Into Stunning Images
                </h1>
                
                <p className="text-lg md:text-xl text-gray-300 mb-8 max-w-3xl mx-auto">
                  PictureThis uses advanced AI to generate beautiful, detailed images from your text descriptions or reference images. Unleash your creativity today!
                </p>
                
                <div className="flex flex-col md:flex-row gap-4 justify-center">
                  <Link
                    href="/register"
                    className="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-500 rounded-md text-white font-medium hover:opacity-90 transition-opacity"
                  >
                    Get Started For Free
                  </Link>
                  <Link
                    href="/generate"
                    className="px-8 py-3 bg-gray-700 hover:bg-gray-600 rounded-md text-white font-medium transition-colors"
                  >
                    Try Demo
                  </Link>
                </div>
              </motion.div>
              
              <motion.div
                className="mt-12 md:mt-16 relative"
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ duration: 0.7, delay: 0.2 }}
              >
                <div className="bg-gray-800 p-4 rounded-xl shadow-2xl overflow-hidden">
                  {/* Placeholder for demo image */}
                  <div className="aspect-video bg-gradient-to-br from-purple-900 to-pink-800 rounded-lg flex items-center justify-center">
                    <span className="text-white/80 text-xl font-medium">AI-Generated Image Example</span>
                  </div>
                </div>
                
                <div className="absolute -bottom-4 -right-4 bg-gradient-to-r from-purple-600 to-pink-500 rounded-lg p-3 shadow-lg">
                  <Wand2 className="w-6 h-6" />
                </div>
              </motion.div>
            </div>
          </div>
        </section>

        {/* Features Section */}
        <section className="py-20 bg-gray-800">
          <div className="container mx-auto px-4">
            <div className="text-center mb-16">
              <h2 className="text-3xl md:text-4xl font-bold mb-4">Powerful Features</h2>
              <p className="text-lg text-gray-300 max-w-2xl mx-auto">
                Everything you need to bring your imagination to life with AI-generated imagery
              </p>
            </div>
            
            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
              {/* Feature 1 */}
              <motion.div 
                className="bg-gray-900 rounded-xl p-6 border border-gray-700"
                whileHover={{ y: -5, transition: { duration: 0.2 } }}
              >
                <div className="w-12 h-12 bg-purple-500/20 rounded-full flex items-center justify-center mb-4">
                  <Wand2 className="w-6 h-6 text-purple-400" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Text-to-Image Generation</h3>
                <p className="text-gray-400">
                  Convert your text descriptions into highly detailed images using our state-of-the-art AI models.
                </p>
              </motion.div>
              
              {/* Feature 2 */}
              <motion.div 
                className="bg-gray-900 rounded-xl p-6 border border-gray-700"
                whileHover={{ y: -5, transition: { duration: 0.2 } }}
              >
                <div className="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center mb-4">
                  <Sparkles className="w-6 h-6 text-blue-400" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Prompt Enhancement</h3>
                <p className="text-gray-400">
                  Our AI helps you craft the perfect prompts to get better results with intelligent suggestions.
                </p>
              </motion.div>
              
              {/* Feature 3 */}
              <motion.div 
                className="bg-gray-900 rounded-xl p-6 border border-gray-700"
                whileHover={{ y: -5, transition: { duration: 0.2 } }}
              >
                <div className="w-12 h-12 bg-pink-500/20 rounded-full flex items-center justify-center mb-4">
                  <Send className="w-6 h-6 text-pink-400" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Instant Sharing</h3>
                <p className="text-gray-400">
                  Download your creations instantly or share them directly to your social media platforms.
                </p>
              </motion.div>
              
              {/* Feature 4 */}
              <motion.div 
                className="bg-gray-900 rounded-xl p-6 border border-gray-700"
                whileHover={{ y: -5, transition: { duration: 0.2 } }}
              >
                <div className="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center mb-4">
                  <Image src="/image-icon.svg" alt="Image icon" width={24} height={24} className="text-green-400" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Image-to-Image Generation</h3>
                <p className="text-gray-400">
                  Upload reference images and transform them with AI into new creative variations.
                </p>
              </motion.div>
              
              {/* Feature 5 */}
              <motion.div 
                className="bg-gray-900 rounded-xl p-6 border border-gray-700"
                whileHover={{ y: -5, transition: { duration: 0.2 } }}
              >
                <div className="w-12 h-12 bg-yellow-500/20 rounded-full flex items-center justify-center mb-4">
                  <Database className="w-6 h-6 text-yellow-400" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Image Gallery</h3>
                <p className="text-gray-400">
                  All your generated images are saved to your personal gallery for easy access anytime.
                </p>
              </motion.div>
            </div>
          </div>
        </section>

        {/* Call to Action */}
        <section className="py-20 bg-gradient-to-br from-gray-900 to-gray-800">
          <div className="container mx-auto px-4 text-center">
            <h2 className="text-3xl md:text-4xl font-bold mb-6">Ready to Create Amazing Images?</h2>
            <p className="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
              Sign up today and get 50 free credits to start generating stunning AI images instantly!
            </p>
            <Link
              href="/register"
              className="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-500 rounded-md text-white font-medium hover:opacity-90 transition-opacity inline-block"
            >
              Create Free Account
            </Link>
          </div>
        </section>
      </main>
      
      <Footer />
    </div>
  );
}
