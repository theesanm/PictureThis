"use client";

import React, { useState, useEffect } from 'react';
import { useAuth } from '@/lib/auth-context';
import { useToast } from '@/components/ui/use-toast';

// Simple inline SVG icons to avoid SSR/dynamic import issues during build
const UserIcon = (props: any) => (
  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" strokeWidth={1.5} strokeLinecap="round" strokeLinejoin="round" {...props}>
    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
    <circle cx="12" cy="7" r="4" />
  </svg>
);

const MailIcon = (props: any) => (
  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" strokeWidth={1.5} strokeLinecap="round" strokeLinejoin="round" {...props}>
    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8" />
    <rect x="3" y="5" width="18" height="14" rx="2" />
  </svg>
);

const SaveIcon = (props: any) => (
  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" strokeWidth={1.5} strokeLinecap="round" strokeLinejoin="round" {...props}>
    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
    <polyline points="17 21 17 13 7 13 7 21" />
  </svg>
);

export default function ProfileClient() {
  const { user, refreshUserProfile } = useAuth();
  // const { toast } = useToast();
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
  });
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    if (user) {
      setFormData({
        fullName: user.fullName || user.name || '',
        email: user.email || '',
      });
    }
  }, [user]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    try {
      setIsLoading(true);
      // const response = await userAPI.updateProfile({
      //   fullName: formData.fullName,
      // });
      
      // Simulate successful update
      // if (response.data.success) {
        // toast({
        //   title: "Profile updated successfully",
        //   description: "Your profile has been updated.",
        //   variant: "default",
        // });
        refreshUserProfile(); // Refresh user data in context
      // } else {
      //   toast({
      //     title: "Update failed",
      //     description: response.data.message || 'Failed to update profile',
      //     variant: "destructive",
      //   });
      // }
    } catch (error) {
      console.error('Error updating profile:', error);
      // toast({
      //   title: "Error",
      //   description: 'An error occurred while updating your profile',
      //   variant: "destructive",
      // });
    } finally {
      setIsLoading(false);
    }
  };

  if (!user) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-purple-500"></div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto">
      <h1 className="text-3xl font-bold mb-8 text-white">Your Profile</h1>
      
      <div className="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Account Information */}
          <div>
            <h2 className="text-xl font-semibold mb-4 text-white flex items-center">
              <UserIcon className="mr-2" /> Account Information
            </h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Name */}
              <div>
                <label htmlFor="fullName" className="block text-sm font-medium text-gray-300 mb-1">
                  Name
                </label>
                <input
                  type="text"
                  id="fullName"
                  name="fullName"
                  value={formData.fullName}
                  onChange={handleChange}
                  className="w-full p-3 bg-gray-700 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-white"
                  placeholder="Your name"
                />
              </div>
              
              {/* Email (Disabled) */}
              <div>
                <label htmlFor="email" className="block text-sm font-medium text-gray-300 mb-1">
                  Email Address
                </label>
                <div className="flex items-center">
                  <input
                    type="email"
                    id="email"
                    name="email"
                    value={formData.email}
                    disabled
                    className="w-full p-3 bg-gray-700 border border-gray-600 rounded-md text-gray-400 cursor-not-allowed"
                  />
                  {user.isVerified ? (
                    <span className="ml-2 px-2 py-1 bg-green-900/30 text-green-400 text-xs rounded-md">Verified</span>
                  ) : (
                    <span className="ml-2 px-2 py-1 bg-yellow-900/30 text-yellow-400 text-xs rounded-md">Pending</span>
                  )}
                </div>
                <p className="mt-1 text-xs text-gray-400">Email address cannot be changed</p>
              </div>
            </div>
          </div>
          
          {/* Account Stats */}
          <div className="border-t border-gray-700 pt-6">
            <h2 className="text-xl font-semibold mb-4 text-white flex items-center">
              <MailIcon className="mr-2" /> Account Stats
            </h2>
            
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div className="bg-gray-700/50 p-4 rounded-lg">
                <p className="text-gray-400 text-sm">Credits</p>
                <p className="text-2xl font-bold text-yellow-400">{user.credits || 0}</p>
              </div>
              
              <div className="bg-gray-700/50 p-4 rounded-lg">
                <p className="text-gray-400 text-sm">Account Created</p>
                <p className="text-lg font-semibold text-white">
                  {new Date(user.createdAt || '').toLocaleDateString()}
                </p>
              </div>
              
              <div className="bg-gray-700/50 p-4 rounded-lg">
                <p className="text-gray-400 text-sm">Account Type</p>
                <p className="text-lg font-semibold text-white capitalize">
                  {user.role || 'User'}
                </p>
              </div>
            </div>
          </div>
          
          {/* Submit Button */}
          <div className="flex justify-end pt-4 border-t border-gray-700">
            <button
              type="submit"
              disabled={isLoading}
              className={`px-5 py-2 bg-purple-600 hover:bg-purple-700 rounded-md text-white font-medium flex items-center ${isLoading ? 'opacity-70 cursor-not-allowed' : ''}`}
            >
              {isLoading ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-t-2 border-b-2 border-white mr-2"></div>
                  Saving...
                </>
              ) : (
                <>
                  <SaveIcon className="mr-2" /> 
                  Save Changes
                </>
              )}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
