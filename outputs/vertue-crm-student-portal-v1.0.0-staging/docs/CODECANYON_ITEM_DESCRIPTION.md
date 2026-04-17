# CodeCanyon Item Description (Ready to Paste)

## Item Title
Vertue CRM + Student Portal for Education Agencies (Laravel)

## Category Suggestion
CodeCanyon > PHP Scripts > Project Management Tools

## Price Recommendation
- Regular License: **$59**
- Extended License: keep Envato default multiplier

Why $59:
- Competitive for Laravel CRM products with real modules
- High enough to signal quality and include support
- Good conversion point for first 30-60 days

## Short Description
Vertue CRM is a production-ready Laravel SaaS for education agencies with a separate student portal.  
Manage leads, students, applications, universities, tasks, documents, notifications, and messaging in one secure multi-tenant system.

## Full Description
Build and scale your student recruitment operations with a complete CRM and Student Portal designed for real agency workflows.

Vertue CRM includes:
- CRM workspace for Super Admin, Admin, Agent, and Sub-Agent
- Secure Student Portal with separate authentication
- Full admissions flow from lead to enrolled
- Document collection and verification workflow
- Task assignment and follow-up management
- University and program tracking
- Messaging between students and CRM staff
- Role-based access control and tenant isolation

This is not a static demo. Core modules are connected to database-backed CRUD and workflow logic.

## Core Features
- Multi-tenant architecture with `tenant_id` isolation
- Role-based access (Super Admin, Admin, Agent, Sub-Agent, Student)
- Secure authentication with bcrypt-hashed passwords
- Students CRUD
- Universities CRUD
- Applications CRUD
- Student requests (apply, review, approve/reject)
- Document management (upload/verify/replace)
- Tasks and priority workflow
- Notifications center
- Scholarships module
- Finance module
- Internal audit-ready structure

## Student Portal Features
- Login at `/portal/login`
- Personal dashboard
- Application status tracking
- University browsing and apply request
- Required document uploads
- Messaging with assigned staff

## Tech Stack
- Laravel 10
- PHP 8.1+
- MySQL
- Blade frontend
- REST-style portal APIs

## Package Includes
- Full source code
- SQL schema + demo data: `database/schema-and-seed.sql`
- cPanel deployment guide
- Complete install and usage docs
- QA checklist

## Demo Credentials
CRM Admin:
- Email: `admincrm@vertue.com`
- Password: `Vertue2026`

Student Portal:
- Email: `priya@example.com`
- Password: `Student123!`

## Installation Summary
1. Upload source files
2. Configure `.env`
3. Import `database/schema-and-seed.sql`
4. Run:
   - `composer install`
   - `php artisan key:generate`
   - `php artisan storage:link`
   - `php artisan optimize:clear`
5. Open CRM and Portal URLs

Full documentation:
- `docs/CODECANYON_DOCUMENTATION.md`

## Support Scope
Includes:
- Installation help
- Reproducible bug fixes for bundled features
- Clarification for documented modules

Excludes:
- Custom feature development
- Third-party service integrations not in package
- Server management outside Laravel app scope

## Changelog
### v1.0.0
- Initial public release for CodeCanyon
- CRM + Student Portal modules included
- Multi-tenant + role access baseline
- Demo SQL and deployment docs included
