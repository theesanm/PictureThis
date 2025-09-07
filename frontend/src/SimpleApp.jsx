import React from 'react'

function SimpleApp() {
  return (
    <div style={{ 
      padding: '20px', 
      fontFamily: 'Arial, sans-serif',
      backgroundColor: '#f9fafb',
      minHeight: '100vh'
    }}>
      <h1 style={{ 
        fontSize: '3rem', 
        fontWeight: 'bold', 
        color: '#111827',
        textAlign: 'center',
        marginBottom: '2rem'
      }}>
        🎨 Picture This - AI Image Generation
      </h1>
      
      <div style={{
        backgroundColor: 'white',
        padding: '2rem',
        borderRadius: '0.5rem',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        maxWidth: '800px',
        margin: '0 auto',
        textAlign: 'center'
      }}>
        <h2 style={{ color: '#2563eb', marginBottom: '1rem' }}>
          Welcome to the Picture This Platform
        </h2>
        
        <p style={{ color: '#4b5563', marginBottom: '2rem', lineHeight: '1.75' }}>
          Transform your ideas into beautiful artwork with our advanced AI image generation technology.
          This is the **Phase 3** frontend of our development plan.
        </p>
        
        <div style={{ 
          display: 'grid', 
          gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
          gap: '1rem',
          marginTop: '2rem'
        }}>
          <div style={{ 
            backgroundColor: '#eff6ff', 
            padding: '1rem', 
            borderRadius: '0.5rem',
            border: '1px solid #dbeafe'
          }}>
            <h3 style={{ color: '#1d4ed8', marginBottom: '0.5rem' }}>🖼️ Generate</h3>
            <p style={{ color: '#374151', fontSize: '0.875rem' }}>Create AI images</p>
          </div>
          
          <div style={{ 
            backgroundColor: '#f0fdf4', 
            padding: '1rem', 
            borderRadius: '0.5rem',
            border: '1px solid #bbf7d0'
          }}>
            <h3 style={{ color: '#15803d', marginBottom: '0.5rem' }}>👤 Profile</h3>
            <p style={{ color: '#374151', fontSize: '0.875rem' }}>Manage account</p>
          </div>
          
          <div style={{ 
            backgroundColor: '#fefce8', 
            padding: '1rem', 
            borderRadius: '0.5rem',
            border: '1px solid #fef3c7'
          }}>
            <h3 style={{ color: '#a16207', marginBottom: '0.5rem' }}>📊 Dashboard</h3>
            <p style={{ color: '#374151', fontSize: '0.875rem' }}>View your images</p>
          </div>
        </div>
        
        <div style={{ 
          marginTop: '2rem',
          padding: '1rem',
          backgroundColor: '#f3f4f6',
          borderRadius: '0.5rem'
        }}>
          <p style={{ color: '#6b7280', fontSize: '0.875rem' }}>
            ✅ Backend API: Connected<br/>
            ✅ Frontend: React + Vite running<br/>
            ⚠️ Authentication: Not implemented yet<br/>
            ⚠️ Image Generation: Not implemented yet
          </p>
        </div>
      </div>
    </div>
  )
}

export default SimpleApp
