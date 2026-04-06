 codex/create-multi-tenant-crm-and-student-portal-eww8s8
'use client';

import { useEffect, useMemo, useState } from 'react';
import AppShell from '@/components/app-shell';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';

type University = {
  id: string;
  name: string;
  country: string;
  website?: string;
  tuitionMin?: number;
  tuitionMax?: number;
  language?: string;
  programs: string[];
  deadline?: string;
};

const countries = ['ALL', 'Turkey', 'Northern Cyprus'];
const pageSize = 8;

export default function UniversitiesPage() {
  const [rows, setRows] = useState<University[]>([]);
  const [query, setQuery] = useState('');
  const [country, setCountry] = useState('ALL');
  const [sort, setSort] = useState<'name' | 'country' | 'language'>('name');
  const [page, setPage] = useState(1);
  const [open, setOpen] = useState(false);
  const [step, setStep] = useState(1);
  const [form, setForm] = useState({
    name: '',
    country: 'Turkey',
    website: '',
    tuitionMin: '',
    tuitionMax: '',
    language: 'English',
    programs: '',
    deadline: ''
  });

  async function loadUniversities() {
    const res = await fetch('/api/universities');
    const data = await res.json();
    setRows(Array.isArray(data) ? data : []);
  }

  useEffect(() => {
    loadUniversities();
  }, []);

  const filtered = useMemo(() => {
    return rows
      .filter((item) => `${item.name} ${item.language ?? ''}`.toLowerCase().includes(query.toLowerCase()))
      .filter((item) => country === 'ALL' ? true : item.country === country)
      .sort((a, b) => (a[sort] ?? '').localeCompare(b[sort] ?? ''));
  }, [rows, query, country, sort]);

  const pages = Math.max(1, Math.ceil(filtered.length / pageSize));
  const paged = filtered.slice((page - 1) * pageSize, page * pageSize);

  async function addUniversity(e: React.FormEvent) {
    e.preventDefault();

    const payload = {
      name: form.name,
      country: form.country,
      website: form.website || null,
      tuitionMin: form.tuitionMin ? Number(form.tuitionMin) : null,
      tuitionMax: form.tuitionMax ? Number(form.tuitionMax) : null,
      language: form.language,
      programs: form.programs.split(',').map((p) => p.trim()).filter(Boolean),
      deadline: form.deadline || null
    };

    const res = await fetch('/api/universities', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      setOpen(false);
      setStep(1);
      setForm({
        name: '',
        country: 'Turkey',
        website: '',
        tuitionMin: '',
        tuitionMax: '',
        language: 'English',
        programs: '',
        deadline: ''
      });
      loadUniversities();
    }
  }

  return (
    <AppShell title="Universities">
      <div className="space-y-4">
        <div className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="grid grid-cols-1 gap-3 lg:grid-cols-4">
            <Input placeholder="Search university or language" value={query} onChange={(e) => setQuery(e.target.value)} />
            <Select value={country} onChange={(e) => setCountry(e.target.value)}>
              {countries.map((item) => <option key={item} value={item}>{item}</option>)}
            </Select>
            <Select value={sort} onChange={(e) => setSort(e.target.value as 'name' | 'country' | 'language')}>
              <option value="name">Sort: Name</option>
              <option value="country">Sort: Country</option>
              <option value="language">Sort: Language</option>
            </Select>
            <Button onClick={() => setOpen(true)}>+ Add University</Button>
          </div>
        </div>

        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
          <table className="min-w-full text-sm">
            <thead className="bg-slate-50 text-left text-slate-600">
              <tr>
                <th className="px-4 py-3">University</th>
                <th className="px-4 py-3">Country</th>
                <th className="px-4 py-3">Language</th>
                <th className="px-4 py-3">Tuition</th>
                <th className="px-4 py-3">Programs</th>
              </tr>
            </thead>
            <tbody>
              {paged.length === 0 ? (
                <tr><td className="px-4 py-4" colSpan={5}>No universities found.</td></tr>
              ) : paged.map((item) => (
                <tr key={item.id} className="border-t border-slate-100">
                  <td className="px-4 py-3">
                    <p className="font-medium text-slate-900">{item.name}</p>
                    {item.website ? <a href={item.website} target="_blank" rel="noreferrer" className="text-xs text-slate-500 underline">Website</a> : null}
                  </td>
                  <td className="px-4 py-3 text-slate-600">{item.country}</td>
                  <td className="px-4 py-3 text-slate-600">{item.language ?? '-'}</td>
                  <td className="px-4 py-3 text-slate-600">{item.tuitionMin ?? '-'} - {item.tuitionMax ?? '-'}</td>
                  <td className="px-4 py-3 text-slate-600">{item.programs?.slice(0, 2).join(', ') || '-'}</td>
                </tr>
              ))}
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
          <form onSubmit={addUniversity} className="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold">Add University</h2>
              <Button type="button" variant="ghost" onClick={() => { setOpen(false); setStep(1); }}>✕</Button>
            </div>

            <div className="h-2 w-full overflow-hidden rounded-full bg-slate-100">
              <div className={`h-full bg-slate-900 transition-all ${step === 1 ? 'w-1/2' : 'w-full'}`} />
            </div>

            {step === 1 ? (
              <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                <Input required placeholder="University Name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                <Select value={form.country} onChange={(e) => setForm({ ...form, country: e.target.value })}>
                  <option>Turkey</option>
                  <option>Northern Cyprus</option>
                </Select>
                <Input placeholder="Website" value={form.website} onChange={(e) => setForm({ ...form, website: e.target.value })} className="md:col-span-2" />
                <div className="md:col-span-2 flex justify-end"><Button type="button" onClick={() => setStep(2)}>Next</Button></div>
              </div>
            ) : (
              <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                <Input placeholder="Tuition Min" type="number" value={form.tuitionMin} onChange={(e) => setForm({ ...form, tuitionMin: e.target.value })} />
                <Input placeholder="Tuition Max" type="number" value={form.tuitionMax} onChange={(e) => setForm({ ...form, tuitionMax: e.target.value })} />
                <Input placeholder="Language" value={form.language} onChange={(e) => setForm({ ...form, language: e.target.value })} />
                <Input placeholder="Deadline (YYYY-MM-DD)" value={form.deadline} onChange={(e) => setForm({ ...form, deadline: e.target.value })} />
                <Input placeholder="Programs (comma separated)" value={form.programs} onChange={(e) => setForm({ ...form, programs: e.target.value })} className="md:col-span-2" />
                <div className="md:col-span-2 flex justify-between gap-2">
                  <Button type="button" variant="secondary" onClick={() => setStep(1)}>Back</Button>
                  <Button type="submit">Save University</Button>
                </div>
              </div>
            )}
          </form>
        </div>
      ) : null}
    </AppShell>
  );
}
=======
export default function UniversitiesPage() { return <main className="p-8"><h1 className="text-2xl font-semibold">Universities</h1></main>; }
 main
