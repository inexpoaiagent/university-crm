# University CRM + Student Portal

Production-focused multi-tenant CRM SaaS for international student recruitment.

## Features
- Multi-tenant architecture with strict tenant scoping.
- JWT + bcrypt authentication.
- Role and permission model (SuperAdmin, Admin, Agent, SubAgent, Student).
- CRUD APIs for users, students, universities, applications, documents, scholarships, tasks, notifications, requests, finance, roles.
- Student portal login and dashboard.
- Scholarship and smart university matching endpoint.
- Settings/profile updates and password change endpoint.
- i18n JSON dictionaries (EN/TR/FA).

## Setup
1. Copy `.env.example` to `.env`.
2. Install dependencies: `npm install`
3. Generate Prisma client: `npm run prisma:generate`
4. Run migrations: `npm run prisma:migrate`
5. Seed default tenant + super admin: `npm run prisma:seed`
6. Run app: `npm run dev`

## Default Super Admin
- Email: `admincrm@vertue.com`
- Password: `Vertue2026`
