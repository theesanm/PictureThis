'use client';

import React from 'react';
import { AlertCircle } from 'lucide-react';

interface CreditStatusProps {
  credits: number;
  creditCost: number;
  className?: string;
}

const CreditStatus = ({ credits, creditCost, className = '' }: CreditStatusProps) => {
  const isLow = credits < creditCost * 2;
  const isVeryLow = credits < creditCost;
  
  let statusText = `${credits} credits`;
  let statusColor = 'text-gray-300';
  let showWarning = false;
  
  if (isVeryLow) {
    statusText = `Insufficient credits (${credits})`;
    statusColor = 'text-red-500';
    showWarning = true;
  } else if (isLow) {
    statusText = `Low credits (${credits})`;
    statusColor = 'text-yellow-400';
    showWarning = true;
  }
  
  return (
    <div className={`flex items-center ${className}`}>
      {showWarning && (
        <AlertCircle 
          size={16} 
          className={isVeryLow ? "text-red-500 mr-2" : "text-yellow-400 mr-2"} 
        />
      )}
      <span className={`text-sm font-medium ${statusColor}`}>
        {statusText}
      </span>
    </div>
  );
};

export default CreditStatus;
