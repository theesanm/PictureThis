import React, { useState } from 'react'
import { useAuth } from '../contexts/AuthContext'
import { User, Mail, Save } from 'lucide-react'

const ProfileSimple = () => {
  const { user } = useAuth()
  const [formData, setFormData] = useState({
    fullName: user?.fullName || '',
    email: user?.email || ''
  })

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    // Just prevent default for now - no API call
    console.log('Profile form submitted:', formData)
  }

  if (!user) {
    return (
      <div className="flex items-center justify-center py-12">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
      </div>
    )
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-2xl mx-auto">
        <div className="bg-gray-800 rounded-lg border border-gray-700 p-6">
          <div className="mb-6">
            <h1 className="text-2xl font-bold text-white">Profile Settings</h1>
            <p className="text-gray-300 mt-1">Manage your account information</p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <label htmlFor="fullName" className="block text-sm font-medium text-gray-300 mb-2">
                Full Name
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <User className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  type="text"
                  id="fullName"
                  name="fullName"
                  value={formData.fullName}
                  onChange={handleChange}
                  className="block w-full pl-10 pr-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  placeholder="Enter your full name"
                />
              </div>
            </div>

            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-300 mb-2">
                Email Address
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Mail className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  type="email"
                  id="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  className="block w-full pl-10 pr-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  placeholder="Enter your email"
                />
              </div>
            </div>

            <div className="pt-4">
              <button
                type="submit"
                className="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-800 flex items-center justify-center space-x-2"
              >
                <Save className="h-4 w-4" />
                <span>Save Changes</span>
              </button>
            </div>
          </form>

          {/* Account Info */}
          <div className="mt-8 pt-6 border-t border-gray-700">
            <h3 className="text-lg font-medium text-white mb-4">Account Information</h3>
            <div className="space-y-3">
              <div className="flex justify-between">
                <span className="text-gray-300">Member since:</span>
                <span className="font-medium text-white">
                  {user.createdAt ? new Date(user.createdAt).toLocaleDateString() : 'N/A'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-300">Account ID:</span>
                <span className="font-medium font-mono text-sm text-white">{user.id}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default ProfileSimple
