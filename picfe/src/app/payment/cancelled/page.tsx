import Link from 'next/link';

export const dynamic = 'force-dynamic';

export default function PaymentCancelled() {
  return (
    <div className="min-h-screen bg-gray-900 flex items-center justify-center">
      <div className="bg-gray-800 rounded-xl p-8 max-w-md w-full mx-4 text-center">
        <div className="text-red-500 w-16 h-16 mx-auto mb-4 flex items-center justify-center text-6xl font-bold">
          ✕
        </div>
        <h1 className="text-2xl font-bold text-white mb-4">Payment Cancelled</h1>
        <p className="text-gray-300 mb-6">
          Your payment was cancelled. No charges were made to your account.
        </p>
        <Link
          href="/credits"
          className="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors inline-block"
        >
          Back to Credits
        </Link>
      </div>
    </div>
  );
}
