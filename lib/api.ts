import { NextRequest, NextResponse } from 'next/server';
import { prisma } from './prisma';
import { getTokenFromRequest, verifyToken } from './auth';

export async function getAuthContext(req: NextRequest) {
  const token = getTokenFromRequest(req);
  if (!token) return null;

  try {
    const session = verifyToken(token);
    const user = await prisma.user.findFirst({
      where: { id: session.userId, tenantId: session.tenantId, isActive: true, isDeleted: false },
      include: { role: true }
    });
    if (!user) return null;
    return { session, user };
  } catch {
    return null;
  }
}

export function unauthorized() {
  return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
}

export function forbidden() {
  return NextResponse.json({ error: 'Forbidden' }, { status: 403 });
}
