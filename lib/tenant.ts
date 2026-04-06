import { NextRequest } from 'next/server';

export function getTenantFromHeader(req: NextRequest): string | null {
  return req.headers.get('x-tenant-id');
}
