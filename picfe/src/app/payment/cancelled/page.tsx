'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { XCircle } from 'lucide-react';

export default function PaymentCancelled() {
  const router = useRouter();

  useEffect(() => {
    // Redirect to credits page after 3 seconds
    const timer = setTimeout(() => {
      router.push('/credits');
    }, 3000);

    return () => clearTimeout(timer);
  }, [router]);

  return (
    <div className="min-h-screen bg-gray-900 flex items-center justify-center">
      <div className="bg-gray-800 rounded-xl p-8 max-w-md w-full mx-4 text-center">
        <XCircle className="text-red-500 w-16 h-16 mx-auto mb-4" />
        <h1 className="text-2xl font-bold text-white mb-4">Payment Cancelled</h1>
        <p className="text-gray-300 mb-6">
          Your payment was cancelled. No charges were made to your account.
        </p>
        <button
          onClick={() => router.push('/credits')}
          className="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
        >
          Back to Credits
        </button>
      </div>
    </div>
  );
}
