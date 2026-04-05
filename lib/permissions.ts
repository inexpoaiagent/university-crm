import { RoleType } from '@prisma/client';

export const defaultPermissions: Record<RoleType, string[]> = {
  SUPER_ADMIN: ['*'],
  ADMIN: [
    'users.manage',
    'students.manage',
    'applications.manage',
    'universities.manage',
    'scholarships.manage',
    'documents.verify',
    'finance.manage',
    'tasks.manage'
  ],
  AGENT: ['students.manage_own', 'subagents.manage', 'applications.manage_own', 'tasks.manage_own'],
  SUB_AGENT: ['students.view_assigned', 'applications.view_assigned', 'tasks.manage_own'],
  STUDENT: ['portal.access', 'documents.upload', 'applications.view_own']
};

export function hasPermission(granted: string[], required: string): boolean {
  return granted.includes('*') || granted.includes(required);
}
