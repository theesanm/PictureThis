import React from 'react'
import ReactDOM from 'react-dom/client'

function SimpleApp() {
  return (
    <div style={{ padding: '20px', fontFamily: 'Arial' }}>
      <h1>🚀 Picture This - Simple Test</h1>
      <p>Frontend is working!</p>
      <button onClick={() => fetch('http://localhost:3011/api/health')
        .then(res => res.json())
        .then(data => alert(JSON.stringify(data)))
        .catch(err => alert('Backend Error: ' + err.message))}>
        Test Backend Connection
      </button>
    </div>
  )
}

ReactDOM.createRoot(document.getElementById('root')).render(<SimpleApp />)
