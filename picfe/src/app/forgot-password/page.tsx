"use client"

import { useState } from 'react';

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState<{ type: 'idle' | 'success' | 'error'; message?: string }>({ type: 'idle' });

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setStatus({ type: 'idle' });

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3011'}/api/auth/forgot-password`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });

      if (res.ok) {
        setStatus({ type: 'success', message: 'If an account exists we will send password reset instructions to that email.' });
      } else {
        const data = await res.json().catch(() => ({}));
        setStatus({ type: 'error', message: data?.message || 'Failed to request password reset' });
      }
    } catch (err) {
      setStatus({ type: 'error', message: (err as Error).message || 'Network error' });
    }
  }

  return (
    <div className="max-w-xl mx-auto px-6 py-12">
      <h1 className="text-3xl font-semibold mb-4">Forgot your password?</h1>
      <p className="mb-6 text-sm text-slate-400">Enter your account email and we'll send a password reset link if the account exists.</p>

      <form onSubmit={handleSubmit} className="space-y-4">
        <label className="block">
          <span className="text-sm">Email</span>
          <input
            type="email"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="mt-1 block w-full rounded-md border px-3 py-2 bg-slate-800 text-white"
          />
        </label>

        <div>
          <button className="px-4 py-2 rounded-md bg-gradient-to-r from-purple-500 to-pink-500 text-white">Send reset link</button>
        </div>
      </form>

      {status.type === 'success' && <div className="mt-4 text-green-400">{status.message}</div>}
      {status.type === 'error' && <div className="mt-4 text-red-400">{status.message}</div>}
    </div>
  );
}
