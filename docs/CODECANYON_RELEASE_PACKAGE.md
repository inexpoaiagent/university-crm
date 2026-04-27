# CodeCanyon Release Checklist

Use this checklist before uploading the item to CodeCanyon.

## 1) Final Product Validation
- [ ] CRM login works (`/login`)
- [ ] Student portal login works (`/portal/login`)
- [ ] Dashboard opens without error
- [ ] Students CRUD works
- [ ] Universities CRUD works
- [ ] Applications CRUD works
- [ ] Student Requests approve/reject flow works
- [ ] Documents upload works (CRM + Portal)
- [ ] Tasks module works
- [ ] Notifications page works
- [ ] Messaging (Portal <-> CRM) works
- [ ] No PHP syntax errors (`php -l`)
- [ ] No merge-conflict markers in source files

## 2) Environment Readiness
- [ ] `.env.example` is clean and complete
- [ ] `APP_DEBUG=false` in production example
- [ ] `SESSION_SECURE_COOKIE=true` in production docs
- [ ] Storage symlink steps documented (`php artisan storage:link`)
- [ ] Cache clear steps documented (`php artisan optimize:clear`)

## 3) Database & Demo Data
- [ ] `database/schema-and-seed.sql` imports on clean DB
- [ ] Demo admin credential works
- [ ] Demo student credential works
- [ ] Demo data is realistic and safe
- [ ] No personal/private real customer data included

## 4) Package Content
- [ ] Main source code included
- [ ] `docs/CODECANYON_DOCUMENTATION.md` included
- [ ] `docs/TESTING_AND_QA.md` included
- [ ] `DEPLOY-CPANEL.md` included
- [ ] `README.md` updated
- [ ] License file/policy included

## 5) CodeCanyon Upload Assets
- [ ] ZIP package prepared
- [ ] Main thumbnail prepared (80x80)
- [ ] Preview image(s) prepared
- [ ] Optional live demo URL prepared
- [ ] Sales copy prepared (see `docs/CODECANYON_ITEM_DESCRIPTION.md`)
- [ ] Support policy prepared
- [ ] Changelog prepared

## 6) Recommended ZIP Structure
```
vertue-crm-student-portal/
├─ app/
├─ bootstrap/
├─ config/
├─ database/
├─ docs/
├─ public/
├─ resources/
├─ routes/
├─ storage/
├─ .env.example
├─ artisan
├─ composer.json
├─ README.md
└─ DEPLOY-CPANEL.md
```

## 7) Final Commands Before Packaging
Run locally (not inside the uploaded ZIP):
- `php artisan optimize:clear`
- `php artisan about`
- `php artisan route:list`

Then create final upload ZIP from project root.

