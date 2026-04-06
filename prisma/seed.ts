import { PrismaClient, RoleType } from '@prisma/client';
import { hashPassword } from '../lib/auth';
import { defaultPermissions } from '../lib/permissions';

const prisma = new PrismaClient();

async function main() {
  const tenant = await prisma.tenant.upsert({
    where: { slug: 'main' },
    update: {},
    create: { name: 'Main Tenant', slug: 'main' }
  });

  const roles = await Promise.all(
    Object.values(RoleType).map((roleType) =>
      prisma.role.upsert({
        where: { tenantId_name: { tenantId: tenant.id, name: roleType } },
        update: { permissions: defaultPermissions[roleType] },
        create: {
          tenantId: tenant.id,
          name: roleType,
          roleType,
          permissions: defaultPermissions[roleType]
        }
      })
    )
  );

  const superAdminRole = roles.find((role) => role.roleType === 'SUPER_ADMIN');
  if (!superAdminRole) throw new Error('Super admin role not found');

  await prisma.user.upsert({
    where: { tenantId_email: { tenantId: tenant.id, email: 'admincrm@vertue.com' } },
    update: { isActive: true, isDeleted: false },
    create: {
      tenantId: tenant.id,
      roleId: superAdminRole.id,
      name: 'Main Super Admin',
      email: 'admincrm@vertue.com',
      passwordHash: await hashPassword('Vertue2026')
    }
  });
}

main()
  .then(async () => prisma.$disconnect())
  .catch(async (error) => {
    console.error(error);
    await prisma.$disconnect();
    process.exit(1);
  });
