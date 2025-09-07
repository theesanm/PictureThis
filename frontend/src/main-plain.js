console.log('🚀 Plain JS React App Starting')

// Import React and ReactDOM
import React from 'react'
import ReactDOM from 'react-dom/client'

console.log('📦 React modules imported')

// Create a simple component using React.createElement
function PlainJSApp() {
  console.log('🎯 PlainJSApp function called')
  
  return React.createElement('div', {
    style: {
      padding: '20px',
      backgroundColor: '#e8f5e8',
      fontFamily: 'Arial, sans-serif',
      textAlign: 'center'
    }
  }, [
    React.createElement('h1', { 
      key: 'title',
      style: { color: '#2d5016' }
    }, '🌟 Plain JS React App'),
    React.createElement('p', { 
      key: 'subtitle',
      style: { fontSize: '18px', color: '#4a7c59' }
    }, 'This app uses React.createElement instead of JSX'),
    React.createElement('p', { 
      key: 'status',
      style: { 
        backgroundColor: '#d4edda',
        border: '1px solid #c3e6cb',
        borderRadius: '5px',
        padding: '10px',
        color: '#155724'
      }
    }, 'If you see this, React and Vite are working correctly!')
  ])
}

console.log('🔧 Creating React root element')

try {
  const rootElement = document.getElementById('root')
  console.log('📍 Root element found:', rootElement)
  
  const root = ReactDOM.createRoot(rootElement)
  console.log('🎨 React root created successfully')
  
  root.render(React.createElement(PlainJSApp))
  console.log('✅ App rendered successfully')
} catch (error) {
  console.error('❌ Error during app initialization:', error)
  document.body.innerHTML = `
    <div style="padding: 20px; background: #ffebee; color: #c62828; font-family: Arial;">
      <h1>❌ Error</h1>
      <p><strong>Message:</strong> ${error.message}</p>
      <p><strong>Stack:</strong> ${error.stack}</p>
    </div>
  `
}
