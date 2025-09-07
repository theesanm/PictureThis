import React from 'react'
import ReactDOM from 'react-dom/client'

console.log('🚀 Ultra Simple React App Starting')

function UltraSimpleApp() {
  console.log('🎯 UltraSimpleApp rendering')
  
  return React.createElement('div', {
    style: {
      padding: '20px',
      background: '#f0f0f0',
      fontFamily: 'Arial, sans-serif'
    }
  }, [
    React.createElement('h1', { key: 'title' }, '🎨 Ultra Simple React App'),
    React.createElement('p', { key: 'text' }, 'This is the simplest possible React app'),
    React.createElement('p', { key: 'status' }, 'If you see this, React is working!')
  ])
}

console.log('🔧 Creating React root')
const root = ReactDOM.createRoot(document.getElementById('root'))
console.log('🎨 Rendering app')
root.render(React.createElement(UltraSimpleApp))
console.log('✅ App rendered')
