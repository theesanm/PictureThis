"use client";

import React from 'react';

const AlertCircleIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 16, height = 16, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <circle cx="12" cy="12" r="10" />
    <line x1="12" y1="8" x2="12" y2="12" />
    <line x1="12" y1="16" x2="12.01" y2="16" />
  </svg>
);

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
        <AlertCircleIcon 
          width={16} 
          height={16}
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
