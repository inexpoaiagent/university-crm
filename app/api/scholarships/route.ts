import { NextRequest } from 'next/server';
import { tenantCreate, tenantDelete, tenantList, tenantUpdate } from '@/lib/route-helpers';

export async function GET(req: NextRequest) { return tenantList(req, 'scholarship', { university: true }); }
export async function POST(req: NextRequest) { return tenantCreate(req, 'scholarship', await req.json()); }
export async function PATCH(req: NextRequest) { const { id, ...payload } = await req.json(); return tenantUpdate(req, 'scholarship', id, payload); }
export async function DELETE(req: NextRequest) { const id = req.nextUrl.searchParams.get('id')!; return tenantDelete(req, 'scholarship', id); }
