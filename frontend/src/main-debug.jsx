import React from 'react'
import ReactDOM from 'react-dom/client'

console.log('Starting React app...')

function TestApp() {
  console.log('TestApp component rendering...')
  
  const [count, setCount] = React.useState(0)
  const [backendStatus, setBackendStatus] = React.useState('Testing...')
  
  React.useEffect(() => {
    console.log('useEffect running...')
    
    // Test backend connection
    fetch('http://localhost:3011/api/health')
      .then(response => {
        console.log('Backend response:', response)
        return response.json()
      })
      .then(data => {
        console.log('Backend data:', data)
        setBackendStatus('✅ Connected')
      })
      .catch(error => {
        console.error('Backend error:', error)
        setBackendStatus('❌ Error')
      })
  }, [])

  const handleClick = () => {
    console.log('Button clicked, count:', count)
    setCount(count + 1)
  }

  return (
    <div style={{
      padding: '20px',
      fontFamily: 'Arial, sans-serif',
      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      minHeight: '100vh',
      color: 'white'
    }}>
      <h1>🎨 Picture This - Debug Mode</h1>
      <p>Backend Status: {backendStatus}</p>
      <p>Click Count: {count}</p>
      <button 
        onClick={handleClick}
        style={{
          padding: '10px 20px',
          fontSize: '16px',
          background: '#4CAF50',
          color: 'white',
          border: 'none',
          borderRadius: '5px',
          cursor: 'pointer'
        }}
      >
        Click me! ({count})
      </button>
      
      <div style={{ marginTop: '20px' }}>
        <h3>Debug Info:</h3>
        <ul>
          <li>React is working ✅</li>
          <li>State management working ✅</li>
          <li>Event handlers working ✅</li>
          <li>Backend connection: {backendStatus}</li>
        </ul>
      </div>
    </div>
  )
}

console.log('Creating React root...')

try {
  const root = ReactDOM.createRoot(document.getElementById('root'))
  console.log('Rendering app...')
  root.render(<TestApp />)
  console.log('App rendered successfully!')
} catch (error) {
  console.error('Error rendering app:', error)
  document.body.innerHTML = `<h1>Error: ${error.message}</h1>`
}
