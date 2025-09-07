import React from 'react'
import ReactDOM from 'react-dom/client'

function SimpleApp() {
  const [message, setMessage] = React.useState('Loading...')
  
  React.useEffect(() => {
    // Test backend connection
    fetch('http://localhost:3011/api/health')
      .then(res => res.json())
      .then(data => setMessage('✅ Backend Connected'))
      .catch(() => setMessage('❌ Backend Error'))
  }, [])

  return (
    <div style={{
      minHeight: '100vh',
      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      color: 'white',
      fontFamily: 'Arial, sans-serif',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      flexDirection: 'column'
    }}>
      <h1 style={{ fontSize: '3rem', marginBottom: '20px' }}>
        🎨 Picture This
      </h1>
      <p style={{ fontSize: '1.2rem', marginBottom: '20px' }}>
        AI Image Generation Platform
      </p>
      <div style={{ 
        background: 'rgba(255,255,255,0.2)', 
        padding: '15px 30px', 
        borderRadius: '25px',
        fontSize: '1.1rem'
      }}>
        {message}
      </div>
      <div style={{ marginTop: '30px' }}>
        <button 
          onClick={() => alert('React is working!')}
          style={{
            background: '#4CAF50',
            color: 'white',
            border: 'none',
            padding: '15px 30px',
            borderRadius: '25px',
            fontSize: '1.1rem',
            cursor: 'pointer',
            margin: '10px'
          }}
        >
          Test React
        </button>
      </div>
    </div>
  )
}

ReactDOM.createRoot(document.getElementById('root')).render(<SimpleApp />)
