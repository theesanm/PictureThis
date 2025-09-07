import React from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { Image, User, LogOut, CreditCard } from 'lucide-react'

const Header = () => {
  const { user, credits, logout, isAuthenticated } = useAuth()
  const navigate = useNavigate()

  const handleLogout = () => {
    logout()
    navigate('/')
  }

  return (
    <header style={{
      backgroundColor: '#1e293b',
      borderBottom: '1px solid #334155',
      position: 'sticky',
      top: 0,
      zIndex: 50
    }}>
      <div className="container">
        <div className="flex items-center justify-between" style={{ height: '4rem' }}>
          {/* Logo */}
          <Link to="/" className="flex items-center gap-2" style={{ textDecoration: 'none', color: 'white' }}>
            <div style={{
              padding: '0.5rem',
              background: 'linear-gradient(135deg, #9333ea, #ec4899)',
              borderRadius: '0.5rem',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}>
              <Image size={24} color="white" />
            </div>
            <span className="text-xl font-bold">Picture This</span>
          </Link>

          {/* Navigation */}
          <nav className="flex items-center gap-4">
            {isAuthenticated ? (
              <>
                <Link to="/dashboard" className="text-gray-300 font-medium px-3 py-2 rounded-lg" style={{ textDecoration: 'none' }}>
                  Dashboard
                </Link>
                <Link to="/generate" className="text-gray-300 font-medium px-3 py-2 rounded-lg" style={{ textDecoration: 'none' }}>
                  Generate
                </Link>

                {/* Credits Display */}
                <div className="flex items-center gap-2" style={{
                  background: 'rgba(147, 51, 234, 0.2)',
                  padding: '0.5rem 1rem',
                  borderRadius: '9999px',
                  border: '1px solid rgba(147, 51, 234, 0.3)',
                  fontSize: '0.875rem',
                  fontWeight: '500',
                  color: '#c084fc'
                }}>
                  <CreditCard size={16} />
                  <span>{credits} credits</span>
                </div>

                {/* User Menu */}
                <div className="flex items-center gap-3">
                  <Link to="/profile" className="flex items-center gap-2 text-gray-300 font-medium px-3 py-2 rounded-lg" style={{ textDecoration: 'none' }}>
                    <User size={16} />
                    <span>{user?.fullName}</span>
                  </Link>
                  <button
                    onClick={handleLogout}
                    className="text-gray-400 p-2 rounded-lg"
                    style={{ background: 'transparent', border: 'none', cursor: 'pointer' }}
                  >
                    <LogOut size={16} />
                  </button>
                </div>
              </>
            ) : (
              <div className="flex items-center gap-3">
                <Link to="/login" className="btn btn-secondary">
                  Login
                </Link>
                <Link to="/register" className="btn btn-primary">
                  Sign Up
                </Link>
              </div>
            )}
          </nav>
        </div>
      </div>
    </header>
  )
}

export default Header
