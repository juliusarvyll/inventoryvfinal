---
status: complete
completed: 2026-05-08
slug: import-fix
description: Fix AssetImporter - case-insensitive matching, track created vs reused records, preserve department selection
---

## Summary

Fixed `app/Filament/Imports/AssetImporter.php` with 3 improvements:

### 1. Track created vs reused records
- Added `$createdCounts` and `$reusedCounts` arrays
- Each relationship lookup (Category, StatusLabel, Supplier, Location, Department) now tracks new vs existing
- `afterSave()` persists counts to `$import->data` JSON column
- `getCompletedNotificationBody()` shows summary: "Assets: 5 new, 2 existing"

### 2. Case-insensitive matching for all relationship fields
- Updated `getColumns()` and `beforeSave()` to use `whereRaw('LOWER(name) = LOWER(?)')` for Category, StatusLabel, Supplier, Location
- Prevents "deleted" behavior where CSV values like "computer" vs "Computer" created duplicates

### 3. Preserve department selection
- `getOptionsFormComponents()` has `import_department_id` Select field (only visible to super_admin)
- `beforeSave()` applies:
  - Super admin WITH department selected → uses selected department
  - Non-admin WITH primary department → uses user's primary department
  - Otherwise → defaults to "Unassigned"
- Removed `ImportColumn::make('department')` from CSV columns (not needed in CSV)

## Acceptance Criteria Met
- Pint passes
- Case-insensitive matching via `LOWER()` in all relationship lookups
- Department selection preserved via import options (not CSV column)
- Created vs reused counts tracked and shown in completion notification
