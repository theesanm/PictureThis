"use client";

import React, { useState } from 'react';
import Link from 'next/link';
import { useAuth } from '@/lib/auth-context';
import { usePathname } from 'next/navigation';

// Inline, tiny SVG icon components to avoid runtime dynamic lucide-react resolution during prerender
const ImageIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 24, height = 24, className }) => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width={width}
    height={height}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth={2}
    strokeLinecap="round"
    strokeLinejoin="round"
    className={className}
  >
    <rect x="3" y="3" width="18" height="14" rx="2" />
    <circle cx="8.5" cy="8.5" r="1.5" />
    <path d="M21 21l-5-5-3 3-4-4-4 4" />
  </svg>
);

const UserIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 18, height = 18, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
    <circle cx="12" cy="7" r="4" />
  </svg>
);

const LogOutIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 18, height = 18, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
    <polyline points="16 17 21 12 16 7" />
    <line x1="21" y1="12" x2="9" y2="12" />
  </svg>
);

const MenuIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 24, height = 24, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <line x1="3" y1="12" x2="21" y2="12" />
    <line x1="3" y1="6" x2="21" y2="6" />
    <line x1="3" y1="18" x2="21" y2="18" />
  </svg>
);

const XIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 24, height = 24, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <line x1="18" y1="6" x2="6" y2="18" />
    <line x1="6" y1="6" x2="18" y2="18" />
  </svg>
);

const CreditCardIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 18, height = 18, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <rect x="1" y="4" width="22" height="16" rx="2" />
    <line x1="1" y1="10" x2="23" y2="10" />
  </svg>
);
const Header = () => {
  const { user, credits, logout, isAuthenticated } = useAuth();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const pathname = usePathname();

  const isActive = (path: string) => {
    return pathname === path;
  };

  const toggleMobileMenu = () => {
    setMobileMenuOpen(!mobileMenuOpen);
  };

  return (
    <header className="sticky top-0 z-50 bg-gradient-to-r from-gray-900 to-gray-800 border-b border-gray-700">
      <div className="container mx-auto px-4 sm:px-6">
        <div className="flex items-center justify-between h-16 md:h-20">
          {/* Logo */}
          <Link href="/" className="flex items-center gap-3">
              <div className="p-2 bg-gradient-to-r from-purple-600 to-pink-500 rounded-lg flex items-center justify-center">
              <ImageIcon width={24} height={24} className="text-white" />
            </div>
            <span className="text-xl md:text-2xl font-bold text-white">PictureThis</span>
          </Link>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center gap-6">
            {isAuthenticated ? (
              <>
                <Link 
                  href="/dashboard" 
                  className={`text-sm font-medium px-3 py-2 rounded-md transition-all ${
                    isActive('/dashboard')
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                >
                  Dashboard
                </Link>
                <Link
                  href="/generate"
                  className={`text-sm font-medium px-3 py-2 rounded-md transition-all ${
                    isActive('/generate') 
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                >
                  Generate
                </Link>
                <Link 
                  href="/gallery" 
                  className={`text-sm font-medium px-3 py-2 rounded-md transition-all ${
                    isActive('/gallery') 
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                >
                  Gallery
                </Link>

                {/* Credit Counter */}
                <div className="flex items-center gap-2 px-3 py-2 bg-gray-800 rounded-md">
                  <CreditCardIcon width={18} height={18} className="text-yellow-400" />
                  <span className="text-yellow-400 font-medium">{credits}</span>
                </div>

                {/* Profile Menu */}
                <div className="relative">
                  <Link
                    href="/profile"
                    className={`flex items-center gap-2 px-3 py-2 rounded-md ${
                      isActive('/profile') 
                        ? 'bg-gray-700 text-white' 
                        : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                    }`}
                  >
                    <UserIcon width={18} height={18} />
                    <span className="font-medium">{user?.fullName || user?.name || user?.email}</span>
                  </Link>
                </div>

                <button
                  onClick={logout}
                  className="flex items-center gap-2 px-3 py-2 text-red-400 hover:bg-gray-700 rounded-md"
                >
                  <LogOutIcon width={18} height={18} />
                  <span>Logout</span>
                </button>
              </>
            ) : (
              <>
                <Link
                  href="/login"
                  className={`text-sm font-medium px-3 py-2 rounded-md transition-all ${
                    isActive('/login') 
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                >
                  Login
                </Link>
                <Link
                  href="/register"
                  className="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 rounded-md text-white font-medium hover:opacity-90 transition-opacity"
                >
                  Sign Up
                </Link>
              </>
            )}
          </nav>

          {/* Mobile Menu Button */}
          <div className="md:hidden">
            <button
              onClick={toggleMobileMenu}
              className="text-gray-300 hover:text-white p-2"
            >
                {mobileMenuOpen ? (
                <XIcon width={24} height={24} />
              ) : (
                <MenuIcon width={24} height={24} />
              )}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden bg-gray-800">
          <div className="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            {isAuthenticated ? (
              <>
                <Link
                  href="/dashboard"
                  className={`block px-3 py-2 rounded-md text-base font-medium ${
                    isActive('/dashboard')
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Dashboard
                </Link>
                <Link
                  href="/generate"
                  className={`block px-3 py-2 rounded-md text-base font-medium ${
                    isActive('/generate') 
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Generate
                </Link>
                <Link
                  href="/gallery"
                  className={`block px-3 py-2 rounded-md text-base font-medium ${
                    isActive('/gallery') 
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Gallery
                </Link>
                <Link
                  href="/profile"
                  className={`block px-3 py-2 rounded-md text-base font-medium ${
                    isActive('/profile') 
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Profile
                </Link>
                <div className="flex items-center justify-between px-3 py-2">
                  <div className="flex items-center gap-2">
                      <CreditCardIcon width={18} height={18} className="text-yellow-400" />
                      <span className="text-yellow-400 font-medium">{credits} credits</span>
                    </div>
                </div>
                <button
                  onClick={() => {
                    logout();
                    setMobileMenuOpen(false);
                  }}
                  className="flex w-full items-center gap-2 px-3 py-2 text-red-400 hover:bg-gray-700 rounded-md"
                >
                  <LogOutIcon width={18} height={18} />
                  <span>Logout</span>
                </button>
              </>
            ) : (
              <>
                <Link
                  href="/login"
                  className={`block px-3 py-2 rounded-md text-base font-medium ${
                    isActive('/login') 
                      ? 'bg-gray-700 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Login
                </Link>
                <Link
                  href="/register"
                  className="block px-3 py-2 bg-gradient-to-r from-purple-600 to-pink-500 rounded-md text-white font-medium"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  Sign Up
                </Link>
              </>
            )}
          </div>
        </div>
      )}
    </header>
  );
};

export default Header;
