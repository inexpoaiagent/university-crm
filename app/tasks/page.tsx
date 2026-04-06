import AppShell from '@/components/app-shell';

export default function TasksPage() {
  return (
    <AppShell title="Tasks & Reminders">
      <div className="rounded-2xl border border-slate-200 bg-white p-6">
        <p className="text-slate-600">Create assignments, set deadlines, and monitor overdue actions.</p>
      </div>
    </AppShell>
  );
}
