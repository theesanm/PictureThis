import React from 'react'
import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom'
import SimpleAuthProvider, { useAuth } from './contexts/SimpleAuthContext'
import ImageGeneration from './pages/ImageGeneration'
import Gallery from './pages/Gallery'

// Enhanced components with authentication
function HomePage() {
  const { isAuthenticated, user } = useAuth()
  
  return (
    <div style={{ padding: '20px', textAlign: 'center', backgroundColor: '#0f172a', color: '#f8fafc', minHeight: '100vh' }}>
      <h1 style={{ color: '#60a5fa', marginBottom: '2rem' }}>🎨 Picture This - Home</h1>
      {isAuthenticated ? (
        <div>
          <p style={{ color: '#10b981', marginBottom: '1rem' }}>
            Welcome back, {user?.name || user?.email}! 👋
          </p>
          <p style={{ color: '#cbd5e1', marginBottom: '2rem' }}>
            Ready to create some amazing AI artwork?
          </p>
        </div>
      ) : (
        <p style={{ color: '#cbd5e1', marginBottom: '2rem' }}>
          Sign in to start generating amazing AI images
        </p>
      )}
      
      <div style={{ display: 'flex', gap: '1rem', justifyContent: 'center', flexWrap: 'wrap' }}>
        {isAuthenticated ? (
          <>
            <Link to="/generate" style={{ 
              backgroundColor: '#2563eb', 
              color: 'white', 
              padding: '0.75rem 1.5rem', 
              borderRadius: '0.5rem',
              textDecoration: 'none',
              fontWeight: '600'
            }}>
              🖼️ Generate Images
            </Link>
            <Link to="/gallery" style={{ 
              backgroundColor: '#7c3aed', 
              color: 'white', 
              padding: '0.75rem 1.5rem', 
              borderRadius: '0.5rem',
              textDecoration: 'none',
              fontWeight: '600'
            }}>
              🎨 My Gallery
            </Link>
            <Link to="/dashboard" style={{ 
              backgroundColor: '#059669', 
              color: 'white', 
              padding: '0.75rem 1.5rem', 
              borderRadius: '0.5rem',
              textDecoration: 'none',
              fontWeight: '600'
            }}>
              📊 Dashboard
            </Link>
          </>
        ) : (
          <>
            <Link to="/login" style={{ 
              backgroundColor: '#2563eb', 
              color: 'white', 
              padding: '0.75rem 1.5rem', 
              borderRadius: '0.5rem',
              textDecoration: 'none',
              fontWeight: '600'
            }}>
              🔑 Sign In
            </Link>
            <Link to="/register" style={{ 
              backgroundColor: '#059669', 
              color: 'white', 
              padding: '0.75rem 1.5rem', 
              borderRadius: '0.5rem',
              textDecoration: 'none',
              fontWeight: '600'
            }}>
              📝 Sign Up
            </Link>
          </>
        )}
      </div>
    </div>
  )
}

function LoginPage() {
  const { login, isLoading } = useAuth()
  const [email, setEmail] = React.useState('')
  const [password, setPassword] = React.useState('')
  const [error, setError] = React.useState('')

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    
    const result = await login(email, password)
    if (!result.success) {
      setError(result.error)
    }
  }

  return (
    <div style={{ 
      padding: '20px', 
      maxWidth: '400px', 
      margin: '0 auto', 
      backgroundColor: '#0f172a', 
      color: '#f8fafc', 
      minHeight: '100vh' 
    }}>
      <h1 style={{ color: '#60a5fa', marginBottom: '2rem', textAlign: 'center' }}>🔑 Sign In</h1>
      
      <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          style={{
            padding: '0.75rem',
            border: '1px solid #475569',
            borderRadius: '0.5rem',
            fontSize: '1rem',
            backgroundColor: '#1e293b',
            color: '#f8fafc'
          }}
          required
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          style={{
            padding: '0.75rem',
            border: '1px solid #475569',
            borderRadius: '0.5rem',
            fontSize: '1rem',
            backgroundColor: '#1e293b',
            color: '#f8fafc'
          }}
          required
        />
        
        {error && (
          <p style={{ color: '#ef4444', fontSize: '0.875rem' }}>{error}</p>
        )}
        
        <button
          type="submit"
          disabled={isLoading}
          style={{
            backgroundColor: isLoading ? '#9ca3af' : '#2563eb',
            color: 'white',
            padding: '0.75rem',
            border: 'none',
            borderRadius: '0.5rem',
            fontSize: '1rem',
            fontWeight: '600',
            cursor: isLoading ? 'not-allowed' : 'pointer'
          }}
        >
          {isLoading ? 'Signing In...' : 'Sign In'}
        </button>
      </form>
      
      <p style={{ textAlign: 'center', marginTop: '1rem', color: '#6b7280' }}>
        Don't have an account? <Link to="/register" style={{ color: '#2563eb' }}>Sign up</Link>
      </p>
      <p style={{ textAlign: 'center', marginTop: '1rem' }}>
        <Link to="/" style={{ color: '#6b7280', textDecoration: 'underline' }}>← Back to Home</Link>
      </p>
    </div>
  )
}

function RegisterPage() {
  const { register, isLoading } = useAuth()
  const [email, setEmail] = React.useState('')
  const [password, setPassword] = React.useState('')
  const [name, setName] = React.useState('')
  const [error, setError] = React.useState('')

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    
    const result = await register(email, password, name)
    if (!result.success) {
      setError(result.error)
    }
  }

  return (
    <div style={{ 
      padding: '20px', 
      maxWidth: '400px', 
      margin: '0 auto', 
      backgroundColor: '#0f172a', 
      color: '#f8fafc', 
      minHeight: '100vh' 
    }}>
      <h1 style={{ color: '#10b981', marginBottom: '2rem', textAlign: 'center' }}>📝 Sign Up</h1>
      
      <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        <input
          type="text"
          placeholder="Full Name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          style={{
            padding: '0.75rem',
            border: '1px solid #475569',
            borderRadius: '0.5rem',
            fontSize: '1rem',
            backgroundColor: '#1e293b',
            color: '#f8fafc'
          }}
          required
        />
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          style={{
            padding: '0.75rem',
            border: '1px solid #475569',
            borderRadius: '0.5rem',
            fontSize: '1rem',
            backgroundColor: '#1e293b',
            color: '#f8fafc'
          }}
          required
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          style={{
            padding: '0.75rem',
            border: '1px solid #475569',
            borderRadius: '0.5rem',
            fontSize: '1rem',
            backgroundColor: '#1e293b',
            color: '#f8fafc'
          }}
          required
        />
        
        {error && (
          <p style={{ color: '#ef4444', fontSize: '0.875rem' }}>{error}</p>
        )}
        
        <button
          type="submit"
          disabled={isLoading}
          style={{
            backgroundColor: isLoading ? '#9ca3af' : '#059669',
            color: 'white',
            padding: '0.75rem',
            border: 'none',
            borderRadius: '0.5rem',
            fontSize: '1rem',
            fontWeight: '600',
            cursor: isLoading ? 'not-allowed' : 'pointer'
          }}
        >
          {isLoading ? 'Creating Account...' : 'Sign Up'}
        </button>
      </form>
      
      <p style={{ textAlign: 'center', marginTop: '1rem', color: '#6b7280' }}>
        Already have an account? <Link to="/login" style={{ color: '#059669' }}>Sign in</Link>
      </p>
      <p style={{ textAlign: 'center', marginTop: '1rem' }}>
        <Link to="/" style={{ color: '#6b7280', textDecoration: 'underline' }}>← Back to Home</Link>
      </p>
    </div>
  )
}

function GeneratePage() {
  const { isAuthenticated, user } = useAuth()
  
  if (!isAuthenticated) {
    return (
      <div style={{ padding: '20px', textAlign: 'center' }}>
        <h1 style={{ color: '#dc2626', marginBottom: '2rem' }}>🔒 Access Denied</h1>
        <p style={{ color: '#4b5563', marginBottom: '2rem' }}>
          Please sign in to access the image generation feature
        </p>
        <Link to="/login" style={{ 
          backgroundColor: '#2563eb', 
          color: 'white', 
          padding: '0.75rem 1.5rem', 
          borderRadius: '0.5rem',
          textDecoration: 'none',
          fontWeight: '600'
        }}>
          Sign In
        </Link>
      </div>
    )
  }

  return (
    <div style={{ padding: '20px', textAlign: 'center' }}>
      <h1 style={{ color: '#059669', marginBottom: '2rem' }}>🖼️ Generate Images</h1>
      <p style={{ color: '#4b5563', marginBottom: '2rem' }}>
        Welcome {user?.name}! AI Image Generation will be implemented here
      </p>
      <div style={{
        backgroundColor: 'white',
        padding: '2rem',
        borderRadius: '0.5rem',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        maxWidth: '600px',
        margin: '0 auto'
      }}>
        <p style={{ color: '#6b7280', marginBottom: '1rem' }}>🚧 Coming Soon:</p>
        <ul style={{ color: '#4b5563', textAlign: 'left', lineHeight: '1.75' }}>
          <li>Text-to-image generation</li>
          <li>Style selection</li>
          <li>Image quality options</li>
          <li>Download & save functionality</li>
        </ul>
      </div>
      <p style={{ marginTop: '2rem' }}>
        <Link to="/" style={{ color: '#2563eb', textDecoration: 'underline' }}>← Back to Home</Link>
      </p>
    </div>
  )
}

function DashboardPage() {
  const { isAuthenticated, user } = useAuth()
  
  if (!isAuthenticated) {
    return (
      <div style={{ 
        padding: '20px', 
        textAlign: 'center', 
        backgroundColor: '#0f172a', 
        color: '#f8fafc', 
        minHeight: '100vh' 
      }}>
        <h1 style={{ color: '#ef4444', marginBottom: '2rem' }}>🔒 Access Denied</h1>
        <p style={{ color: '#cbd5e1', marginBottom: '2rem' }}>
          Please sign in to access your dashboard
        </p>
        <Link to="/login" style={{ 
          backgroundColor: '#2563eb', 
          color: 'white', 
          padding: '0.75rem 1.5rem', 
          borderRadius: '0.5rem',
          textDecoration: 'none',
          fontWeight: '600'
        }}>
          Sign In
        </Link>
      </div>
    )
  }

  return (
    <div style={{ 
      padding: '20px', 
      textAlign: 'center', 
      backgroundColor: '#0f172a', 
      color: '#f8fafc', 
      minHeight: '100vh' 
    }}>
      <h1 style={{ color: '#60a5fa', marginBottom: '2rem' }}>📊 Dashboard</h1>
      <p style={{ color: '#cbd5e1', marginBottom: '2rem' }}>
        Welcome to your dashboard, {user?.name}!
      </p>
      <div style={{
        backgroundColor: '#1e293b',
        padding: '2rem',
        borderRadius: '0.5rem',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.3)',
        border: '1px solid #334155',
        maxWidth: '600px',
        margin: '0 auto'
      }}>
        <p style={{ color: '#94a3b8', marginBottom: '1rem' }}>📈 Your Stats:</p>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: '1rem' }}>
          <div style={{ backgroundColor: '#334155', padding: '1rem', borderRadius: '0.5rem', border: '1px solid #475569' }}>
            <p style={{ fontSize: '2rem', color: '#e2e8f0' }}>0</p>
            <p style={{ color: '#94a3b8' }}>Images Generated</p>
          </div>
          <div style={{ backgroundColor: '#334155', padding: '1rem', borderRadius: '0.5rem', border: '1px solid #475569' }}>
            <p style={{ fontSize: '2rem', color: '#e2e8f0' }}>100</p>
            <p style={{ color: '#94a3b8' }}>Credits Remaining</p>
          </div>
        </div>
      </div>
      <p style={{ marginTop: '2rem' }}>
        <Link to="/" style={{ color: '#60a5fa', textDecoration: 'underline' }}>← Back to Home</Link>
      </p>
    </div>
  )
}

// Enhanced Navigation with Auth
function Navigation() {
  const { isAuthenticated, user, logout } = useAuth()
  
  return (
    <nav style={{
      backgroundColor: '#1e293b',
      borderBottom: '1px solid #475569',
      padding: '1rem 0'
    }}>
      <div style={{ 
        maxWidth: '1200px', 
        margin: '0 auto', 
        padding: '0 1rem',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center'
      }}>
        <Link to="/" style={{ 
          fontSize: '1.5rem', 
          fontWeight: 'bold', 
          color: '#60a5fa',
          textDecoration: 'none'
        }}>
          Picture This
        </Link>
        
        <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
          <Link to="/" style={{ color: '#cbd5e1', textDecoration: 'none' }}>Home</Link>
          
          {isAuthenticated ? (
            <>
              <Link to="/generate" style={{ color: '#cbd5e1', textDecoration: 'none' }}>Generate</Link>
              <Link to="/gallery" style={{ color: '#cbd5e1', textDecoration: 'none' }}>Gallery</Link>
              <Link to="/dashboard" style={{ color: '#cbd5e1', textDecoration: 'none' }}>Dashboard</Link>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                {user?.avatar && (
                  <img 
                    src={user.avatar} 
                    alt={user.name} 
                    style={{ width: '32px', height: '32px', borderRadius: '50%' }}
                  />
                )}
                <span style={{ color: '#cbd5e1' }}>{user?.name}</span>
                <button 
                  onClick={logout}
                  style={{
                    backgroundColor: 'transparent',
                    border: '1px solid #475569',
                    color: '#94a3b8',
                    padding: '0.25rem 0.75rem',
                    borderRadius: '0.375rem',
                    fontSize: '0.875rem',
                    cursor: 'pointer'
                  }}
                >
                  Logout
                </button>
              </div>
            </>
          ) : (
            <>
              <Link to="/login" style={{ color: '#cbd5e1', textDecoration: 'none' }}>Sign In</Link>
              <Link to="/register" style={{ 
                backgroundColor: '#2563eb',
                color: 'white',
                padding: '0.5rem 1rem',
                borderRadius: '0.375rem',
                textDecoration: 'none',
                fontSize: '0.875rem',
                fontWeight: '500'
              }}>
                Sign Up
              </Link>
            </>
          )}
        </div>
      </div>
    </nav>
  )
}

function AppContent() {
  const { isLoading } = useAuth()
  
  if (isLoading) {
    return (
      <div style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: '#0f172a'
      }}>
        <div style={{ textAlign: 'center' }}>
          <div style={{
            width: '40px',
            height: '40px',
            border: '4px solid #475569',
            borderTop: '4px solid #60a5fa',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite',
            margin: '0 auto 1rem'
          }}></div>
          <p style={{ color: '#cbd5e1' }}>Loading...</p>
        </div>
      </div>
    )
  }
  
  return (
    <div style={{ 
      minHeight: '100vh', 
      backgroundColor: '#0f172a',
      fontFamily: 'Arial, sans-serif'
    }}>
      <Navigation />
      <main style={{ padding: '2rem 0', backgroundColor: '#0f172a', minHeight: 'calc(100vh - 160px)' }}>
        <div style={{ maxWidth: '1200px', margin: '0 auto', padding: '0 1rem' }}>
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/generate" element={<ImageGeneration />} />
            <Route path="/gallery" element={<Gallery />} />
            <Route path="/dashboard" element={<DashboardPage />} />
          </Routes>
        </div>
      </main>
      
      {/* Enhanced Status Footer */}
      <footer style={{
        backgroundColor: '#1e293b',
        borderTop: '1px solid #475569',
        padding: '1rem',
        textAlign: 'center',
        color: '#94a3b8',
        fontSize: '0.875rem'
      }}>
        ✅ Phase 3: Frontend with Router & Auth | 🔗 Backend API Connected | 
        🚧 Next: Add Original Landing Page Components
      </footer>
    </div>
  )
}

function AppWithAuth() {
  return (
    <SimpleAuthProvider>
      <Router>
        <AppContent />
      </Router>
    </SimpleAuthProvider>
  )
}

export default AppWithAuth
