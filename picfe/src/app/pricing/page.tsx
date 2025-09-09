'use client';

import React, { useState } from 'react';
import { Package, CheckCircle, CreditCard } from 'lucide-react';
import Link from 'next/link';

interface CreditPackage {
  id: string;
  credits: number;
  price: number;
  name: string;
  savings?: string;
}

export default function Pricing() {
  const [loading, setLoading] = useState(false);

  // Hardcoded packages for now to test if the page renders
  const packages: CreditPackage[] = [
    { id: 'small', credits: 50, price: 200.00, name: '50 Credits' },
    { id: 'medium', credits: 75, price: 250.00, name: '75 Credits (10% off)' },
    { id: 'large', credits: 125, price: 300.00, name: '125 Credits (20% off)' },
    { id: 'premium', credits: 200, price: 350.00, name: '200 Credits (30% off)' }
  ];

  const creditDescriptionText = '1 credit = 1 image generation';

  const handlePurchase = (packageId: string) => {
    // Redirect to credits page for purchase
    window.location.href = `/credits?package=${packageId}`;
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500"></div>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto py-12">
      <div className="text-center mb-12">
        <h1 className="text-4xl font-bold mb-4">Choose Your Package</h1>
        <p className="text-xl text-gray-400 mb-8">Buy credits to generate AI images and enhance prompts</p>
        <div className="bg-gray-800 rounded-xl p-6 border border-gray-700 inline-block">
          <div className="flex items-center justify-center gap-2">
            <CreditCard className="text-purple-400" size={24} />
            <span className="text-lg text-purple-400">{creditDescriptionText}</span>
          </div>
        </div>
      </div>

      {/* Credit Packages */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        {packages.map((pkg) => (
          <div key={pkg.id} className="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-purple-500 transition-colors">
            <div className="text-center mb-4">
              <Package className="mx-auto text-purple-400 mb-2" size={32} />
              <h3 className="text-lg font-semibold">{pkg.name}</h3>
              {pkg.savings && (
                <span className="inline-block bg-green-600 text-white text-xs px-2 py-1 rounded-full mt-1">
                  {pkg.savings}
                </span>
              )}
            </div>

            <div className="text-center mb-4">
              <div className="text-3xl font-bold text-purple-400 mb-1">
                R{pkg.price.toFixed(2)}
              </div>
              <div className="text-sm text-gray-400">
                {(pkg.price / pkg.credits).toFixed(2)}c per credit
              </div>
            </div>

            <Link
              href="/login"
              className="w-full bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-90 text-white font-medium py-3 px-4 rounded-lg transition-all flex items-center justify-center gap-2"
            >
              Get Started
            </Link>
          </div>
        ))}
      </div>

      {/* Features */}
      <div className="bg-gray-800 rounded-xl p-8 border border-gray-700 mb-8">
        <h2 className="text-2xl font-bold mb-6 text-center">Why Choose Our Credits?</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="text-center">
            <CheckCircle className="mx-auto text-green-400 mb-3" size={32} />
            <h3 className="text-lg font-semibold mb-2">High Quality Images</h3>
            <p className="text-gray-400">Generate stunning AI images with our advanced models</p>
          </div>
          <div className="text-center">
            <CheckCircle className="mx-auto text-green-400 mb-3" size={32} />
            <h3 className="text-lg font-semibold mb-2">Prompt Enhancement</h3>
            <p className="text-gray-400">Get better results with our AI-powered prompt enhancement</p>
          </div>
          <div className="text-center">
            <CheckCircle className="mx-auto text-green-400 mb-3" size={32} />
            <h3 className="text-lg font-semibold mb-2">Never Expires</h3>
            <p className="text-gray-400">Your credits never expire, use them whenever you want</p>
          </div>
        </div>
      </div>

      {/* Payment Security Notice */}
      <div className="bg-blue-900/20 border border-blue-600 rounded-lg p-6">
        <div className="flex items-start gap-3">
          <CheckCircle className="text-blue-400 mt-1" size={20} />
          <div>
            <h3 className="font-semibold text-blue-400 mb-2">Secure Payments</h3>
            <p className="text-sm text-gray-300">
              All payments are processed securely through PayFast, South Africa's leading payment gateway.
              Your payment information is never stored on our servers.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
