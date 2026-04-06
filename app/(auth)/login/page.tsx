import LoginForm from '@/components/login-form';

export default function LoginPage() {
  return (
    <main className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-200 flex items-center justify-center p-8">
      <LoginForm portal={false} />
    </main>
  );
}