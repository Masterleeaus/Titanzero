# Titan Zero Pass 11 — Classifier v2 + Review Queue

## Adds
- Document classification confidence + review status fields
- New command: titan:classify-docs-v2
- Admin Review Queue UI to approve/mark docs and apply tags quickly

## Command
php artisan titan:classify-docs-v2 --limit=500 --dry-run
php artisan titan:classify-docs-v2 --limit=500

## Admin
/dashboard/admin/settings/titan-zero/library/review-queue
