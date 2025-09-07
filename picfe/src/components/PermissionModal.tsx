'use client';

import React from 'react';
import { X } from 'lucide-react';

interface PermissionModalProps {
  isOpen: boolean;
  onClose: () => void;
  onAccept: () => void;
  onDecline: () => void;
  title: string;
  description: string;
  permissionType: string;
}

const PermissionModal: React.FC<PermissionModalProps> = ({
  isOpen,
  onClose,
  onAccept,
  onDecline,
  title,
  description,
  permissionType
}) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
      <div className="bg-gray-800 rounded-xl border border-gray-700 shadow-xl max-w-md w-full animate-fade-in">
        <div className="flex justify-between items-center p-5 border-b border-gray-700">
          <h3 className="text-xl font-semibold text-white">{title}</h3>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-300">
            <X size={20} />
          </button>
        </div>
        
        <div className="p-6">
          <p className="text-gray-300 mb-6">
            {description}
          </p>
          
          {permissionType === 'image_usage' && (
            <div className="bg-gray-700/50 p-4 rounded-md mb-6">
              <p className="text-gray-300 text-sm">
                <strong>Important:</strong> By accepting, you confirm that:
              </p>
              <ul className="list-disc list-inside text-gray-300 text-sm mt-2 space-y-1">
                <li>You have the necessary rights, licenses, and permissions to use any images you upload</li>
                <li>Your use of these images complies with applicable copyright laws</li>
                <li>You take responsibility for any images you upload to our service</li>
                <li>Your acceptance will be recorded along with your IP address and device information</li>
              </ul>
            </div>
          )}
          
          <div className="flex justify-end space-x-3">
            <button
              onClick={onDecline}
              className="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-md"
            >
              Decline
            </button>
            <button
              onClick={onAccept}
              className="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 hover:opacity-90 text-white rounded-md"
            >
              Accept
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PermissionModal;
