"use client"

import { useState, useEffect } from 'react';

export default function ResetPasswordPage() {
  const [token, setToken] = useState('');
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [status, setStatus] = useState<{ type: 'idle' | 'success' | 'error'; message?: string }>({ type: 'idle' });

  useEffect(() => {
    // Parse token from window location on client-side to avoid prerender issues
    if (typeof window !== 'undefined') {
      const params = new URLSearchParams(window.location.search);
      const t = params.get('token') || '';
      setToken(t);
      if (!t) setStatus({ type: 'error', message: 'No valid token found' });
    }
  }, []);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setStatus({ type: 'idle' });
    if (password.length < 6) {
      setStatus({ type: 'error', message: 'Password must be at least 6 characters' });
      return;
    }
    if (password !== confirm) {
      setStatus({ type: 'error', message: 'Passwords do not match' });
      return;
    }

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3011'}/api/auth/reset-password`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, password })
      });

      if (res.ok) {
        setStatus({ type: 'success', message: 'Password reset successful. You can now log in.' });
      } else {
        const data = await res.json().catch(() => ({}));
        setStatus({ type: 'error', message: data?.message || 'Failed to reset password' });
      }
    } catch (err) {
      setStatus({ type: 'error', message: (err as Error).message || 'Network error' });
    }
  }

  return (
    <div className="max-w-xl mx-auto px-6 py-12">
      <h1 className="text-3xl font-semibold mb-4">Reset your password</h1>
      {token ? (
        <form onSubmit={handleSubmit} className="space-y-4">
          <label className="block">
            <span className="text-sm">New password</span>
            <input type="password" required value={password} onChange={(e) => setPassword(e.target.value)} className="mt-1 block w-full rounded-md border px-3 py-2 bg-slate-800 text-white" />
          </label>

          <label className="block">
            <span className="text-sm">Confirm password</span>
            <input type="password" required value={confirm} onChange={(e) => setConfirm(e.target.value)} className="mt-1 block w-full rounded-md border px-3 py-2 bg-slate-800 text-white" />
          </label>

          <div>
            <button className="px-4 py-2 rounded-md bg-gradient-to-r from-purple-500 to-pink-500 text-white">Set new password</button>
          </div>
        </form>
      ) : (
        <div className="text-red-400">No valid token found in the URL.</div>
      )}

      {status.type === 'success' && <div className="mt-4 text-green-400">{status.message}</div>}
      {status.type === 'error' && <div className="mt-4 text-red-400">{status.message}</div>}
    </div>
  );
}
