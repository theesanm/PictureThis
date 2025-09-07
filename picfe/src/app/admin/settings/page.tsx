'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { ArrowLeft, Save, RefreshCw } from 'lucide-react';
import { toast } from 'react-toastify';

interface SystemSettings {
  creditCostPerImage: number;
  maxFreeCredits: number;
  stripeEnabled: boolean;
  enhancedPromptEnabled: boolean;
  enhancedPromptCost: number;
  aiProvider: 'openrouter' | 'openai' | 'stabilityai';
}

export default function SystemSettings() {
  const [settings, setSettings] = useState<SystemSettings>({
    creditCostPerImage: 10,
    maxFreeCredits: 50,
    stripeEnabled: false,
    enhancedPromptEnabled: true,
    enhancedPromptCost: 0,
    aiProvider: 'openrouter'
  });
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);

  // Function to fetch system settings
  const fetchSettings = async () => {
    try {
      setIsLoading(true);
      const response = await fetch('/api/admin/settings', {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      
      if (!response.ok) {
        throw new Error('Failed to fetch system settings');
      }
      
      const data = await response.json();
      setSettings(data.data.settings);
    } catch (error) {
      console.error('Error fetching settings:', error);
      toast.error('Failed to fetch system settings');
    } finally {
      setIsLoading(false);
    }
  };

  // Function to update system settings
  const saveSettings = async () => {
    try {
      setIsSaving(true);
      const response = await fetch('/api/admin/settings', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ settings })
      });
      
      if (!response.ok) {
        throw new Error('Failed to update system settings');
      }
      
      toast.success('System settings updated successfully');
    } catch (error) {
      console.error('Error updating settings:', error);
      toast.error('Failed to update system settings');
    } finally {
      setIsSaving(false);
    }
  };

  // Handle input change
  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value, type } = e.target as HTMLInputElement;
    
    setSettings(prev => ({
      ...prev,
      [name]: type === 'checkbox' 
        ? (e.target as HTMLInputElement).checked 
        : type === 'number' 
        ? parseInt(value) 
        : value
    }));
  };

  useEffect(() => {
    fetchSettings();
  }, []);

  return (
    <div className="min-h-screen bg-gray-900">
      <div className="bg-gray-800 border-b border-gray-700 shadow-lg">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between py-4">
            <div className="flex items-center">
              <Link href="/admin" className="flex items-center text-gray-300 hover:text-white">
                <ArrowLeft size={16} className="mr-2" />
                Back to Admin Dashboard
              </Link>
              <h1 className="ml-8 text-xl font-bold text-white">System Settings</h1>
            </div>
            
            <button
              onClick={saveSettings}
              disabled={isLoading || isSaving}
              className={`flex items-center px-4 py-2 bg-green-600 rounded text-white ${
                isLoading || isSaving ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'
              }`}
            >
              {isSaving ? (
                <>
                  <RefreshCw size={16} className="mr-2 animate-spin" />
                  Saving...
                </>
              ) : (
                <>
                  <Save size={16} className="mr-2" />
                  Save Settings
                </>
              )}
            </button>
          </div>
        </div>
      </div>

      <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {isLoading ? (
          <div className="flex justify-center items-center h-64">
            <RefreshCw size={24} className="animate-spin text-purple-500" />
          </div>
        ) : (
          <div className="space-y-8">
            <div className="bg-gray-800 border border-gray-700 rounded-lg p-6">
              <h2 className="text-xl font-bold text-white mb-4">Credit Settings</h2>
              
              <div className="space-y-4">
                <div>
                  <label htmlFor="creditCostPerImage" className="block text-sm font-medium text-gray-300 mb-2">
                    Credit Cost per Image
                  </label>
                  <input
                    type="number"
                    id="creditCostPerImage"
                    name="creditCostPerImage"
                    value={settings.creditCostPerImage}
                    onChange={handleChange}
                    min="1"
                    className="w-full p-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                  />
                  <p className="mt-1 text-sm text-gray-400">
                    Number of credits required to generate an image
                  </p>
                </div>
                
                <div>
                  <label htmlFor="maxFreeCredits" className="block text-sm font-medium text-gray-300 mb-2">
                    Max Free Credits
                  </label>
                  <input
                    type="number"
                    id="maxFreeCredits"
                    name="maxFreeCredits"
                    value={settings.maxFreeCredits}
                    onChange={handleChange}
                    min="0"
                    className="w-full p-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                  />
                  <p className="mt-1 text-sm text-gray-400">
                    Maximum number of free credits given to new users
                  </p>
                </div>
              </div>
            </div>
            
            <div className="bg-gray-800 border border-gray-700 rounded-lg p-6">
              <h2 className="text-xl font-bold text-white mb-4">Feature Settings</h2>
              
              <div className="space-y-4">
                <div className="flex items-center">
                  <input
                    type="checkbox"
                    id="stripeEnabled"
                    name="stripeEnabled"
                    checked={settings.stripeEnabled}
                    onChange={handleChange}
                    className="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                  />
                  <label htmlFor="stripeEnabled" className="ml-2 block text-sm text-gray-300">
                    Enable Stripe Payments
                  </label>
                </div>
                
                <div className="flex items-center">
                  <input
                    type="checkbox"
                    id="enhancedPromptEnabled"
                    name="enhancedPromptEnabled"
                    checked={settings.enhancedPromptEnabled}
                    onChange={handleChange}
                    className="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                  />
                  <label htmlFor="enhancedPromptEnabled" className="ml-2 block text-sm text-gray-300">
                    Enable Enhanced Prompts
                  </label>
                </div>
                
                {settings.enhancedPromptEnabled && (
                  <div>
                    <label htmlFor="enhancedPromptCost" className="block text-sm font-medium text-gray-300 mb-2">
                      Enhanced Prompt Cost (Credits)
                    </label>
                    <input
                      type="number"
                      id="enhancedPromptCost"
                      name="enhancedPromptCost"
                      value={settings.enhancedPromptCost}
                      onChange={handleChange}
                      min="0"
                      className="w-full p-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                    />
                    <p className="mt-1 text-sm text-gray-400">
                      Number of credits required to use enhanced prompts (0 = free)
                    </p>
                  </div>
                )}
              </div>
            </div>
            
            <div className="bg-gray-800 border border-gray-700 rounded-lg p-6">
              <h2 className="text-xl font-bold text-white mb-4">AI Provider Settings</h2>
              
              <div>
                <label htmlFor="aiProvider" className="block text-sm font-medium text-gray-300 mb-2">
                  AI Provider
                </label>
                <select
                  id="aiProvider"
                  name="aiProvider"
                  value={settings.aiProvider}
                  onChange={handleChange}
                  className="w-full p-2 bg-gray-700 border border-gray-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                >
                  <option value="openrouter">OpenRouter</option>
                  <option value="openai">OpenAI</option>
                  <option value="stabilityai">Stability AI</option>
                </select>
                <p className="mt-1 text-sm text-gray-400">
                  The AI provider to use for image generation
                </p>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
