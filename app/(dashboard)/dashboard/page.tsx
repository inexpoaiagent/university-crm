import Link from 'next/link';

const modules = [
  { name: 'Students', href: '/students' },
  { name: 'Applications', href: '/applications' },
  { name: 'Universities', href: '/universities' },
  { name: 'Tasks', href: '/tasks' }
];

export default function DashboardPage() {
  return (
    <main className="p-8 space-y-6">
      <h1 className="text-2xl font-semibold">CRM Dashboard</h1>
      <section className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {modules.map((item) => (
          <Link key={item.href} href={item.href} className="card hover:border-slate-400 transition">
            <h2 className="text-lg font-semibold">{item.name}</h2>
            <p className="text-sm text-slate-600">Open {item.name} management</p>
          </Link>
        ))}
      </section>
    </main>
  );
}
