'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { Button } from './ui/button';
import { Input } from './ui/input';

export default function LoginForm({ portal }: { portal: boolean }) {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
 codex/create-multi-tenant-crm-and-student-portal-2gl9hw
    if (loading) return;

    setError('');
    setLoading(true);

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);


    setError('');
    setLoading(true);

 main
    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
 codex/create-multi-tenant-crm-and-student-portal-2gl9hw
        body: JSON.stringify({ email, password, portal }),
        credentials: 'same-origin',
        signal: controller.signal
      });

      const payload = await res.json().catch(() => ({}));

      if (!res.ok) {

        body: JSON.stringify({ email, password, portal })
      });

      if (!res.ok) {
        const payload = await res.json();
 main
        setError(payload.error ?? 'Login failed');
        setLoading(false);
        return;
      }

 codex/create-multi-tenant-crm-and-student-portal-2gl9hw
      router.replace(portal ? '/portal/dashboard' : '/dashboard');
      router.refresh();
    } catch (err) {
      if ((err as Error).name === 'AbortError') {
        setError('Login timed out. Please check server/database and try again.');
      } else {
        setError('Network/server error during login. Please try again.');
      }
      setLoading(false);
    } finally {
      clearTimeout(timeout);

      router.push(portal ? '/portal/dashboard' : '/dashboard');
    } catch (err) {
      setError('An unexpected error occurred');
      setLoading(false);
 main
    }
  };

  return (
    <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
 codex/create-multi-tenant-crm-and-student-portal-2gl9hw
      <h1 className="text-2xl font-semibold text-slate-900">{portal ? 'Student Portal' : 'Vertue CRM'}</h1>

      <h1 className="text-2xl font-semibold text-slate-900">
        {portal ? 'Student Portal' : 'Vertue CRM'}
      </h1>
 main
      <p className="mt-1 text-sm text-slate-500">Sign in to continue</p>

      <form onSubmit={submit} className="mt-6 space-y-4">
        <Input placeholder="Email" value={email} onChange={(e) => setEmail(e.target.value)} />
        <Input type="password" placeholder="Password" value={password} onChange={(e) => setPassword(e.target.value)} />
        {error ? <p className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{error}</p> : null}
 codex/create-multi-tenant-crm-and-student-portal-2gl9hw
        <Button type="submit" className="w-full" disabled={loading}>{loading ? 'Signing in...' : 'Sign In'}</Button>
      </form>
    </div>
  );
}

        <Button type="submit" className="w-full" disabled={loading}>
          {loading ? 'Signing in...' : 'Sign In'}
        </Button>
      </form>
    </div>
  );
}
 main
