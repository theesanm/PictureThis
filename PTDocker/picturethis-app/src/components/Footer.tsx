import React from 'react';

const Footer: React.FC = () => {
  return (
    <footer className="bg-gray-800 py-6">
      <div className="container mx-auto text-center text-gray-400">
        <p>&copy; {new Date().getFullYear()} PictureThis. All rights reserved.</p>
        <div className="mt-4">
          <a href="/privacy" className="hover:text-white">Privacy Policy</a>
          <span className="mx-2">|</span>
          <a href="/terms" className="hover:text-white">Terms of Service</a>
        </div>
      </div>
    </footer>
  );
};

export default Footer;