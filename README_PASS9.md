# Titan Zero Pass 9 — Coach Metadata + Registry

## Adds
- Coaches registry + admin editor
- Document tags + metadata editor
- Retrieval filters per coach (include/exclude tags, superseded, etc)
- Account coaches pages

## URLs
- /account/titan/zero/coaches
- /dashboard/admin/settings/titan-zero/coaches
- Document Metadata: /dashboard/admin/settings/titan-zero/library/documents/{id}/meta

## Install
php artisan module:migrate TitanZero
php artisan db:seed --class="Modules\TitanZero\Database\Seeders\TitanZeroTagSeeder"
php artisan db:seed --class="Modules\TitanZero\Database\Seeders\TitanZeroCoachSeeder"
