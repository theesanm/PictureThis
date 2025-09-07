import React from 'react'
import ReactDOM from 'react-dom/client'

// Router State Management
function useRouter() {
  const [currentPage, setCurrentPage] = React.useState('landing')
  const [user, setUser] = React.useState(null)
  
  const navigate = (page) => setCurrentPage(page)
  
  return { currentPage, navigate, user, setUser }
}

// Auth functions
const authAPI = {
  login: async (email, password) => {
    const response = await fetch('http://localhost:3011/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    })
    return response.json()
  },
  
  register: async (email, password) => {
    const response = await fetch('http://localhost:3011/api/auth/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    })
    return response.json()
  }
}

// Common styles
const styles = {
  container: {
    minHeight: '100vh',
    background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    color: 'white',
    fontFamily: 'Arial, sans-serif'
  },
  content: {
    maxWidth: '1200px',
    margin: '0 auto',
    padding: '20px'
  },
  card: {
    background: 'rgba(255,255,255,0.1)',
    padding: '40px',
    borderRadius: '20px',
    backdropFilter: 'blur(10px)',
    maxWidth: '600px',
    margin: '0 auto'
  },
  button: {
    background: '#4CAF50',
    color: 'white',
    border: 'none',
    padding: '15px 30px',
    borderRadius: '25px',
    fontSize: '1.1rem',
    cursor: 'pointer',
    margin: '10px',
    transition: 'all 0.3s ease'
  },
  input: {
    width: '100%',
    padding: '15px',
    margin: '10px 0',
    border: 'none',
    borderRadius: '10px',
    fontSize: '1rem',
    background: 'rgba(255,255,255,0.9)',
    color: '#333'
  }
}

// Header Component
function Header({ user, navigate, setUser }) {
  return (
    <header style={{ 
      display: 'flex', 
      justifyContent: 'space-between', 
      alignItems: 'center', 
      padding: '20px 0',
      borderBottom: '1px solid rgba(255,255,255,0.2)',
      marginBottom: '40px'
    }}>
      <h1 
        onClick={() => navigate('landing')}
        style={{ 
          fontSize: '2rem', 
          margin: 0, 
          cursor: 'pointer' 
        }}
      >
        🎨 Picture This
      </h1>
      
      <nav style={{ display: 'flex', gap: '20px', alignItems: 'center' }}>
        {user ? (
          <>
            <button 
              onClick={() => navigate('dashboard')}
              style={{...styles.button, background: 'transparent', border: '1px solid white'}}
            >
              Dashboard
            </button>
            <button 
              onClick={() => navigate('generate')}
              style={{...styles.button, background: 'transparent', border: '1px solid white'}}
            >
              Generate
            </button>
            <span>Welcome, {user.email}</span>
            <button 
              onClick={() => {
                setUser(null)
                navigate('landing')
              }}
              style={{...styles.button, background: '#f44336'}}
            >
              Logout
            </button>
          </>
        ) : (
          <>
            <button 
              onClick={() => navigate('login')}
              style={{...styles.button, background: 'transparent', border: '1px solid white'}}
            >
              Login
            </button>
            <button 
              onClick={() => navigate('register')}
              style={styles.button}
            >
              Sign Up
            </button>
          </>
        )}
      </nav>
    </header>
  )
}

// Landing Page
function Landing({ navigate }) {
  return (
    <main style={{ textAlign: 'center', padding: '40px 0' }}>
      <div style={styles.card}>
        <h2 style={{ fontSize: '2.5rem', marginBottom: '30px' }}>
          Transform Your Ideas Into Stunning Images
        </h2>
        
        <p style={{ fontSize: '1.2rem', lineHeight: '1.6', marginBottom: '30px' }}>
          Generate high-quality images using state-of-the-art AI technology. 
          Simply describe what you want to see, and our AI will create it for you.
        </p>

        <div style={{ display: 'flex', gap: '20px', justifyContent: 'center', flexWrap: 'wrap' }}>
          <button 
            onClick={() => navigate('register')}
            style={styles.button}
          >
            Get Started Free
          </button>
          
          <button 
            onClick={() => navigate('login')}
            style={{
              ...styles.button,
              background: 'transparent',
              border: '2px solid white'
            }}
          >
            Sign In
          </button>
        </div>
      </div>

      {/* Features */}
      <div style={{ 
        display: 'grid', 
        gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', 
        gap: '30px',
        marginTop: '60px'
      }}>
        {[
          { icon: '🚀', title: 'Fast Generation', desc: 'Create images in seconds' },
          { icon: '🎨', title: 'High Quality', desc: 'Professional-grade results' },
          { icon: '💡', title: 'Creative Freedom', desc: 'Unlimited possibilities' }
        ].map((feature, idx) => (
          <div key={idx} style={{
            background: 'rgba(255,255,255,0.1)',
            padding: '30px',
            borderRadius: '15px',
            backdropFilter: 'blur(10px)',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '3rem', marginBottom: '15px' }}>
              {feature.icon}
            </div>
            <h3 style={{ fontSize: '1.3rem', marginBottom: '10px' }}>
              {feature.title}
            </h3>
            <p style={{ opacity: 0.9 }}>
              {feature.desc}
            </p>
          </div>
        ))}
      </div>
    </main>
  )
}

// Login Page
function Login({ navigate, setUser }) {
  const [email, setEmail] = React.useState('')
  const [password, setPassword] = React.useState('')
  const [loading, setLoading] = React.useState(false)
  const [message, setMessage] = React.useState('')

  const handleLogin = async (e) => {
    e.preventDefault()
    setLoading(true)
    try {
      const result = await authAPI.login(email, password)
      if (result.success) {
        setUser({ email, token: result.token })
        navigate('dashboard')
        setMessage('Login successful!')
      } else {
        setMessage('Login failed: ' + result.message)
      }
    } catch (error) {
      setMessage('Error: ' + error.message)
    }
    setLoading(false)
  }

  return (
    <main style={{ textAlign: 'center', padding: '40px 0' }}>
      <div style={styles.card}>
        <h2 style={{ fontSize: '2rem', marginBottom: '30px' }}>Login</h2>
        
        <form onSubmit={handleLogin}>
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            style={styles.input}
            required
          />
          
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            style={styles.input}
            required
          />
          
          <button 
            type="submit" 
            disabled={loading}
            style={{...styles.button, width: '100%', marginTop: '20px'}}
          >
            {loading ? 'Logging in...' : 'Login'}
          </button>
        </form>

        {message && (
          <div style={{ 
            marginTop: '20px', 
            padding: '10px', 
            background: message.includes('successful') ? 'rgba(76,175,80,0.3)' : 'rgba(244,67,54,0.3)',
            borderRadius: '10px'
          }}>
            {message}
          </div>
        )}

        <p style={{ marginTop: '20px' }}>
          Don't have an account?{' '}
          <button 
            onClick={() => navigate('register')}
            style={{ background: 'none', border: 'none', color: 'white', textDecoration: 'underline', cursor: 'pointer' }}
          >
            Sign up
          </button>
        </p>
      </div>
    </main>
  )
}

// Register Page
function Register({ navigate, setUser }) {
  const [email, setEmail] = React.useState('')
  const [password, setPassword] = React.useState('')
  const [loading, setLoading] = React.useState(false)
  const [message, setMessage] = React.useState('')

  const handleRegister = async (e) => {
    e.preventDefault()
    setLoading(true)
    try {
      const result = await authAPI.register(email, password)
      if (result.success) {
        setUser({ email, id: result.user.id })
        navigate('dashboard')
        setMessage('Registration successful!')
      } else {
        setMessage('Registration failed: ' + result.message)
      }
    } catch (error) {
      setMessage('Error: ' + error.message)
    }
    setLoading(false)
  }

  return (
    <main style={{ textAlign: 'center', padding: '40px 0' }}>
      <div style={styles.card}>
        <h2 style={{ fontSize: '2rem', marginBottom: '30px' }}>Create Account</h2>
        
        <form onSubmit={handleRegister}>
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            style={styles.input}
            required
          />
          
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            style={styles.input}
            required
          />
          
          <button 
            type="submit" 
            disabled={loading}
            style={{...styles.button, width: '100%', marginTop: '20px'}}
          >
            {loading ? 'Creating Account...' : 'Create Account'}
          </button>
        </form>

        {message && (
          <div style={{ 
            marginTop: '20px', 
            padding: '10px', 
            background: message.includes('successful') ? 'rgba(76,175,80,0.3)' : 'rgba(244,67,54,0.3)',
            borderRadius: '10px'
          }}>
            {message}
          </div>
        )}

        <p style={{ marginTop: '20px' }}>
          Already have an account?{' '}
          <button 
            onClick={() => navigate('login')}
            style={{ background: 'none', border: 'none', color: 'white', textDecoration: 'underline', cursor: 'pointer' }}
          >
            Login
          </button>
        </p>
      </div>
    </main>
  )
}

// Dashboard Page
function Dashboard({ user, navigate }) {
  const [credits, setCredits] = React.useState(0)
  const [loading, setLoading] = React.useState(true)

  React.useEffect(() => {
    fetch('http://localhost:3011/api/credits')
      .then(res => res.json())
      .then(data => {
        setCredits(data.credits)
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }, [])

  return (
    <main style={{ padding: '40px 0' }}>
      <div style={styles.card}>
        <h2 style={{ fontSize: '2rem', marginBottom: '30px', textAlign: 'center' }}>
          Welcome back, {user.email}!
        </h2>
        
        <div style={{ 
          display: 'grid', 
          gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', 
          gap: '20px',
          marginBottom: '30px'
        }}>
          <div style={{
            background: 'rgba(255,255,255,0.2)',
            padding: '20px',
            borderRadius: '15px',
            textAlign: 'center'
          }}>
            <h3>Credits</h3>
            <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>
              {loading ? '...' : credits}
            </div>
          </div>
          
          <div style={{
            background: 'rgba(255,255,255,0.2)',
            padding: '20px',
            borderRadius: '15px',
            textAlign: 'center'
          }}>
            <h3>Images Generated</h3>
            <div style={{ fontSize: '2rem', fontWeight: 'bold' }}>0</div>
          </div>
        </div>

        <div style={{ textAlign: 'center' }}>
          <h3>Quick Actions</h3>
          <div style={{ display: 'flex', gap: '20px', justifyContent: 'center', flexWrap: 'wrap' }}>
            <button 
              onClick={() => navigate('generate')}
              style={styles.button}
            >
              🎨 Generate Image
            </button>
            
            <button 
              onClick={() => alert('Coming soon!')}
              style={{...styles.button, background: '#2196F3'}}
            >
              📁 My Gallery
            </button>
            
            <button 
              onClick={() => alert('Coming soon!')}
              style={{...styles.button, background: '#FF9800'}}
            >
              💳 Buy Credits
            </button>
          </div>
        </div>
      </div>
    </main>
  )
}

// Image Generation Page
function ImageGeneration({ user }) {
  const [prompt, setPrompt] = React.useState('')
  const [loading, setLoading] = React.useState(false)
  const [message, setMessage] = React.useState('')

  const handleGenerate = async (e) => {
    e.preventDefault()
    setLoading(true)
    try {
      const response = await fetch('http://localhost:3011/api/images/generate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt })
      })
      const result = await response.json()
      
      if (result.success) {
        setMessage(`✅ Image generation started! Task ID: ${result.task_id}`)
      } else {
        setMessage('❌ Generation failed: ' + result.message)
      }
    } catch (error) {
      setMessage('❌ Error: ' + error.message)
    }
    setLoading(false)
  }

  return (
    <main style={{ padding: '40px 0' }}>
      <div style={styles.card}>
        <h2 style={{ fontSize: '2rem', marginBottom: '30px', textAlign: 'center' }}>
          🎨 Generate AI Image
        </h2>
        
        <form onSubmit={handleGenerate}>
          <div style={{ marginBottom: '20px' }}>
            <label style={{ display: 'block', marginBottom: '10px', fontSize: '1.1rem' }}>
              Describe what you want to create:
            </label>
            <textarea
              value={prompt}
              onChange={(e) => setPrompt(e.target.value)}
              placeholder="e.g., A majestic mountain landscape at sunset with a lake reflection..."
              style={{
                ...styles.input,
                height: '120px',
                resize: 'vertical'
              }}
              required
            />
          </div>
          
          <button 
            type="submit" 
            disabled={loading || !prompt.trim()}
            style={{...styles.button, width: '100%', fontSize: '1.2rem'}}
          >
            {loading ? '🎨 Generating...' : '🚀 Generate Image'}
          </button>
        </form>

        {message && (
          <div style={{ 
            marginTop: '20px', 
            padding: '15px', 
            background: message.includes('✅') ? 'rgba(76,175,80,0.3)' : 'rgba(244,67,54,0.3)',
            borderRadius: '10px',
            fontSize: '1.1rem'
          }}>
            {message}
          </div>
        )}

        <div style={{ 
          marginTop: '30px', 
          padding: '20px', 
          background: 'rgba(255,255,255,0.1)',
          borderRadius: '15px'
        }}>
          <h3>💡 Tips for better results:</h3>
          <ul style={{ textAlign: 'left', lineHeight: '1.6' }}>
            <li>Be specific and descriptive</li>
            <li>Mention style (realistic, cartoon, painting, etc.)</li>
            <li>Include lighting and mood details</li>
            <li>Specify colors and composition</li>
          </ul>
        </div>
      </div>
    </main>
  )
}

// Main App Component
function App() {
  const { currentPage, navigate, user, setUser } = useRouter()
  const [backendStatus, setBackendStatus] = React.useState('Checking...')

  React.useEffect(() => {
    fetch('http://localhost:3011/api/health')
      .then(res => res.json())
      .then(() => setBackendStatus('✅ Connected'))
      .catch(() => setBackendStatus('❌ Offline'))
  }, [])

  const renderPage = () => {
    switch (currentPage) {
      case 'login':
        return <Login navigate={navigate} setUser={setUser} />
      case 'register':
        return <Register navigate={navigate} setUser={setUser} />
      case 'dashboard':
        return user ? <Dashboard user={user} navigate={navigate} /> : <Login navigate={navigate} setUser={setUser} />
      case 'generate':
        return user ? <ImageGeneration user={user} /> : <Login navigate={navigate} setUser={setUser} />
      default:
        return <Landing navigate={navigate} />
    }
  }

  return (
    <div style={styles.container}>
      <div style={styles.content}>
        <Header user={user} navigate={navigate} setUser={setUser} />
        
        {/* Backend Status */}
        <div style={{ 
          textAlign: 'center',
          marginBottom: '20px',
          fontSize: '0.9rem',
          opacity: 0.8
        }}>
          Backend Status: {backendStatus}
        </div>
        
        {renderPage()}
      </div>
    </div>
  )
}

// Render the app
ReactDOM.createRoot(document.getElementById('root')).render(<App />)
