import { NextRequest, NextResponse } from 'next/server';
import { getAuthContext, unauthorized } from '@/lib/api';
import { prisma } from '@/lib/prisma';
import { scoreUniversity } from '@/lib/matching';

export async function GET(req: NextRequest) {
  const context = await getAuthContext(req);
  if (!context) return unauthorized();
  const studentId = req.nextUrl.searchParams.get('studentId');
  if (!studentId) return NextResponse.json({ error: 'studentId required' }, { status: 400 });

  const student = await prisma.student.findFirst({ where: { id: studentId, tenantId: context.user.tenantId } });
  if (!student) return NextResponse.json({ error: 'Student not found' }, { status: 404 });

  const universities = await prisma.university.findMany({ where: { tenantId: context.user.tenantId } });
  const recommendations = universities
    .map((university) => ({ ...university, score: scoreUniversity(student, university) }))
    .sort((first, second) => second.score - first.score)
    .slice(0, 5);

  return NextResponse.json(recommendations);
}
