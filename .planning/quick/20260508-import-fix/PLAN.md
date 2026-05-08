---
description: Fix AssetImporter - case-insensitive matching, track created vs reused records, preserve department selection
wave: 1
files_modified:
  - app/Filament/Imports/AssetImporter.php
---

## Task 1: Track created vs reused records

<read_first>
- app/Filament/Imports/AssetImporter.php (full file)
</read_first>

<action>
1. Add protected array `$createdCounts = []` property (tracks new records created)
2. Add protected array `$reusedCounts = []` property (tracks existing records reused)
3. In `resolveRecord()`:
   - When returning existing Asset (by asset_tag or serial): increment `$this->reusedCounts['assets']`
   - When creating new Asset: increment `$this->createdCounts['assets']`
4. In `beforeSave()`:
   - For Category: if creating new → `$this->createdCounts['categories']`; if reusing → `$this->reusedCounts['categories']`
   - For StatusLabel: same pattern with `status_labels` key
   - For Supplier: same pattern with `suppliers` key
   - For Location: same pattern with `locations` key
   - For Department: same pattern with `departments` key
5. Add `afterSave()` method to persist counts to import record:
   ```php
   protected function afterSave(): void
   {
       $import = $this->getImport();
       if ($import) {
           $data = $import->data ?? [];
           $data['created_counts'] = $this->createdCounts;
           $data['reused_counts'] = $this->reusedCounts;
           $import->data = $data;
           $import->save();
       }
   }
   ```
6. Update `getCompletedNotificationBody()` to read counts from `$import->data['created_counts']` and `$import->data['reused_counts']` and show summary like:
   - "Assets: 5 new, 2 existing"
   - "Categories: 3 new, 1 existing"
   - etc.
</action>

<acceptance_criteria>
- grep -q "protected array \$createdCounts = [];" app/Filament/Imports/AssetImporter.php
- grep -q "protected array \$reusedCounts = [];" app/Filament/Imports/AssetImporter.php
- grep -q "afterSave" app/Filament/Imports/AssetImporter.php
- grep -q "created_counts" app/Filament/Imports/AssetImporter.php
- grep -q "reused_counts" app/Filament/Imports/AssetImporter.php
</acceptance_criteria>

---

## Task 2: Case-insensitive matching for all relationship fields

<read_first>
- app/Filament/Imports/AssetImporter.php (getColumns() method)
</read_first>

<action>
1. In `getColumns()` method, update all relationship `resolveUsing` callbacks to use case-insensitive matching:
   - For Category: use `->whereRaw('LOWER(name) = LOWER(?)', [$state])` before falling back to `firstOrCreate()`
   - For StatusLabel: same pattern with `->whereRaw('LOWER(name) = LOWER(?)', [$state])`
   - For Supplier: same pattern
   - For Location: same pattern
2. In `beforeSave()` method, update the Category lookup to use case-insensitive matching:
   - Change `Category::query()->firstOrCreate(...)` to use `->whereRaw('LOWER(name) = LOWER(?)', [$categoryName])->first()` then create if not found
3. Same for StatusLabel in `beforeSave()`
4. Same for Supplier in `beforeSave()`
5. Same for Location in `beforeSave()`
</action>

<acceptance_criteria>
- grep -q "LOWER(name)" app/Filament/Imports/AssetImporter.php (should appear multiple times)
- php artisan test --compact --filter=AssetImporter 2>&1 | grep -v "INFO" | head -20 (should pass or show no tests)
</acceptance_criteria>

---

## Task 3: Preserve department selection for super_admin and non-admin users

<read_first>
- app/Filament/Imports/AssetImporter.php (getOptionsFormComponents(), beforeSave())
- app/Models/User.php (hasRole, primaryDepartment methods)
- app/Models/Concerns/BelongsToDepartment.php
</read_first>

<action>
1. In `getOptionsFormComponents()`:
   - Keep `Select::make('import_department_id')` with `->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)`
   - This ensures only super_admins see the department selector

2. In `beforeSave()`:
   - If user is super_admin AND `import_department_id` option is set:
     ```php
     $department = Department::query()->find($this->options['import_department_id']);
     ```
   - Elseif user has primary department:
     ```php
     $department = $user->primaryDepartment();
     ```
   - Else:
     ```php
     $department = Department::query()->firstOrCreate(['name' => 'Unassigned']);
     ```

3. Remove the `ImportColumn::make('department')` from `getColumns()` - no department column in CSV

4. Ensure `BelongsToDepartment` trait still auto-sets department_id if not already set (backup)
</action>

<acceptance_criteria>
- grep -q "import_department_id" app/Filament/Imports/AssetImporter.php
- grep -q "hasRole('super_admin')" app/Filament/Imports/AssetImporter.php
- grep -v "ImportColumn::make('department')" app/Filament/Imports/AssetImporter.php | grep -q "department" (should not find CSV column)
- vendor/bin/pint --format agent app/Filament/Imports/AssetImporter.php (should pass)
</acceptance_criteria>
