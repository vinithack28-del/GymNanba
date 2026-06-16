Tenant-specific gym module migrations should live in this directory.

When a tenant is created with `domain_mode=separate` and `database_mode=separate`,
the app provisions the database and runs `php artisan migrate --database=tenant --path=database/migrations/tenant`.
