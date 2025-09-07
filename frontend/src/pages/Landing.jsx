import React from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { Sparkles, ArrowRight } from 'lucide-react'

const Landing = () => {
  const { isAuthenticated } = useAuth()

  const heroStyle = {
    background: 'linear-gradient(135deg, #0f172a 0%, #581c87 50%, #0f172a 100%)',
    minHeight: '100vh',
    padding: '5rem 0',
    textAlign: 'center'
  }

  const containerStyle = {
    maxWidth: '1200px',
    margin: '0 auto',
    padding: '0 1rem'
  }

  const headingStyle = {
    fontSize: '4rem',
    fontWeight: 'bold',
    color: 'white',
    marginBottom: '1.5rem',
    lineHeight: '1.1'
  }

  const gradientTextStyle = {
    background: 'linear-gradient(to right, #c084fc, #f472b6)',
    WebkitBackgroundClip: 'text',
    WebkitTextFillColor: 'transparent',
    display: 'block'
  }

  const subtitleStyle = {
    fontSize: '1.25rem',
    color: '#cbd5e1',
    marginBottom: '3rem',
    maxWidth: '600px',
    margin: '0 auto 3rem auto',
    lineHeight: '1.6'
  }

  const buttonContainerStyle = {
    display: 'flex',
    gap: '1rem',
    justifyContent: 'center',
    flexWrap: 'wrap',
    marginBottom: '2rem'
  }

  const featuresStyle = {
    background: '#1e293b',
    padding: '5rem 0'
  }

  const featureGridStyle = {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
    gap: '2rem',
    marginTop: '3rem'
  }

  const featureCardStyle = {
    background: '#334155',
    padding: '2rem',
    borderRadius: '1rem',
    border: '1px solid #475569',
    textAlign: 'center',
    transition: 'all 0.3s ease'
  }

  return (
    <div style={{ backgroundColor: '#0f172a', minHeight: '100vh' }}>
      {/* Hero Section */}
      <section style={heroStyle}>
        <div style={containerStyle}>
          <h1 style={headingStyle}>
            Create Stunning AI Images
            <span style={gradientTextStyle}>in Seconds</span>
          </h1>
          <p style={subtitleStyle}>
            Transform your ideas into beautiful artwork with our advanced AI image generation technology.
            No design skills required - just describe what you want to see.
          </p>

          <div style={buttonContainerStyle}>
            {isAuthenticated ? (
              <Link to="/generate" className="btn btn-primary">
                <Sparkles style={{ width: '1.25rem', height: '1.25rem', marginRight: '0.5rem' }} />
                Start Creating
                <ArrowRight style={{ width: '1.25rem', height: '1.25rem', marginLeft: '0.5rem' }} />
              </Link>
            ) : (
              <>
                <Link to="/register" className="btn btn-primary">
                  Get Started Free
                </Link>
                <Link to="/login" className="btn btn-secondary">
                  Sign In
                </Link>
              </>
            )}
          </div>

          <div style={{ color: '#94a3b8', marginTop: '2rem' }}>
            <p style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem' }}>
              <Sparkles style={{ width: '1rem', height: '1rem', color: '#fbbf24' }} />
              50 free credits for new users • No credit card required
            </p>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section style={featuresStyle}>
        <div style={containerStyle}>
          <div style={{ textAlign: 'center', marginBottom: '4rem' }}>
            <h2 style={{ fontSize: '2.5rem', fontWeight: 'bold', color: 'white', marginBottom: '1rem' }}>
              Powerful AI Technology
            </h2>
            <p style={{ fontSize: '1.25rem', color: '#94a3b8', maxWidth: '600px', margin: '0 auto' }}>
              Experience the latest in AI image generation with our cutting-edge features
            </p>
          </div>
          
          <div style={featureGridStyle}>
            <div style={featureCardStyle}>
              <div style={{ 
                width: '4rem', 
                height: '4rem', 
                background: 'linear-gradient(135deg, #9333ea, #ec4899)',
                borderRadius: '1rem',
                margin: '0 auto 1.5rem auto',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '1.5rem'
              }}>
                🎨
              </div>
              <h3 style={{ fontSize: '1.25rem', fontWeight: '600', color: 'white', marginBottom: '1rem' }}>
                Creative Freedom
              </h3>
              <p style={{ color: '#94a3b8', lineHeight: '1.6' }}>
                Generate any style of artwork from photorealistic to abstract, fantasy to sci-fi
              </p>
            </div>
            
            <div style={featureCardStyle}>
              <div style={{ 
                width: '4rem', 
                height: '4rem', 
                background: 'linear-gradient(135deg, #3b82f6, #06b6d4)',
                borderRadius: '1rem',
                margin: '0 auto 1.5rem auto',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '1.5rem'
              }}>
                ⚡
              </div>
              <h3 style={{ fontSize: '1.25rem', fontWeight: '600', color: 'white', marginBottom: '1rem' }}>
                Lightning Fast
              </h3>
              <p style={{ color: '#94a3b8', lineHeight: '1.6' }}>
                High-quality images generated in seconds, not hours. Perfect for rapid prototyping
              </p>
            </div>
            
            <div style={featureCardStyle}>
              <div style={{ 
                width: '4rem', 
                height: '4rem', 
                background: 'linear-gradient(135deg, #10b981, #34d399)',
                borderRadius: '1rem',
                margin: '0 auto 1.5rem auto',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '1.5rem'
              }}>
                💎
              </div>
              <h3 style={{ fontSize: '1.25rem', fontWeight: '600', color: 'white', marginBottom: '1rem' }}>
                High Quality
              </h3>
              <p style={{ color: '#94a3b8', lineHeight: '1.6' }}>
                Professional-grade results suitable for commercial use, presentations, and more
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section style={{
        background: 'linear-gradient(135deg, #581c87, #1e3a8a, #581c87)',
        padding: '5rem 0',
        textAlign: 'center'
      }}>
        <div style={containerStyle}>
          <h2 style={{ fontSize: '2.5rem', fontWeight: 'bold', color: 'white', marginBottom: '1.5rem' }}>
            Ready to Create Amazing Images?
          </h2>
          <p style={{ fontSize: '1.25rem', color: '#e2e8f0', marginBottom: '2rem', maxWidth: '600px', margin: '0 auto 2rem auto' }}>
            Join thousands of creators who are already using AI to bring their imagination to life
          </p>
          <Link 
            to="/register"
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              backgroundColor: 'white',
              color: '#581c87',
              fontWeight: '600',
              padding: '1rem 2rem',
              borderRadius: '0.75rem',
              textDecoration: 'none',
              fontSize: '1.125rem',
              boxShadow: '0 10px 25px rgba(0,0,0,0.2)',
              transition: 'all 0.3s ease'
            }}
            onMouseOver={(e) => {
              e.target.style.transform = 'translateY(-2px)'
              e.target.style.boxShadow = '0 15px 35px rgba(0,0,0,0.3)'
            }}
            onMouseOut={(e) => {
              e.target.style.transform = 'translateY(0)'
              e.target.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)'
            }}
          >
            Get Started Free
            <ArrowRight style={{ width: '1.25rem', height: '1.25rem', marginLeft: '0.5rem' }} />
          </Link>
        </div>
      </section>
    </div>
  )
}

export default Landing
