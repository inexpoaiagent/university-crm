import Link from 'next/link';
 codex/create-multi-tenant-crm-and-student-portal-eww8s8
import AppShell from '@/components/app-shell';

const cards = [
  { label: 'Total Students', value: '—', href: '/students' },
  { label: 'Applications', value: '—', href: '/applications' },
  { label: 'Pending Documents', value: '—', href: '/documents' },
  { label: 'Universities', value: '—', href: '/universities' }
=======

const modules = [
  { name: 'Students', href: '/students' },
  { name: 'Applications', href: '/applications' },
  { name: 'Universities', href: '/universities' },
  { name: 'Tasks', href: '/tasks' }
 main
];

export default function DashboardPage() {
  return (
 codex/create-multi-tenant-crm-and-student-portal-eww8s8
    <AppShell title="Dashboard">
      <section className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        {cards.map((card) => (
          <Link key={card.label} href={card.href} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow">
            <p className="text-sm text-slate-500">{card.label}</p>
            <p className="mt-3 text-3xl font-semibold text-slate-900">{card.value}</p>
            <p className="mt-2 text-xs text-slate-500">Open module</p>
          </Link>
        ))}
      </section>
    </AppShell>
=======
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
 main
  );
}
