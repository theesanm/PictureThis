import React, { createContext, useContext, useState, useEffect } from 'react'
import { authAPI, userAPI, creditsAPI } from '../utils/api'
import toast from 'react-hot-toast'

const AuthContext = createContext()

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}

const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)
  const [credits, setCredits] = useState(0)

  useEffect(() => {
    const token = localStorage.getItem('token')
    
    // Clear any old/invalid auth state first
    if (token && token.includes('demo')) {
      localStorage.clear()
      setLoading(false)
      return
    }
    
    if (token) {
      loadUserData()
    } else {
      setLoading(false)
    }
  }, [])

  const loadUserData = async () => {
    try {
      const [profileRes, creditsRes] = await Promise.all([
        userAPI.getProfile(),
        creditsAPI.getBalance()
      ])

      setUser(profileRes.data.data.user)
      setCredits(creditsRes.data.data.credits)
    } catch (error) {
      console.error('Failed to load user data:', error)
      localStorage.removeItem('token')
      setUser(null)
      setCredits(0)
    } finally {
      setLoading(false)
    }
  }

  const login = async (email, password) => {
    try {
      const response = await authAPI.login({ email, password })
      
      // Check if login was successful
      if (!response.data.success) {
        // Handle email verification requirement
        if (response.data.requiresVerification) {
          return { 
            success: false, 
            requiresVerification: true,
            message: response.data.message,
            email: response.data.email
          }
        }
        
        // Handle other login failures
        return {
          success: false,
          message: response.data.message || 'Login failed'
        }
      }

      // Login was successful
      const { user, token } = response.data.data

      localStorage.setItem('token', token)
      setUser(user)
      await loadUserData() // Reload to get credits
      toast.success('Login successful!')
      return { success: true }
    } catch (error) {
      const errorData = error.response?.data
      
      // Handle verification requirement from error response
      if (errorData?.requiresVerification) {
        return {
          success: false,
          requiresVerification: true,
          message: errorData.message,
          email: errorData.email
        }
      }

      const message = errorData?.message || 'Login failed'
      toast.error(message)
      return { success: false, message }
    }
  }

  const register = async (email, password, fullName) => {
    try {
      const response = await authAPI.register({ email, password, fullName })
      
      // Handle email verification requirement
      if (response.data.requiresVerification) {
        return {
          success: true,
          requiresVerification: true,
          message: response.data.message,
          user: response.data.data.user
        }
      }

      const { user, token } = response.data.data

      localStorage.setItem('token', token)
      setUser(user)
      setCredits(50) // New users get 50 credits
      toast.success('Registration successful!')
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Registration failed'
      toast.error(message)
      return { success: false, message }
    }
  }

  const logout = () => {
    localStorage.removeItem('token')
    localStorage.clear() // Clear all localStorage just to be sure
    setUser(null)
    setCredits(0)
    toast.success('Logged out successfully')
  }

  const updateProfile = async (userData) => {
    try {
      const response = await userAPI.updateProfile(userData)
      setUser(response.data.data.user)
      toast.success('Profile updated successfully!')
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Profile update failed'
      toast.error(message)
      return { success: false, message }
    }
  }

  const refreshCredits = async () => {
    try {
      const response = await creditsAPI.getBalance()
      setCredits(response.data.data.credits)
    } catch (error) {
      console.error('Failed to refresh credits:', error)
    }
  }

  const value = {
    user,
    credits,
    loading,
    login,
    register,
    logout,
    updateProfile,
    refreshCredits,
    isAuthenticated: !!user,
  }

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  )
}

export default AuthProvider
