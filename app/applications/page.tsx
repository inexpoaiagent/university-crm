 codex/create-multi-tenant-crm-and-student-portal-2gl9hw

 codex/create-multi-tenant-crm-and-student-portal-eww8s8
 main
'use client';

import { useEffect, useMemo, useState } from 'react';
import AppShell from '@/components/app-shell';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';

type Application = {
  id: string;
  studentId: string;
  universityId: string;
  program: string;
  intake: string;
  status: string;
};

const statuses = ['DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'ACCEPTED', 'REJECTED'];

export default function ApplicationsPage() {
  const [rows, setRows] = useState<Application[]>([]);
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState('');
  const [status, setStatus] = useState('ALL');
  const [form, setForm] = useState({
    studentId: '',
    universityId: '',
    program: '',
    intake: '',
    status: 'DRAFT'
  });

  async function loadApplications() {
    const res = await fetch('/api/applications');
    const data = await res.json();
    setRows(Array.isArray(data) ? data : []);
  }

  useEffect(() => {
    loadApplications();
  }, []);

  const filtered = useMemo(
    () => rows
      .filter((item) => status === 'ALL' ? true : item.status === status)
      .filter((item) => `${item.program} ${item.intake}`.toLowerCase().includes(query.toLowerCase())),
    [rows, query, status]
  );

  async function addApplication(e: React.FormEvent) {
    e.preventDefault();
    const res = await fetch('/api/applications', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    });

    if (res.ok) {
      setOpen(false);
      setForm({ studentId: '', universityId: '', program: '', intake: '', status: 'DRAFT' });
      loadApplications();
    }
  }

  return (
    <AppShell title="Applications">
      <div className="space-y-4">
        <div className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-4">
            <Input placeholder="Search by program/intake" value={query} onChange={(e) => setQuery(e.target.value)} />
            <Select value={status} onChange={(e) => setStatus(e.target.value)}>
              <option value="ALL">ALL</option>
              {statuses.map((item) => <option key={item} value={item}>{item}</option>)}
            </Select>
            <div />
            <Button onClick={() => setOpen(true)}>+ New Application</Button>
          </div>
        </div>

        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
          <table className="min-w-full text-sm">
            <thead className="bg-slate-50 text-left text-slate-600">
              <tr>
                <th className="px-4 py-3">Program</th>
                <th className="px-4 py-3">Intake</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Student</th>
                <th className="px-4 py-3">University</th>
              </tr>
            </thead>
            <tbody>
              {filtered.length === 0 ? (
                <tr><td className="px-4 py-4" colSpan={5}>No applications found.</td></tr>
              ) : filtered.map((item) => (
                <tr key={item.id} className="border-t border-slate-100">
                  <td className="px-4 py-3 font-medium text-slate-900">{item.program}</td>
                  <td className="px-4 py-3 text-slate-600">{item.intake}</td>
                  <td className="px-4 py-3"><span className="rounded-full bg-indigo-50 px-2 py-1 text-xs text-indigo-700">{item.status}</span></td>
                  <td className="px-4 py-3 text-slate-600">{item.studentId}</td>
                  <td className="px-4 py-3 text-slate-600">{item.universityId}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {open ? (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
          <form onSubmit={addApplication} className="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl space-y-3">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold">Create Application</h2>
              <Button type="button" variant="ghost" onClick={() => setOpen(false)}>✕</Button>
            </div>
            <Input required placeholder="Student ID" value={form.studentId} onChange={(e) => setForm({ ...form, studentId: e.target.value })} />
            <Input required placeholder="University ID" value={form.universityId} onChange={(e) => setForm({ ...form, universityId: e.target.value })} />
            <Input required placeholder="Program" value={form.program} onChange={(e) => setForm({ ...form, program: e.target.value })} />
            <Input required placeholder="Intake (e.g. Fall 2026)" value={form.intake} onChange={(e) => setForm({ ...form, intake: e.target.value })} />
            <Select value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })}>
              {statuses.map((item) => <option key={item}>{item}</option>)}
            </Select>
            <div className="flex justify-end gap-2">
              <Button type="button" variant="secondary" onClick={() => setOpen(false)}>Cancel</Button>
              <Button type="submit">Save Application</Button>
            </div>
          </form>
        </div>
      ) : null}
    </AppShell>
  );
}
 codex/create-multi-tenant-crm-and-student-portal-2gl9hw


export default function ApplicationsPage() { return <main className="p-8"><h1 className="text-2xl font-semibold">Applications</h1></main>; }
 main
 main
