<<<<<<< HEAD
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
=======
# Vertue CRM (Laravel, cPanel-ready)

Production-focused multi-tenant CRM + Student Portal backend/web app for international student recruitment agencies.

## Stack
- Laravel 10 (PHP 8.1+)
- MySQL
- JWT auth + bcrypt
- Blade UI (modern, contrast-optimized, dark mode-ready)
>>>>>>> 6f791bc (initial project setup)

## Default Super Admin
- Email: `admincrm@vertue.com`
- Password: `Vertue2026`
<<<<<<< HEAD
=======

## Key Features Included
- Multi-tenant model with `tenant_id` enforced in controllers/queries
- Role-based access (SuperAdmin/Admin/Agent/SubAgent/Student)
- Dynamic permission model (`roles`, `permissions`, `role_permissions`)
- Student, University, Application, Agent/User CRUD
- Student Requests flow with Approve / Reject / More info
- Dashboard hero + KPI cards + pipeline section
- Pipeline intelligence fields (`enroll_probability`, explainability, best next action)
- SLA automation command (`crm:sla-check`) for stale/overdue work
- Multi-language files (`en`, `tr`, `fa`)
- Multi-currency fields (USD/EUR/TRY)
- Audit log table and event writes

## Database
Import:
- `database/schema-and-seed.sql`

This SQL:
- Clears previous tables
- Creates all schema
- Inserts seed users/roles/permissions/students/applications
- Inserts requested university dataset baseline (Acibadem, Altinbas, Antalya Bilim, Istanbul Atlas, Near East, EMU)

## cPanel Deployment (No SSH)
See:
- `DEPLOY-CPANEL.md`

>>>>>>> 6f791bc (initial project setup)
