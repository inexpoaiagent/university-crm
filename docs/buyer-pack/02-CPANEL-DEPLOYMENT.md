# cPanel Deployment Guide (No Terminal Friendly)

## 1) Upload Files
Upload project files to your account, for example:
- `/home/USERNAME/public_html/laravel-crm-cpanel`

## 2) Point Web Root
Point your domain/subdomain document root to the Laravel `public` folder.

## 3) Create Database + User
In cPanel:
- Create MySQL database
- Create user
- Assign full privileges

## 4) Import SQL
Use phpMyAdmin to import:
- `database/schema-and-seed.sql`

## 5) Configure `.env`
Edit `.env` with production values:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- DB credentials
- `SESSION_SECURE_COOKIE=true`

## 6) Permissions
Ensure writable:
- `storage/`
- `bootstrap/cache/`

Typical permissions:
- directories `755`
- files `644`

## 7) Cache Clear (if terminal exists)
```bash
php artisan optimize:clear
```

If terminal is not available, use a temporary maintenance route/script and remove it immediately after use.

## 8) Final Smoke Test
- CRM login works
- Portal login works
- Dashboard loads
- Document upload works
