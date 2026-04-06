 codex/create-multi-tenant-crm-and-student-portal-2gl9hw

 codex/create-multi-tenant-crm-and-student-portal-eww8s8
 main
'use client';

import { useEffect, useMemo, useState } from 'react';
import AppShell from '@/components/app-shell';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';

type Student = {
  id: string;
  fullName: string;
  email: string;
  phone?: string;
  stage: string;
  nationality?: string;
};

const stageOptions = ['ALL', 'LEAD', 'CONTACTED', 'QUALIFIED', 'APPLIED', 'ADMITTED', 'ENROLLED'];
const pageSize = 8;

export default function StudentsPage() {
  const [students, setStudents] = useState<Student[]>([]);
  const [loading, setLoading] = useState(true);
  const [open, setOpen] = useState(false);
  const [q, setQ] = useState('');
  const [stageFilter, setStageFilter] = useState('ALL');
  const [sortBy, setSortBy] = useState<'fullName' | 'email' | 'stage'>('fullName');
  const [page, setPage] = useState(1);
  const [step, setStep] = useState(1);
  const [form, setForm] = useState({ fullName: '', email: '', phone: '', nationality: '', stage: 'LEAD' });

  async function loadStudents() {
    setLoading(true);
    const res = await fetch('/api/students');
    const data = await res.json();
    setStudents(Array.isArray(data) ? data : []);
    setLoading(false);
  }

  useEffect(() => {
    loadStudents();
  }, []);

  const filtered = useMemo(() => {
    const base = students
      .filter((s) => `${s.fullName} ${s.email}`.toLowerCase().includes(q.toLowerCase()))
      .filter((s) => stageFilter === 'ALL' ? true : s.stage === stageFilter)
      .sort((a, b) => a[sortBy].localeCompare(b[sortBy]));

    return base;
  }, [students, q, stageFilter, sortBy]);

  const pages = Math.max(1, Math.ceil(filtered.length / pageSize));
  const paged = filtered.slice((page - 1) * pageSize, page * pageSize);

  useEffect(() => {
    if (page > pages) setPage(1);
  }, [page, pages]);

  async function addStudent(e: React.FormEvent) {
    e.preventDefault();
    const res = await fetch('/api/students', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    });
    if (res.ok) {
      setOpen(false);
      setStep(1);
      setForm({ fullName: '', email: '', phone: '', nationality: '', stage: 'LEAD' });
      loadStudents();
    }
  }

  return (
    <AppShell title="Students">
      <div className="space-y-4">
        <div className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-4">
            <Input placeholder="Search by name or email" value={q} onChange={(e) => setQ(e.target.value)} />
            <Select value={stageFilter} onChange={(e) => setStageFilter(e.target.value)}>
              {stageOptions.map((stage) => <option key={stage} value={stage}>{stage}</option>)}
            </Select>
            <Select value={sortBy} onChange={(e) => setSortBy(e.target.value as 'fullName' | 'email' | 'stage')}>
              <option value="fullName">Sort: Name</option>
              <option value="email">Sort: Email</option>
              <option value="stage">Sort: Stage</option>
            </Select>
            <Button onClick={() => setOpen(true)}>+ Add Student</Button>
          </div>
        </div>

        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
          <table className="min-w-full text-sm">
            <thead className="bg-slate-50 text-left text-slate-600">
              <tr>
                <th className="px-4 py-3">Name</th>
                <th className="px-4 py-3">Email</th>
                <th className="px-4 py-3">Phone</th>
                <th className="px-4 py-3">Stage</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td className="px-4 py-4" colSpan={4}>Loading...</td></tr>
              ) : paged.length === 0 ? (
                <tr><td className="px-4 py-4" colSpan={4}>No students found.</td></tr>
              ) : (
                paged.map((student) => (
                  <tr key={student.id} className="border-t border-slate-100">
                    <td className="px-4 py-3 font-medium text-slate-900">{student.fullName}</td>
                    <td className="px-4 py-3 text-slate-600">{student.email}</td>
                    <td className="px-4 py-3 text-slate-600">{student.phone ?? '-'}</td>
                    <td className="px-4 py-3"><span className="rounded-full bg-slate-100 px-2 py-1 text-xs">{student.stage}</span></td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm">
          <p className="text-slate-500">Showing {paged.length} of {filtered.length}</p>
          <div className="flex items-center gap-2">
            <Button variant="secondary" disabled={page <= 1} onClick={() => setPage((p) => p - 1)}>Prev</Button>
            <span className="text-slate-600">Page {page} / {pages}</span>
            <Button variant="secondary" disabled={page >= pages} onClick={() => setPage((p) => p + 1)}>Next</Button>
          </div>
        </div>
      </div>

      {open ? (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
          <form onSubmit={addStudent} className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold">Add New Student</h2>
              <Button type="button" variant="ghost" onClick={() => { setOpen(false); setStep(1); }}>✕</Button>
            </div>

            <div className="h-2 w-full overflow-hidden rounded-full bg-slate-100">
              <div className={`h-full bg-slate-900 transition-all ${step === 1 ? 'w-1/2' : 'w-full'}`} />
            </div>

            {step === 1 ? (
              <>
                <Input placeholder="Full name" required value={form.fullName} onChange={(e) => setForm({ ...form, fullName: e.target.value })} />
                <Input placeholder="Email" type="email" required value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                <div className="flex justify-end"><Button type="button" onClick={() => setStep(2)}>Next</Button></div>
              </>
            ) : (
              <>
                <Input placeholder="Phone" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                <Input placeholder="Nationality" value={form.nationality} onChange={(e) => setForm({ ...form, nationality: e.target.value })} />
                <Select value={form.stage} onChange={(e) => setForm({ ...form, stage: e.target.value })}>
                  {stageOptions.filter((s) => s !== 'ALL').map((stage) => <option key={stage} value={stage}>{stage}</option>)}
                </Select>
                <div className="flex justify-between gap-2">
                  <Button type="button" variant="secondary" onClick={() => setStep(1)}>Back</Button>
                  <Button type="submit">Save Student</Button>
                </div>
              </>
            )}
          </form>
        </div>
      ) : null}
    </AppShell>
  );
}
 codex/create-multi-tenant-crm-and-student-portal-2gl9hw


export default function StudentsPage() { return <main className="p-8"><h1 className="text-2xl font-semibold">Students</h1></main>; }
 main
 main
