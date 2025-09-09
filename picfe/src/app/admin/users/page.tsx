'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { ArrowLeft, Search, Edit, Trash, AlertTriangle, Check, X, RefreshCw, Shield, CreditCard } from 'lucide-react';
import { useAuth } from '@/lib/auth-context';
import { toast } from 'react-toastify';
import { adminAPI } from '../../../lib/api';

// Define API types for admin operations
interface User {
  id: string;
  email: string;
  fullName?: string;
  isAdmin: boolean;
  credits: number;
  isVerified: boolean;
  createdAt: string;
}

export default function UserManagement() {
  const { user: currentUser } = useAuth();
  const [users, setUsers] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [showAddCreditsModal, setShowAddCreditsModal] = useState(false);
  const [creditsToAdd, setCreditsToAdd] = useState(0);
  const [showResetPermissionModal, setShowResetPermissionModal] = useState(false);

  // Function to fetch all users
  const fetchUsers = async () => {
    try {
      setIsLoading(true);
      const response = await adminAPI.getUsers();
      
      if (response.data.success && response.data.data) {
        const usersData = response.data.data.users.map((user: any) => ({
          ...user,
          isVerified: user.emailVerified
        }));
        setUsers(usersData);
      } else {
        throw new Error('Failed to fetch users');
      }
    } catch (error) {
      console.error('Error fetching users:', error);
      toast.error('Failed to fetch users');
    } finally {
      setIsLoading(false);
    }
  };

  // Function to add credits to a user
  const handleAddCredits = async () => {
    if (!selectedUser) return;
    
    try {
      const response = await fetch(`/api/admin/users/${selectedUser.id}/credits`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ credits: creditsToAdd })
      });
      
      if (!response.ok) {
        throw new Error('Failed to add credits');
      }
      
      const data = await response.json();
      
      // Update local state
      setUsers(users.map(u => 
        u.id === selectedUser.id 
          ? { ...u, credits: u.credits + creditsToAdd } 
          : u
      ));
      
      toast.success(`Successfully added ${creditsToAdd} credits to ${selectedUser.email}`);
      setShowAddCreditsModal(false);
      setCreditsToAdd(0);
    } catch (error) {
      console.error('Error adding credits:', error);
      toast.error('Failed to add credits');
    }
  };

  // Function to reset user permissions
  const handleResetPermissions = async () => {
    if (!selectedUser) return;
    
    try {
      const response = await fetch(`/api/admin/users/${selectedUser.id}/permissions/reset`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      
      if (!response.ok) {
        throw new Error('Failed to reset permissions');
      }
      
      toast.success(`Successfully reset permissions for ${selectedUser.email}`);
      setShowResetPermissionModal(false);
    } catch (error) {
      console.error('Error resetting permissions:', error);
      toast.error('Failed to reset permissions');
    }
  };

  // Filter users based on search term
  const filteredUsers = users.filter(user => 
    user.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (user.fullName && user.fullName.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  useEffect(() => {
    fetchUsers();
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
              <h1 className="ml-8 text-xl font-bold text-white">User Management</h1>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-2xl font-bold text-white">All Users</h2>
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={18} />
            <input
              type="text"
              placeholder="Search users..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
            />
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
                  <th className="px-4 py-3 text-gray-300 font-medium">Role</th>
                  <th className="px-4 py-3 text-gray-300 font-medium">Credits</th>
                  <th className="px-4 py-3 text-gray-300 font-medium">Status</th>
                  <th className="px-4 py-3 text-gray-300 font-medium">Joined</th>
                  <th className="px-4 py-3 text-gray-300 font-medium">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-700">
                {filteredUsers.map(user => (
                  <tr key={user.id} className="hover:bg-gray-800">
                    <td className="px-4 py-3">
                      <div>
                        <div className="font-medium text-white">{user.fullName || 'Unnamed'}</div>
                        <div className="text-sm text-gray-300">{user.email}</div>
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        user.isAdmin ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'
                      }`}>
                        {user.isAdmin ? 'Admin' : 'User'}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-white">{user.credits}</td>
                    <td className="px-4 py-3">
                      {user.isVerified ? (
                        <span className="inline-flex items-center text-green-400">
                          <Check size={16} className="mr-1" /> Verified
                        </span>
                      ) : (
                        <span className="inline-flex items-center text-yellow-400">
                          <AlertTriangle size={16} className="mr-1" /> Pending
                        </span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-gray-300">
                      {new Date(user.createdAt).toLocaleDateString()}
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex space-x-2">
                        <button 
                          onClick={() => {
                            setSelectedUser(user);
                            setShowAddCreditsModal(true);
                          }}
                          className="p-1 bg-gray-700 hover:bg-gray-600 rounded text-green-400"
                          title="Add Credits"
                        >
                          <CreditCard size={16} />
                        </button>
                        <button 
                          onClick={() => {
                            setSelectedUser(user);
                            setShowResetPermissionModal(true);
                          }}
                          className="p-1 bg-gray-700 hover:bg-gray-600 rounded text-yellow-400"
                          title="Reset Permissions"
                        >
                          <Shield size={16} />
                        </button>
                        {user.id !== currentUser?.id && (
                          <>
                            <button 
                              className="p-1 bg-gray-700 hover:bg-gray-600 rounded text-blue-400"
                              title="Edit User"
                            >
                              <Edit size={16} />
                            </button>
                            <button 
                              className="p-1 bg-gray-700 hover:bg-gray-600 rounded text-red-400"
                              title="Delete User"
                            >
                              <Trash size={16} />
                            </button>
                          </>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Add Credits Modal */}
      {showAddCreditsModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h3 className="text-xl font-bold text-white mb-4">Add Credits</h3>
            <p className="text-gray-300 mb-4">
              Adding credits to <span className="font-medium text-white">{selectedUser?.email}</span>
            </p>
            <div className="mb-4">
              <label htmlFor="credits" className="block text-sm font-medium text-gray-300 mb-2">
                Credits Amount
              </label>
              <input
                type="number"
                id="credits"
                value={creditsToAdd}
                onChange={(e) => setCreditsToAdd(parseInt(e.target.value) || 0)}
                min="1"
                className="w-full p-2 bg-gray-700 border border-gray-600 rounded text-white"
              />
            </div>
            <div className="flex justify-end space-x-3">
              <button
                onClick={() => setShowAddCreditsModal(false)}
                className="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded"
              >
                Cancel
              </button>
              <button
                onClick={handleAddCredits}
                disabled={creditsToAdd <= 0}
                className={`px-4 py-2 bg-green-600 text-white rounded ${
                  creditsToAdd <= 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'
                }`}
              >
                Add Credits
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Reset Permissions Modal */}
      {showResetPermissionModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h3 className="text-xl font-bold text-white mb-4">Reset Permissions</h3>
            <p className="text-gray-300 mb-4">
              Are you sure you want to reset all permissions for <span className="font-medium text-white">{selectedUser?.email}</span>?
            </p>
            <p className="text-gray-400 text-sm mb-6">
              This will require the user to accept all terms and permissions again the next time they use these features.
            </p>
            <div className="flex justify-end space-x-3">
              <button
                onClick={() => setShowResetPermissionModal(false)}
                className="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded"
              >
                Cancel
              </button>
              <button
                onClick={handleResetPermissions}
                className="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded"
              >
                Reset Permissions
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
