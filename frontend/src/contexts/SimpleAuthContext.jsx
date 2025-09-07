import React, { createContext, useContext, useState, useEffect } from 'react'

const AuthContext = createContext({})

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}

export default function SimpleAuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [isLoading, setIsLoading] = useState(true)

  // Check for existing session on mount
  useEffect(() => {
    const checkAuth = () => {
      const savedUser = localStorage.getItem('pictureThisUser')
      if (savedUser) {
        try {
          const userData = JSON.parse(savedUser)
          setUser(userData)
          setIsAuthenticated(true)
        } catch (error) {
          console.error('Error parsing saved user data:', error)
          localStorage.removeItem('pictureThisUser')
        }
      }
      setIsLoading(false)
    }

    // Simulate async auth check
    setTimeout(checkAuth, 300)
  }, [])

  const login = async (email, password) => {
    setIsLoading(true)
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      if (email && password) {
        const userData = {
          id: '1',
          email: email,
          name: email.split('@')[0],
          avatar: `https://ui-avatars.com/api/?name=${email.split('@')[0]}&background=2563eb&color=fff`
        }
        
        setUser(userData)
        setIsAuthenticated(true)
        localStorage.setItem('pictureThisUser', JSON.stringify(userData))
        
        setIsLoading(false)
        return { success: true }
      } else {
        throw new Error('Email and password are required')
      }
    } catch (error) {
      setIsLoading(false)
      return { success: false, error: error.message }
    }
  }

  const register = async (email, password, name) => {
    setIsLoading(true)
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      if (email && password && name) {
        const userData = {
          id: '1',
          email: email,
          name: name,
          avatar: `https://ui-avatars.com/api/?name=${name}&background=059669&color=fff`
        }
        
        setUser(userData)
        setIsAuthenticated(true)
        localStorage.setItem('pictureThisUser', JSON.stringify(userData))
        
        setIsLoading(false)
        return { success: true }
      } else {
        throw new Error('All fields are required')
      }
    } catch (error) {
      setIsLoading(false)
      return { success: false, error: error.message }
    }
  }

  const logout = () => {
    setUser(null)
    setIsAuthenticated(false)
    localStorage.removeItem('pictureThisUser')
  }

  const value = {
    user,
    isAuthenticated,
    isLoading,
    login,
    register,
    logout
  }

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  )
}
