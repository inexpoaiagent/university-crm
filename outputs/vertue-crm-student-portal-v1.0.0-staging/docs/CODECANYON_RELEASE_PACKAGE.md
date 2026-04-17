# CodeCanyon Release Checklist

Use this checklist before uploading to CodeCanyon.

## 1) Product QA (must pass)
- [ ] CRM login works (`/login`)
- [ ] Student portal login works (`/portal/login`)
- [ ] Dashboard opens with no server/view errors
- [ ] Students CRUD works
- [ ] Universities CRUD works
- [ ] Applications CRUD works
- [ ] Student Requests approve/reject flow works
- [ ] Documents upload works in CRM and Portal
- [ ] Tasks module works
- [ ] Notifications module works
- [ ] Messaging (Portal <-> CRM) works
- [ ] No PHP syntax errors
- [ ] No merge-conflict markers remain (`<<<<<<<`, `=======`, `>>>>>>>`)

## 2) Security and production readiness
- [ ] `.env.example` does not expose secrets
- [ ] Production docs enforce `APP_DEBUG=false`
- [ ] Production docs enforce HTTPS + `SESSION_SECURE_COOKIE=true`
- [ ] Default demo passwords are documented to be changed immediately
- [ ] `storage/` and `bootstrap/cache/` permissions documented

## 3) Database and demo package
- [ ] `database/schema-and-seed.sql` imports on a clean database
- [ ] Demo admin credential is valid
- [ ] Demo student credential is valid
- [ ] No real client data is included
- [ ] Demo data is coherent and useful for buyer testing

## 4) Required package files
- [ ] Main source code
- [ ] `docs/CODECANYON_DOCUMENTATION.md`
- [ ] `docs/CODECANYON_ITEM_DESCRIPTION.md`
- [ ] `docs/TESTING_AND_QA.md`
- [ ] `docs/CODECANYON_SELLER_PLAYBOOK.md`
- [ ] `docs/SUPPORT_POLICY.md`
- [ ] `README.md`
- [ ] `DEPLOY-CPANEL.md`

## 5) Marketplace assets
- [ ] Thumbnail image (`80x80`)
- [ ] Main preview / cover image
- [ ] At least 5 screenshots (CRM + Portal)
- [ ] Optional video walkthrough link
- [ ] Item description copy is ready
- [ ] Tags are prepared
- [ ] Changelog section is ready

## 6) Upload ZIP structure (recommended)
```text
vertue-crm-student-portal/
|- app/
|- bootstrap/
|- config/
|- database/
|- docs/
|- public/
|- resources/
|- routes/
|- storage/
|- .env.example
|- artisan
|- composer.json
|- README.md
`- DEPLOY-CPANEL.md
```

## 7) Final pre-zip commands
Run locally before creating ZIP:
- `php artisan optimize:clear`
- `php artisan about`
- `php artisan route:list`

Then compress from project root and upload the ZIP in CodeCanyon Author Dashboard.
