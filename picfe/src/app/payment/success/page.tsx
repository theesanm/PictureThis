'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { CheckCircle, XCircle } from 'lucide-react';

export default function PaymentSuccess() {
  const router = useRouter();

  useEffect(() => {
    // Redirect to dashboard after 3 seconds
    const timer = setTimeout(() => {
      router.push('/dashboard');
    }, 3000);

    return () => clearTimeout(timer);
  }, [router]);

  return (
    <div className="min-h-screen bg-gray-900 flex items-center justify-center">
      <div className="bg-gray-800 rounded-xl p-8 max-w-md w-full mx-4 text-center">
        <CheckCircle className="text-green-500 w-16 h-16 mx-auto mb-4" />
        <h1 className="text-2xl font-bold text-white mb-4">Payment Successful!</h1>
        <p className="text-gray-300 mb-6">
          Your credits have been added to your account. You will be redirected to the dashboard shortly.
        </p>
        <button
          onClick={() => router.push('/dashboard')}
          className="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
        >
          Go to Dashboard
        </button>
      </div>
    </div>
  );
}
