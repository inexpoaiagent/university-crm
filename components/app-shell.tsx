'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Bell, GraduationCap, LayoutDashboard, School, FileText, Users } from 'lucide-react';

const navItems = [
  { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { href: '/students', label: 'Students', icon: Users },
  { href: '/documents', label: 'Documents', icon: FileText },
  { href: '/applications', label: 'Applications', icon: GraduationCap },
  { href: '/universities', label: 'Universities', icon: School }
];

export default function AppShell({ title, children }: { title: string; children: React.ReactNode }) {
  const pathname = usePathname();

  return (
    <div className="min-h-screen bg-slate-100">
      <div className="mx-auto flex max-w-[1400px] gap-6 p-4 md:p-6">
        <aside className="hidden w-72 shrink-0 rounded-2xl bg-slate-900 p-5 text-slate-200 md:block">
          <h2 className="mb-6 text-lg font-semibold">Vertue CRM</h2>
          <nav className="space-y-1">
            {navItems.map((item) => {
              const Icon = item.icon;
              const active = pathname === item.href;
              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition ${active ? 'bg-white/15 text-white' : 'hover:bg-white/10 hover:text-white'}`}
                >
                  <Icon size={16} />
                  {item.label}
                </Link>
              );
            })}
          </nav>
        </aside>

        <div className="flex-1">
          <header className="mb-4 flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4">
            <div>
              <h1 className="text-xl font-semibold text-slate-900">{title}</h1>
              <p className="text-xs text-slate-500">Multi-tenant university recruitment platform</p>
            </div>
            <button className="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50">
              <Bell size={18} />
            </button>
          </header>
          {children}
        </div>
      </div>
    </div>
  );
}
