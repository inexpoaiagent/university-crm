import Link from 'next/link';
import AppShell from '@/components/app-shell';

const cards = [
  { label: 'Total Students', value: '—', href: '/students' },
  { label: 'Applications', value: '—', href: '/applications' },
  { label: 'Pending Documents', value: '—', href: '/documents' },
  { label: 'Universities', value: '—', href: '/universities' }
];

export default function DashboardPage() {
  return (
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
  );
}
