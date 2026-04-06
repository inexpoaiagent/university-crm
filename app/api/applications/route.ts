import { NextRequest } from 'next/server';
import { tenantCreate, tenantDelete, tenantList, tenantUpdate } from '@/lib/route-helpers';

export async function GET(req: NextRequest) { return tenantList(req, 'application', { student: true, university: true }); }
export async function POST(req: NextRequest) { return tenantCreate(req, 'application', await req.json()); }
export async function PATCH(req: NextRequest) { const { id, ...payload } = await req.json(); return tenantUpdate(req, 'application', id, payload); }
export async function DELETE(req: NextRequest) { const id = req.nextUrl.searchParams.get('id')!; return tenantDelete(req, 'application', id); }
