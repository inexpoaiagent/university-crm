import { NextRequest, NextResponse } from 'next/server';
import { hashPassword } from '@/lib/auth';
import { getAuthContext, unauthorized } from '@/lib/api';
import { prisma } from '@/lib/prisma';
import { tenantDelete, tenantList, tenantUpdate } from '@/lib/route-helpers';

export async function GET(req: NextRequest) {
  return tenantList(req, 'user', { role: true });
}

export async function POST(req: NextRequest) {
  const context = await getAuthContext(req);
  if (!context) return unauthorized();

  const body = await req.json();
  const role = await prisma.role.findFirst({ where: { id: body.roleId, tenantId: context.user.tenantId } });
  if (!role) return NextResponse.json({ error: 'Role not found for tenant' }, { status: 400 });

  const passwordHash = await hashPassword(body.password);
  const created = await prisma.user.create({
    data: {
      tenantId: context.user.tenantId,
      roleId: body.roleId,
      name: body.name,
      email: body.email,
      passwordHash
    }
  });
  return Response.json(created, { status: 201 });
}

export async function PATCH(req: NextRequest) {
  const { id, ...payload } = await req.json();
  if (payload.password) {
    payload.passwordHash = await hashPassword(payload.password as string);
    delete payload.password;
  }
  return tenantUpdate(req, 'user', id, payload);
}

export async function DELETE(req: NextRequest) {
  const id = req.nextUrl.searchParams.get('id')!;
  return tenantDelete(req, 'user', id, true);
}
