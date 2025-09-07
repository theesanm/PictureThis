import React from 'react'
import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom'

// Simple page components for testing
function HomePage() {
  return (
    <div style={{ padding: '20px', textAlign: 'center' }}>
      <h1 style={{ color: '#2563eb', marginBottom: '2rem' }}>🎨 Picture This - Home</h1>
      <p style={{ color: '#4b5563', marginBottom: '2rem' }}>
        Welcome to the AI Image Generation Platform
      </p>
      <div style={{ display: 'flex', gap: '1rem', justifyContent: 'center', flexWrap: 'wrap' }}>
        <Link to="/generate" style={{ 
          backgroundColor: '#2563eb', 
          color: 'white', 
          padding: '0.75rem 1.5rem', 
          borderRadius: '0.5rem',
          textDecoration: 'none',
          fontWeight: '600'
        }}>
          Generate Images
        </Link>
        <Link to="/dashboard" style={{ 
          backgroundColor: '#059669', 
          color: 'white', 
          padding: '0.75rem 1.5rem', 
          borderRadius: '0.5rem',
          textDecoration: 'none',
          fontWeight: '600'
        }}>
          Dashboard
        </Link>
      </div>
    </div>
  )
}

function GeneratePage() {
  return (
    <div style={{ padding: '20px', textAlign: 'center' }}>
      <h1 style={{ color: '#059669', marginBottom: '2rem' }}>🖼️ Generate Images</h1>
      <p style={{ color: '#4b5563', marginBottom: '2rem' }}>
        AI Image Generation will be implemented here
      </p>
      <Link to="/" style={{ color: '#2563eb', textDecoration: 'underline' }}>← Back to Home</Link>
    </div>
  )
}

function DashboardPage() {
  return (
    <div style={{ padding: '20px', textAlign: 'center' }}>
      <h1 style={{ color: '#dc2626', marginBottom: '2rem' }}>📊 Dashboard</h1>
      <p style={{ color: '#4b5563', marginBottom: '2rem' }}>
        Your generated images will appear here
      </p>
      <Link to="/" style={{ color: '#2563eb', textDecoration: 'underline' }}>← Back to Home</Link>
    </div>
  )
}

// Simple Navigation Header
function Navigation() {
  return (
    <nav style={{
      backgroundColor: 'white',
      borderBottom: '1px solid #e5e7eb',
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
          color: '#2563eb',
          textDecoration: 'none'
        }}>
          Picture This
        </Link>
        <div style={{ display: 'flex', gap: '1rem' }}>
          <Link to="/" style={{ color: '#4b5563', textDecoration: 'none' }}>Home</Link>
          <Link to="/generate" style={{ color: '#4b5563', textDecoration: 'none' }}>Generate</Link>
          <Link to="/dashboard" style={{ color: '#4b5563', textDecoration: 'none' }}>Dashboard</Link>
        </div>
      </div>
    </nav>
  )
}

function AppWithRouter() {
  return (
    <Router>
      <div style={{ 
        minHeight: '100vh', 
        backgroundColor: '#f9fafb',
        fontFamily: 'Arial, sans-serif'
      }}>
        <Navigation />
        <main style={{ padding: '2rem 0' }}>
          <div style={{ maxWidth: '1200px', margin: '0 auto', padding: '0 1rem' }}>
            <Routes>
              <Route path="/" element={<HomePage />} />
              <Route path="/generate" element={<GeneratePage />} />
              <Route path="/dashboard" element={<DashboardPage />} />
            </Routes>
          </div>
        </main>
        
        {/* Status Footer */}
        <footer style={{
          backgroundColor: 'white',
          borderTop: '1px solid #e5e7eb',
          padding: '1rem',
          textAlign: 'center',
          color: '#6b7280',
          fontSize: '0.875rem'
        }}>
          ✅ Phase 3: Frontend with Router | Backend API Connected | 
          Next: Add Authentication & Original Components
        </footer>
      </div>
    </Router>
  )
}

export default AppWithRouter
