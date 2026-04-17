# Troubleshooting

## Error: No application encryption key has been specified
Fix:
```bash
php artisan key:generate
php artisan optimize:clear
```

## Error: Undefined array key "lottery"
Check `config/session.php` contains:
```php
'lottery' => [2, 100],
```
Then run:
```bash
php artisan optimize:clear
```

## Error: Target class [throttle] does not exist
Check `app/Http/Kernel.php` middleware aliases include:
```php
'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
```

## Error: Invalid credentials
Check:
- Database imported correctly
- Correct login URL used (`/login` vs `/portal/login`)
- Password hash exists in DB
- User `is_active` status is enabled where applicable

## Error: SQLSTATE[HY000] [2002] Connection refused
Check `.env`:
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## Error: 419 Page Expired
Check:
- CSRF token in forms
- Session config and write permissions
- Consistent domain/protocol (http vs https)
