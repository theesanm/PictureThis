'use client';

import React, { useState, useEffect } from 'react';
import { useAuth } from '@/lib/auth-context';
import { publicSettingsAPI } from '@/lib/api';
import { toast } from 'react-toastify';
import { CreditCard, Package, CheckCircle, Clock, DollarSign } from 'lucide-react';

interface CreditPackage {
  id: string;
  credits: number;
  price: number;
  name: string;
  savings?: string;
}

interface Transaction {
  id: number;
  amount: number;
  type: string;
  description: string;
  created_at: string;
}

export default function Credits() {
  const { user, updateUserCredits } = useAuth();
  const [packages, setPackages] = useState<CreditPackage[]>([]);
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState<string | null>(null);
  const [creditCostPerImage, setCreditCostPerImage] = useState(1);
  const [creditDescriptionText, setCreditDescriptionText] = useState('1 credit = 1 image generation');

  // Credit packages with fixed pricing (fallback)
  const defaultPackages: CreditPackage[] = [
    { id: 'small', credits: 50, price: 200.00, name: '50 Credits' },
    { id: 'medium', credits: 75, price: 250.00, name: '75 Credits (10% off)' },
    { id: 'large', credits: 125, price: 300.00, name: '125 Credits (20% off)' },
    { id: 'premium', credits: 200, price: 350.00, name: '200 Credits (30% off)' }
  ];

  useEffect(() => {
    if (user) {
      fetchCreditData();
    } else {
      setTransactions([]);
      setLoading(false);
    }
  }, [user]);

  const fetchCreditData = async () => {
    try {
      setLoading(true);

      // Check if user is authenticated
      if (!user) {
        console.log('User not authenticated, skipping data fetch');
        setTransactions([]);
        return;
      }

      // Fetch available packages
      const packagesResponse = await fetch('/api/credits/packages');
      if (packagesResponse.ok) {
        const packagesData = await packagesResponse.json();
        setPackages(Object.entries(packagesData.data).map(([id, pkg]: [string, any]) => ({
          id,
          ...pkg
        })));
      } else {
        setPackages(defaultPackages);
      }

      // Fetch system settings for credit description
      const settingsResponse = await publicSettingsAPI.getSettings();
      if (settingsResponse.data.success && settingsResponse.data.data?.settings?.creditCostPerImage) {
        const cost = settingsResponse.data.data.settings.creditCostPerImage;
        setCreditCostPerImage(cost);
        
        // Calculate dynamic description text based on credit cost
        if (cost === 1) {
          setCreditDescriptionText('1 credit = 1 image generation');
        } else {
          setCreditDescriptionText(`${cost} credits = 1 image generation`);
        }
      }

      // Fetch transaction history
      const token = localStorage.getItem('token');
      if (!token) {
        console.log('No authentication token found');
        setTransactions([]);
        return;
      }

      const historyResponse = await fetch('/api/credits/history', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      if (historyResponse.ok) {
        const historyData = await historyResponse.json();
        console.log('Transaction history response:', historyData);
        // Handle different response structures
        const transactionsData = historyData.data?.transactions || historyData.data || [];
        setTransactions(Array.isArray(transactionsData) ? transactionsData : []);
      } else if (historyResponse.status === 401) {
        console.log('Authentication failed, user may need to log in again');
        setTransactions([]);
      } else {
        console.error('Failed to fetch transaction history:', historyResponse.status, await historyResponse.text());
        setTransactions([]);
      }
    } catch (error) {
      console.error('Error fetching credit data:', error);
      setPackages(defaultPackages);
      setTransactions([]);
    } finally {
      setLoading(false);
    }
  };

  const handlePurchase = async (packageId: string) => {
    try {
      setProcessing(packageId);

      const response = await fetch('/api/credits/payfast/initiate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ packageId })
      });

      if (response.ok) {
        const data = await response.json();

        // Create and submit PayFast form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = data.data.paymentUrl;

        Object.entries(data.data.payfastData).forEach(([key, value]) => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = key;
          input.value = value as string;
          form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
      } else {
        const errorData = await response.json();
        toast.error(errorData.message || 'Failed to initiate payment');
      }
    } catch (error) {
      console.error('Purchase error:', error);
      toast.error('Failed to process payment');
    } finally {
      setProcessing(null);
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-ZA', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500"></div>
      </div>
    );
  }

  if (!user) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <h2 className="text-2xl font-bold mb-4">Authentication Required</h2>
          <p className="text-gray-400 mb-6">Please log in to access your credits and transaction history.</p>
          <a href="/login" className="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            Log In
          </a>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto">
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-2">Purchase Credits</h1>
        <p className="text-gray-400">Buy credits to generate AI images and enhance prompts</p>
      </div>

      {/* Current Balance */}
      <div className="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8">
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-xl font-semibold mb-2">Current Balance</h2>
            <div className="flex items-center gap-2">
              <CreditCard className="text-purple-400" size={24} />
              <span className="text-2xl font-bold text-purple-400">{user?.credits || 0}</span>
              <span className="text-gray-400">credits</span>
            </div>
          </div>
          <div className="text-right">
            <p className="text-sm text-gray-400">{creditDescriptionText}</p>
            <p className="text-sm text-gray-400">Credits never expire</p>
          </div>
        </div>
      </div>

      {/* Credit Packages */}
      <div className="mb-8">
        <h2 className="text-2xl font-bold mb-6">Choose Your Package</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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

              <button
                onClick={() => handlePurchase(pkg.id)}
                disabled={processing === pkg.id}
                className="w-full bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium py-3 px-4 rounded-lg transition-all flex items-center justify-center gap-2"
              >
                {processing === pkg.id ? (
                  <>
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                    Processing...
                  </>
                ) : (
                  <>
                    Buy Now
                  </>
                )}
              </button>
            </div>
          ))}
        </div>
      </div>

      {/* Transaction History */}
      <div className="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <h2 className="text-2xl font-bold mb-6">Transaction History</h2>

        {(!transactions || transactions.length === 0) ? (
          <div className="text-center py-8">
            <Clock className="mx-auto text-gray-600 mb-4" size={48} />
            <p className="text-gray-400">No transactions yet</p>
            <p className="text-sm text-gray-500 mt-2">Your credit purchases will appear here</p>
          </div>
        ) : (
          <div className="space-y-4">
            {transactions.filter(transaction => transaction && transaction.id).map((transaction) => (
              <div key={transaction.id || Math.random()} className="flex items-center justify-between p-4 bg-gray-700 rounded-lg">
                <div className="flex items-center gap-3">
                  <div className={`p-2 rounded-full ${transaction.type === 'purchase' ? 'bg-green-600' : 'bg-blue-600'}`}>
                    {transaction.type === 'purchase' ? (
                      <CheckCircle size={16} className="text-white" />
                    ) : (
                      <CreditCard size={16} className="text-white" />
                    )}
                  </div>
                  <div>
                    <p className="font-medium">{transaction.description || 'Transaction'}</p>
                    <p className="text-sm text-gray-400">{transaction.created_at ? formatDate(transaction.created_at) : 'Unknown date'}</p>
                  </div>
                </div>
                <div className="text-right">
                  <p className={`font-semibold ${transaction.amount > 0 ? 'text-green-400' : 'text-red-400'}`}>
                    {transaction.amount !== undefined ? (transaction.amount > 0 ? '+' : '') + transaction.amount : '0'} credits
                  </p>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Payment Security Notice */}
      <div className="mt-8 bg-blue-900/20 border border-blue-600 rounded-lg p-4">
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
