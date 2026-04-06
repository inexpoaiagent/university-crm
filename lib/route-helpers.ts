import { PrismaClient } from '@prisma/client';
import { NextRequest, NextResponse } from 'next/server';
import { getAuthContext, unauthorized } from './api';

const prisma = new PrismaClient();

export async function tenantList(req: NextRequest, model: keyof PrismaClient, include?: Record<string, unknown>) {
  const context = await getAuthContext(req);
  if (!context) return unauthorized();
  const data = await (prisma[model] as any).findMany({
    where: { tenantId: context.user.tenantId },
    include,
    orderBy: { createdAt: 'desc' }
  });
  return NextResponse.json(data);
}

export async function tenantCreate(req: NextRequest, model: keyof PrismaClient, payload: Record<string, unknown>) {
  const context = await getAuthContext(req);
  if (!context) return unauthorized();

  const created = await (prisma[model] as any).create({
    data: {
      ...payload,
      tenantId: context.user.tenantId
    }
  });

  return NextResponse.json(created, { status: 201 });
}

export async function tenantUpdate(req: NextRequest, model: keyof PrismaClient, id: string, payload: Record<string, unknown>) {
  const context = await getAuthContext(req);
  if (!context) return unauthorized();

  const existing = await (prisma[model] as any).findFirst({ where: { id, tenantId: context.user.tenantId } });
  if (!existing) return NextResponse.json({ error: 'Not found' }, { status: 404 });

  const updated = await (prisma[model] as any).update({ where: { id }, data: payload });
  return NextResponse.json(updated);
}

export async function tenantDelete(req: NextRequest, model: keyof PrismaClient, id: string, soft = false) {
  const context = await getAuthContext(req);
  if (!context) return unauthorized();

  const existing = await (prisma[model] as any).findFirst({ where: { id, tenantId: context.user.tenantId } });
  if (!existing) return NextResponse.json({ error: 'Not found' }, { status: 404 });

  if (soft) {
    const deleted = await (prisma[model] as any).update({ where: { id }, data: { isDeleted: true } });
    return NextResponse.json(deleted);
  }

  await (prisma[model] as any).delete({ where: { id } });
  return NextResponse.json({ success: true });
}
