import { NextRequest, NextResponse } from 'next/server';
import { verifyToken } from './lib/auth';

const publicPaths = ['/login', '/portal/login', '/api/auth/login'];

export function middleware(req: NextRequest) {
  const { pathname } = req.nextUrl;
  if (publicPaths.includes(pathname) || pathname.startsWith('/_next')) {
    return NextResponse.next();
  }

  const token = req.cookies.get('crm_session')?.value;
  if (!token) {
    return NextResponse.redirect(new URL(pathname.startsWith('/portal') ? '/portal/login' : '/login', req.url));
  }

  try {
    const payload = verifyToken(token);
    if (pathname.startsWith('/portal') && payload.role !== 'STUDENT') {
      return NextResponse.redirect(new URL('/dashboard', req.url));
    }
    return NextResponse.next();
  } catch {
    return NextResponse.redirect(new URL('/login', req.url));
  }
}

export const config = {
  matcher: ['/((?!api/public|_next/static|_next/image|favicon.ico).*)']
};
