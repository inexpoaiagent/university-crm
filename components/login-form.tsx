'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';

export default function LoginForm({ portal }: { portal: boolean }) {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password, portal })
    });

    if (!res.ok) {
      const payload = await res.json();
      setError(payload.error ?? 'Login failed');
      return;
    }

    router.push(portal ? '/portal/dashboard' : '/dashboard');
  };

  return (
    <form onSubmit={submit} className="card w-full max-w-md space-y-4">
      <h1 className="text-xl font-semibold">{portal ? 'Student Portal Login' : 'CRM Login'}</h1>
      <input className="w-full rounded border p-2" placeholder="Email" value={email} onChange={(e) => setEmail(e.target.value)} />
      <input className="w-full rounded border p-2" type="password" placeholder="Password" value={password} onChange={(e) => setPassword(e.target.value)} />
      {error ? <p className="text-sm text-red-600">{error}</p> : null}
      <button type="submit" className="w-full rounded bg-slate-900 py-2 text-white">Sign In</button>
    </form>
  );
}
