import LoginForm from '@/components/login-form';

export default function LoginPage() {
  return (
    <main className="min-h-screen flex items-center justify-center p-8">
      <LoginForm portal={false} />
    </main>
  );
}
