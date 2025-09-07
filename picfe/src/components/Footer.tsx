'use client';

import React from 'react';
import Link from 'next/link';
import { Github, Twitter, Instagram } from 'lucide-react';

const Footer = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-gray-900 text-gray-400">
      <div className="container mx-auto px-4 py-8">
        <div className="flex flex-col md:flex-row justify-between items-center">
          <div className="mb-4 md:mb-0">
            <Link href="/" className="flex items-center gap-2">
              <span className="text-xl font-bold text-white">PictureThis</span>
            </Link>
            <p className="mt-2 text-sm">
              Generate stunning AI images from text or image prompts
            </p>
          </div>

          <div className="flex flex-col md:flex-row gap-6 md:gap-12">
            <div>
              <h3 className="text-white font-medium mb-3">Platform</h3>
              <ul className="space-y-2">
                <li>
                  <Link href="/generate" className="hover:text-white transition-colors">
                    Generate Images
                  </Link>
                </li>
                <li>
                  <Link href="/gallery" className="hover:text-white transition-colors">
                    Gallery
                  </Link>
                </li>
                <li>
                  <Link href="/pricing" className="hover:text-white transition-colors">
                    Pricing
                  </Link>
                </li>
              </ul>
            </div>

            <div>
              <h3 className="text-white font-medium mb-3">Company</h3>
              <ul className="space-y-2">
                <li>
                  <Link href="/about" className="hover:text-white transition-colors">
                    About
                  </Link>
                </li>
                <li>
                  <Link href="/privacy" className="hover:text-white transition-colors">
                    Privacy Policy
                  </Link>
                </li>
                <li>
                  <Link href="/terms" className="hover:text-white transition-colors">
                    Terms of Service
                  </Link>
                </li>
              </ul>
            </div>
          </div>

          <div className="mt-6 md:mt-0">
            <h3 className="text-white font-medium mb-3">Connect With Us</h3>
            <div className="flex space-x-4">
              <a
                href="#"
                className="hover:text-white transition-colors"
                aria-label="Twitter"
              >
                <Twitter />
              </a>
              <a
                href="#"
                className="hover:text-white transition-colors"
                aria-label="Instagram"
              >
                <Instagram />
              </a>
              <a
                href="#"
                className="hover:text-white transition-colors"
                aria-label="GitHub"
              >
                <Github />
              </a>
            </div>
          </div>
        </div>

        <div className="border-t border-gray-800 mt-8 pt-6 text-center md:text-left text-sm">
          <p>&copy; {currentYear} PictureThis. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
