import { NextRequest } from 'next/server';
import { tenantCreate, tenantDelete, tenantList, tenantUpdate } from '@/lib/route-helpers';

export async function GET(req: NextRequest) { return tenantList(req, 'payment', { student: true }); }
export async function POST(req: NextRequest) {
  const body = await req.json();
  if (body.type === 'COMMISSION' && body.amount == null && body.baseAmount && body.commissionRate) {
    body.amount = body.baseAmount * body.commissionRate;
  }
  return tenantCreate(req, 'payment', body);
}
export async function PATCH(req: NextRequest) { const { id, ...payload } = await req.json(); return tenantUpdate(req, 'payment', id, payload); }
export async function DELETE(req: NextRequest) { const id = req.nextUrl.searchParams.get('id')!; return tenantDelete(req, 'payment', id); }
