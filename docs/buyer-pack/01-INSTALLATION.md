# Installation Guide (Local / VPS)

## 1) Upload / Copy Files
Copy the project to your server path.

Example (local):
- `C:\xampp\htdocs\laravel-crm-cpanel`

## 2) Create Database
Create a new MySQL database in phpMyAdmin or your server panel.

## 3) Import Demo SQL
Import:
- `database/schema-and-seed.sql`

This file creates schema and demo data.

## 4) Configure Environment
Copy `.env.example` to `.env` and update:
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `SESSION_SECURE_COOKIE`

## 5) Install Dependencies
Run:
```bash
composer install
php artisan key:generate
php artisan storage:link
php artisan optimize:clear
```

## 6) Run Application
```bash
php artisan serve
```

Open:
- `http://127.0.0.1:8000/login`
- `http://127.0.0.1:8000/portal/login`

## 7) Production Essentials
- `APP_ENV=production`
- `APP_DEBUG=false`
- HTTPS enabled
- `SESSION_SECURE_COOKIE=true`
