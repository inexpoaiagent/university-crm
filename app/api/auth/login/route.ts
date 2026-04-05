import { NextRequest, NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';
import { setSessionCookie, signToken, verifyPassword } from '@/lib/auth';

export async function POST(req: NextRequest) {
  const { email, password, portal } = await req.json();
  const user = await prisma.user.findFirst({
    where: { email, isDeleted: false, isActive: true },
    include: { role: true }
  });

  if (!user) return NextResponse.json({ error: 'Invalid credentials' }, { status: 401 });

  const valid = await verifyPassword(password, user.passwordHash);
  if (!valid) return NextResponse.json({ error: 'Invalid credentials' }, { status: 401 });

  if (portal && user.role.roleType !== 'STUDENT') {
    return NextResponse.json({ error: 'Portal access only for students' }, { status: 403 });
  }

  const token = signToken({ userId: user.id, tenantId: user.tenantId, role: user.role.roleType });
  await setSessionCookie(token);

  return NextResponse.json({ success: true, role: user.role.roleType });
}
