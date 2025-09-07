'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { ArrowLeft, Search, DollarSign, Settings, RefreshCw, ArrowDown, ArrowUp } from 'lucide-react';
import { toast } from 'react-toastify';
import { adminAPI } from '../../../lib/api';

interface CreditTransaction {
  id: string;
  userId: string;
  userEmail: string;
  userName?: string;
  amount: number;
  type: 'purchase' | 'usage' | 'admin' | 'refund';
  description: string;
  createdAt: string;
}

export default function CreditManagement() {
  const [transactions, setTransactions] = useState<CreditTransaction[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [showSettingsModal, setShowSettingsModal] = useState(false);
  const [creditCostPerImage, setCreditCostPerImage] = useState<number | undefined>(undefined);

  // Function to fetch credit transactions
  const fetchTransactions = async () => {
    try {
      setIsLoading(true);
      const response = await adminAPI.getCreditTransactions();
      
      if (response.data.success && response.data.data) {
        const transactionsData = response.data.data.transactions.map((transaction: any) => ({
          ...transaction,
          type: transaction.type as 'purchase' | 'usage' | 'admin' | 'refund'
        }));
        setTransactions(transactionsData);
      } else {
        throw new Error('Failed to fetch credit transactions');
      }
    } catch (error) {
      console.error('Error fetching transactions:', error);
      toast.error('Failed to fetch credit transactions');
    } finally {
      setIsLoading(false);
    }
  };

  // Function to fetch credit settings
  const fetchCreditSettings = async () => {
    try {
      const response = await adminAPI.getCreditSettings();
      
      if (response.data.success && response.data.data) {
        setCreditCostPerImage(response.data.data.settings.creditCostPerImage);
      } else {
        // Fallback to default value if response structure is unexpected
        setCreditCostPerImage(1);
      }
    } catch (error) {
      console.error('Error fetching credit settings:', error);
      toast.error('Failed to fetch credit settings');
    }
  };

  // Function to update credit settings
  const updateCreditSettings = async () => {
    try {
      const response = await adminAPI.updateCreditSettings({
        creditCostPerImage: creditCostPerImage || 1,
        creditCostPerEnhancement: 1,
        initialUserCredits: 10,
        enablePromptEnhancement: true
      });
      
      if (response.data.success) {
        toast.success('Credit settings updated successfully');
        setShowSettingsModal(false);
        // Refresh the credit settings to show the updated value
        fetchCreditSettings();
      } else {
        throw new Error('Failed to update credit settings');
      }
    } catch (error) {
      console.error('Error updating credit settings:', error);
      toast.error('Failed to update credit settings');
    }
  };

  useEffect(() => {
    fetchTransactions();
    fetchCreditSettings();
  }, []);
  const filteredTransactions = transactions.filter(transaction => 
    transaction.userEmail.toLowerCase().includes(searchTerm.toLowerCase()) ||
    transaction.description.toLowerCase().includes(searchTerm.toLowerCase())
  );

  useEffect(() => {
    fetchTransactions();
    fetchCreditSettings();
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
              <h1 className="ml-8 text-xl font-bold text-white">Credit Management</h1>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h2 className="text-2xl font-bold text-white">Credit Transactions</h2>
            <p className="text-gray-400 mt-1">Cost per image generation: {creditCostPerImage || 1} credits</p>
          </div>
          
          <div className="flex items-center space-x-4">
            <button
              onClick={() => setShowSettingsModal(true)}
              className="flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white"
            >
              <Settings size={16} className="mr-2" />
              Credit Settings
            </button>
            
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={18} />
              <input
                type="text"
                placeholder="Search transactions..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
              />
            </div>
          </div>
        </div>

        <div className="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div className="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <h3 className="text-gray-300 text-sm font-medium mb-2">Total Credits Consumed</h3>
            <p className="text-2xl font-bold text-white">
              {transactions
                .filter(t => t.type === 'usage')
                .reduce((sum, t) => sum + Math.abs(t.amount), 0)}
            </p>
          </div>
          
          <div className="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <h3 className="text-gray-300 text-sm font-medium mb-2">Total Credits Added</h3>
            <p className="text-2xl font-bold text-white">
              {transactions
                .filter(t => ['purchase', 'admin'].includes(t.type))
                .reduce((sum, t) => sum + t.amount, 0)}
            </p>
          </div>
          
          <div className="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <h3 className="text-gray-300 text-sm font-medium mb-2">Images Generated</h3>
            <p className="text-2xl font-bold text-white">
              {(creditCostPerImage || 1) > 0 ? Math.floor(transactions
                .filter(t => t.type === 'usage')
                .reduce((sum, t) => sum + Math.abs(t.amount), 0) / (creditCostPerImage || 1)) : 0}
            </p>
          </div>
          
          <div className="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <h3 className="text-gray-300 text-sm font-medium mb-2">Credit Cost per Image</h3>
            <p className="text-2xl font-bold text-white">{creditCostPerImage || 1}</p>
          </div>
        </div>

        {isLoading ? (
          <div className="flex justify-center items-center h-64">
            <RefreshCw size={24} className="animate-spin text-purple-500" />
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full table-auto">
              <thead>
                <tr className="bg-gray-800 text-left">
                  <th className="px-4 py-3 text-gray-300 font-medium">User</th>
                  <th className="px-4 py-3 text-gray-300 font-medium">Type</th>
                  <th className="px-4 py-3 text-gray-300 font-medium">Amount</th>
                  <th className="px-4 py-3 text-gray-300 font-medium">Description</th>
                  <th className="px-4 py-3 text-gray-300 font-medium">Date</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-700">
                {filteredTransactions.map(transaction => (
                  <tr key={transaction.id} className="hover:bg-gray-800">
                    <td className="px-4 py-3">
                      <div>
                        <div className="font-medium text-white">{transaction.userName || 'Unnamed'}</div>
                        <div className="text-sm text-gray-300">{transaction.userEmail}</div>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        transaction.type === 'purchase'
                          ? 'bg-green-100 text-green-800'
                          : transaction.type === 'usage'
                          ? 'bg-red-100 text-red-800'
                          : transaction.type === 'admin'
                          ? 'bg-purple-100 text-purple-800'
                          : 'bg-blue-100 text-blue-800'
                      }`}>
                        {transaction.type}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <span className={`font-medium flex items-center ${
                        transaction.amount > 0 ? 'text-green-400' : 'text-red-400'
                      }`}>
                        {transaction.amount > 0 ? (
                          <ArrowUp size={16} className="mr-1" />
                        ) : (
                          <ArrowDown size={16} className="mr-1" />
                        )}
                        {Math.abs(transaction.amount)}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-gray-300">{transaction.description}</td>
                    <td className="px-4 py-3 text-gray-300">
                      {new Date(transaction.createdAt).toLocaleString()}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Credit Settings Modal */}
      {showSettingsModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h3 className="text-xl font-bold text-white mb-4">Credit Settings</h3>
            
            <div className="mb-4">
              <label htmlFor="creditCost" className="block text-sm font-medium text-gray-300 mb-2">
                Credits per Image Generation
              </label>
              <input
                type="number"
                id="creditCost"
                value={creditCostPerImage || 1}
                onChange={(e) => setCreditCostPerImage(parseInt(e.target.value) || 1)}
                min="1"
                className="w-full p-2 bg-gray-700 border border-gray-600 rounded text-white"
              />
              <p className="text-sm text-gray-400 mt-2">
                This is the number of credits that will be deducted each time a user generates an image.
              </p>
            </div>
            
            <div className="flex justify-end space-x-3">
              <button
                onClick={() => setShowSettingsModal(false)}
                className="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded"
              >
                Cancel
              </button>
              <button
                onClick={updateCreditSettings}
                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded"
              >
                Save Changes
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
