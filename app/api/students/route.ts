import { NextRequest } from 'next/server';
import { tenantCreate, tenantDelete, tenantList, tenantUpdate } from '@/lib/route-helpers';

export async function GET(req: NextRequest) {
  return tenantList(req, 'student', { applications: true, documents: true });
}

export async function POST(req: NextRequest) {
  return tenantCreate(req, 'student', await req.json());
}

export async function PATCH(req: NextRequest) {
  const { id, ...payload } = await req.json();
  return tenantUpdate(req, 'student', id, payload);
}

export async function DELETE(req: NextRequest) {
  const id = req.nextUrl.searchParams.get('id')!;
  return tenantDelete(req, 'student', id);
}
