import React from 'react'
import ReactDOM from 'react-dom/client'

// Simple Landing Page Component
function Landing() {
  const [backendStatus, setBackendStatus] = React.useState('Testing...')
  
  React.useEffect(() => {
    fetch('http://localhost:3011/api/health')
      .then(res => res.json())
      .then(data => setBackendStatus('✅ Backend Connected'))
      .catch(() => setBackendStatus('❌ Backend Error'))
  }, [])

  return (
    <div style={{
      minHeight: '100vh',
      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      color: 'white',
      fontFamily: 'Arial, sans-serif'
    }}>
      <div style={{ maxWidth: '1200px', margin: '0 auto', padding: '20px' }}>
        {/* Header */}
        <header style={{ textAlign: 'center', padding: '40px 0' }}>
          <h1 style={{ fontSize: '3rem', margin: '0 0 20px 0' }}>
            🎨 Picture This
          </h1>
          <p style={{ fontSize: '1.2rem', opacity: 0.9 }}>
            AI-Powered Image Generation Platform
          </p>
          <div style={{ 
            background: 'rgba(255,255,255,0.2)', 
            padding: '10px 20px', 
            borderRadius: '20px',
            display: 'inline-block',
            marginTop: '10px'
          }}>
            {backendStatus}
          </div>
        </header>

        {/* Main Content */}
        <main style={{ textAlign: 'center', padding: '40px 0' }}>
          <div style={{
            background: 'rgba(255,255,255,0.1)',
            padding: '40px',
            borderRadius: '20px',
            backdropFilter: 'blur(10px)',
            maxWidth: '800px',
            margin: '0 auto'
          }}>
            <h2 style={{ fontSize: '2rem', marginBottom: '30px' }}>
              Transform Your Ideas Into Stunning Images
            </h2>
            
            <p style={{ fontSize: '1.1rem', lineHeight: '1.6', marginBottom: '30px' }}>
              Generate high-quality images using state-of-the-art AI technology. 
              Simply describe what you want to see, and our AI will create it for you.
            </p>

            <div style={{ display: 'flex', gap: '20px', justifyContent: 'center', flexWrap: 'wrap' }}>
              <button 
                onClick={() => window.location.href = '#login'}
                style={{
                  background: '#4CAF50',
                  color: 'white',
                  border: 'none',
                  padding: '15px 30px',
                  borderRadius: '25px',
                  fontSize: '1.1rem',
                  cursor: 'pointer',
                  transition: 'all 0.3s ease'
                }}
                onMouseOver={(e) => e.target.style.background = '#45a049'}
                onMouseOut={(e) => e.target.style.background = '#4CAF50'}
              >
                Get Started
              </button>
              
              <button 
                onClick={() => alert('Demo coming soon!')}
                style={{
                  background: 'transparent',
                  color: 'white',
                  border: '2px solid white',
                  padding: '15px 30px',
                  borderRadius: '25px',
                  fontSize: '1.1rem',
                  cursor: 'pointer',
                  transition: 'all 0.3s ease'
                }}
                onMouseOver={(e) => {
                  e.target.style.background = 'white'
                  e.target.style.color = '#667eea'
                }}
                onMouseOut={(e) => {
                  e.target.style.background = 'transparent'
                  e.target.style.color = 'white'
                }}
              >
                Watch Demo
              </button>
            </div>
          </div>

          {/* Features Section */}
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

        {/* Footer */}
        <footer style={{ 
          textAlign: 'center', 
          padding: '40px 0', 
          borderTop: '1px solid rgba(255,255,255,0.2)',
          marginTop: '60px'
        }}>
          <p style={{ opacity: 0.8 }}>
            © 2025 Picture This. Powered by AI.
          </p>
        </footer>
      </div>
    </div>
  )
}

// Render the app
ReactDOM.createRoot(document.getElementById('root')).render(<Landing />)
