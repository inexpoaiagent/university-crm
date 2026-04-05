import { NextRequest, NextResponse } from 'next/server';
import { getAuthContext, unauthorized } from '@/lib/api';
import { hashPassword } from '@/lib/auth';
import { prisma } from '@/lib/prisma';

export async function PATCH(req: NextRequest) {
  const context = await getAuthContext(req);
  if (!context) return unauthorized();

  const body = await req.json();
  const data: Record<string, unknown> = {};

  if (body.name) data.name = body.name;
  if (body.language) data.language = body.language;
  if (body.password) data.passwordHash = await hashPassword(body.password);

  const updated = await prisma.user.update({ where: { id: context.user.id }, data });
  return NextResponse.json(updated);
}
