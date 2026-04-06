'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
 codex/create-multi-tenant-crm-and-student-portal-eww8s8
import { Button } from './ui/button';
import { Input } from './ui/input';
=======
 main

export default function LoginForm({ portal }: { portal: boolean }) {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
 codex/create-multi-tenant-crm-and-student-portal-eww8s8
  const [loading, setLoading] = useState(false);
=======
 main

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
 codex/create-multi-tenant-crm-and-student-portal-eww8s8
    setLoading(true);
=======
 main
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password, portal })
    });

    if (!res.ok) {
      const payload = await res.json();
      setError(payload.error ?? 'Login failed');
 codex/create-multi-tenant-crm-and-student-portal-eww8s8
      setLoading(false);
=======
 main
      return;
    }

    router.push(portal ? '/portal/dashboard' : '/dashboard');
  };

  return (
 codex/create-multi-tenant-crm-and-student-portal-eww8s8
    <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
      <h1 className="text-2xl font-semibold text-slate-900">{portal ? 'Student Portal' : 'Vertue CRM'}</h1>
      <p className="mt-1 text-sm text-slate-500">Sign in to continue</p>

      <form onSubmit={submit} className="mt-6 space-y-4">
        <Input placeholder="Email" value={email} onChange={(e) => setEmail(e.target.value)} />
        <Input type="password" placeholder="Password" value={password} onChange={(e) => setPassword(e.target.value)} />
        {error ? <p className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p> : null}
        <Button type="submit" className="w-full" disabled={loading}>{loading ? 'Signing in...' : 'Sign In'}</Button>
      </form>
    </div>
=======
    <form onSubmit={submit} className="card w-full max-w-md space-y-4">
      <h1 className="text-xl font-semibold">{portal ? 'Student Portal Login' : 'CRM Login'}</h1>
      <input className="w-full rounded border p-2" placeholder="Email" value={email} onChange={(e) => setEmail(e.target.value)} />
      <input className="w-full rounded border p-2" type="password" placeholder="Password" value={password} onChange={(e) => setPassword(e.target.value)} />
      {error ? <p className="text-sm text-red-600">{error}</p> : null}
      <button type="submit" className="w-full rounded bg-slate-900 py-2 text-white">Sign In</button>
    </form>
 main
  );
}
