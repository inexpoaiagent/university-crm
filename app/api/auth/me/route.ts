import { NextRequest, NextResponse } from 'next/server';
import { getAuthContext, unauthorized } from '@/lib/api';

export async function GET(req: NextRequest) {
  const context = await getAuthContext(req);
  if (!context) return unauthorized();
  return NextResponse.json({
    id: context.user.id,
    name: context.user.name,
    email: context.user.email,
    role: context.user.role.roleType,
    tenantId: context.user.tenantId
  });
}
