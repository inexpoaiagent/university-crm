'use client';

import { useEffect, useState } from 'react';
import AppShell from '@/components/app-shell';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';

type DocumentItem = {
  id: string;
  fileName: string;
  fileUrl: string;
  type: string;
  status: string;
  studentId: string;
};

const types = ['Passport', 'Diploma', 'Transcript', 'English Certificate', 'Photo'];

export default function DocumentsPage() {
  const [rows, setRows] = useState<DocumentItem[]>([]);
  const [studentId, setStudentId] = useState('');
  const [fileName, setFileName] = useState('');
  const [fileUrl, setFileUrl] = useState('');
  const [type, setType] = useState(types[0]);

  async function loadDocs() {
    const res = await fetch('/api/documents');
    const data = await res.json();
    setRows(Array.isArray(data) ? data : []);
  }

  useEffect(() => {
    loadDocs();
  }, []);

  async function addDocument(e: React.FormEvent) {
    e.preventDefault();
    const res = await fetch('/api/documents', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ studentId, fileName, fileUrl, type, status: 'UPLOADED' })
    });
    if (res.ok) {
      setStudentId('');
      setFileName('');
      setFileUrl('');
      loadDocs();
    }
  }

  return (
    <AppShell title="Documents">
      <div className="grid grid-cols-1 gap-4 xl:grid-cols-[380px,1fr]">
        <form onSubmit={addDocument} className="rounded-2xl border border-slate-200 bg-white p-5 space-y-3">
          <h2 className="text-base font-semibold">Quick Add Document</h2>
          <Input required placeholder="Student ID" value={studentId} onChange={(e) => setStudentId(e.target.value)} />
          <Input required placeholder="File Name" value={fileName} onChange={(e) => setFileName(e.target.value)} />
          <Input required placeholder="File URL" value={fileUrl} onChange={(e) => setFileUrl(e.target.value)} />
          <Select value={type} onChange={(e) => setType(e.target.value)}>
            {types.map((item) => <option key={item}>{item}</option>)}
          </Select>
          <Button type="submit" className="w-full">Add Document</Button>
        </form>

        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
          <table className="min-w-full text-sm">
            <thead className="bg-slate-50 text-left text-slate-600">
              <tr>
                <th className="px-4 py-3">File</th>
                <th className="px-4 py-3">Type</th>
                <th className="px-4 py-3">Status</th>
                <th className="px-4 py-3">Student</th>
              </tr>
            </thead>
            <tbody>
              {rows.length === 0 ? (
                <tr><td className="px-4 py-4" colSpan={4}>No documents yet.</td></tr>
              ) : rows.map((doc) => (
                <tr key={doc.id} className="border-t border-slate-100">
                  <td className="px-4 py-3">
                    <a href={doc.fileUrl} target="_blank" className="font-medium text-slate-900 underline" rel="noreferrer">{doc.fileName}</a>
                  </td>
                  <td className="px-4 py-3 text-slate-600">{doc.type}</td>
                  <td className="px-4 py-3"><span className="rounded-full bg-emerald-50 px-2 py-1 text-xs text-emerald-700">{doc.status}</span></td>
                  <td className="px-4 py-3 text-slate-600">{doc.studentId}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </AppShell>
  );
}
