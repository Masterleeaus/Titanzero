# Titan Zero Pass 10 — Bulk Tagging + Auto-Classifier + Sidebar Links

## Adds
1) Bulk Tagging UI (Admin)
- /dashboard/admin/settings/titan-zero/library/bulk

2) Auto-classifier (CLI)
- php artisan titan:classify-docs --limit=500
- php artisan titan:classify-docs --limit=500 --dry-run

3) Worksuite Sidebar injection
- Resources/views/sections/sidebar.blade.php adds Titan Zero + Coaches + key coaches.

## Notes
- Bulk tagging merges tags (keeps existing).
- Classifier uses title + original filename heuristics; refine keywords later.
