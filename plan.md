# Codex Prompt: ICT Inventory Management System (Laravel + Filament v3)

## Project Overview

Build a full-featured **ICT Inventory Management System** using **Laravel 11** and **Filament v3**. The system manages organizational IT assets, software licenses, accessories, consumables, hardware components, and a self-service request portal for end users.

The architecture mirrors tools like Snipe-IT but is built natively in Filament with a clean admin panel, role-based access, audit logging, and a request/approval workflow.

---

## Tech Stack

- **Framework**: Laravel 11
- **Admin Panel**: Filament v3 (`filament/filament`)
- **Database**: MySQL 8+
- **Auth**: Laravel Breeze or Jetstream (Filament-compatible)
- **Roles & Permissions**: `spatie/laravel-permission`
- **Audit Logging**: `spatie/laravel-activitylog`
- **Excel Import/Export**: `maatwebsite/laravel-excel`
- **PDF Reports**: `barryvdh/laravel-dompdf`
- **Testing**: PestPHP

---

## Roles

Define three roles using `spatie/laravel-permission`:

1. **Admin** — Full access to all resources, settings, approvals
2. **IT Staff** — Manage assets, perform checkouts/check-ins, process requests
3. **End User** — View their own assigned items, browse and submit requests

---

## Database Schema & Models

### 1. `users`

```php
// Standard Laravel users table plus:
$table->string('employee_id')->nullable()->unique();
$table->string('department')->nullable();
$table->string('job_title')->nullable();
$table->string('phone')->nullable();
$table->string('location')->nullable();
$table->string('avatar')->nullable();
$table->boolean('is_active')->default(true);
```

**Relationships:**
- `hasMany` → `AssetCheckout`
- `hasMany` → `LicenseSeat`
- `hasMany` → `AccessoryCheckout`
- `hasMany` → `ItemRequest`
- `belongsToMany` → roles (via Spatie)

---

### 2. `assets`

```php
$table->string('asset_tag')->unique();
$table->string('name');
$table->foreignId('asset_model_id')->constrained();   // brand/model lookup
$table->foreignId('category_id')->constrained();
$table->foreignId('status_label_id')->constrained();  // Available, Deployed, In Repair, Retired, Lost/Stolen
$table->foreignId('supplier_id')->nullable()->constrained();
$table->foreignId('location_id')->nullable()->constrained();
$table->string('serial')->nullable()->unique();
$table->decimal('purchase_cost', 10, 2)->nullable();
$table->date('purchase_date')->nullable();
$table->date('warranty_expires')->nullable();
$table->date('eol_date')->nullable();    // end of life
$table->text('notes')->nullable();
$table->boolean('requestable')->default(false);
```

**Relationships:**
- `belongsTo` → `AssetModel`, `Category`, `StatusLabel`, `Supplier`, `Location`
- `hasMany` → `AssetCheckout`, `Component` (via pivot), `Maintenance`
- `morphMany` → `ActivityLog`

**Key logic:**
- When an asset is checked out, its `status_label` changes to "Deployed"
- When checked in, status reverts to "Available" (or a configurable default)
- Warranty expiry triggers a notification 30 days before expiration

---

### 3. `licenses`

```php
$table->string('name');
$table->string('product_key')->nullable();
$table->foreignId('category_id')->constrained();
$table->foreignId('manufacturer_id')->nullable()->constrained();
$table->string('license_type'); // per_seat, per_device, open_license, site_license
$table->integer('seats');       // total seats available
$table->date('expiration_date')->nullable();
$table->date('purchase_date')->nullable();
$table->decimal('purchase_cost', 10, 2)->nullable();
$table->string('order_number')->nullable();
$table->boolean('maintained')->default(false);
$table->boolean('requestable')->default(false);
$table->text('notes')->nullable();
```

**Relationships:**
- `hasMany` → `LicenseSeat`
- `belongsTo` → `Category`, `Manufacturer`

**Key logic:**
- A computed attribute `seats_available = seats - assigned_seats_count`
- Cannot assign more seats than `seats` total
- License expiry within 30 days triggers a notification

---

### 4. `license_seats` (pivot)

```php
$table->foreignId('license_id')->constrained();
$table->foreignId('assigned_to')->nullable()->constrained('users');
$table->foreignId('asset_id')->nullable()->constrained();
$table->timestamp('assigned_at')->nullable();
```

---

### 5. `accessories`

```php
$table->string('name');
$table->foreignId('category_id')->constrained();
$table->foreignId('supplier_id')->nullable()->constrained();
$table->foreignId('location_id')->nullable()->constrained();
$table->integer('qty');             // total in stock
$table->integer('min_qty')->default(0); // triggers low-stock alert
$table->string('model_number')->nullable();
$table->decimal('purchase_cost', 10, 2)->nullable();
$table->date('purchase_date')->nullable();
$table->string('order_number')->nullable();
$table->boolean('requestable')->default(false);
$table->text('notes')->nullable();
```

**Relationships:**
- `hasMany` → `AccessoryCheckout`
- `belongsTo` → `Category`, `Supplier`, `Location`

**Key logic:**
- `qty_remaining = qty - accessory_checkouts_count` (computed attribute)
- Cannot check out more than `qty_remaining`
- Alert when `qty_remaining <= min_qty`

---

### 6. `accessory_checkouts` (pivot)

```php
$table->foreignId('accessory_id')->constrained();
$table->foreignId('assigned_to')->constrained('users');
$table->integer('qty')->default(1);
$table->timestamp('assigned_at')->nullable();
$table->timestamp('returned_at')->nullable();
$table->text('note')->nullable();
```

---

### 7. `consumables`

```php
$table->string('name');
$table->foreignId('category_id')->constrained();
$table->foreignId('supplier_id')->nullable()->constrained();
$table->foreignId('location_id')->nullable()->constrained();
$table->integer('qty');
$table->integer('min_qty')->default(0);
$table->string('model_number')->nullable();
$table->string('item_no')->nullable();   // vendor SKU
$table->decimal('purchase_cost', 10, 2)->nullable();
$table->date('purchase_date')->nullable();
$table->string('order_number')->nullable();
$table->boolean('requestable')->default(false);
$table->text('notes')->nullable();
```

**Relationships:**
- `hasMany` → `ConsumableAssignment` (one-way, no check-in)
- `belongsTo` → `Category`, `Supplier`, `Location`

**Key logic:**
- Consumables are issued and stock decrements permanently — no return/check-in
- Alert when `qty_remaining <= min_qty`

---

### 8. `components`

```php
$table->string('name');
$table->foreignId('category_id')->constrained();
$table->foreignId('supplier_id')->nullable()->constrained();
$table->foreignId('location_id')->nullable()->constrained();
$table->integer('qty');
$table->integer('min_qty')->default(0);
$table->string('serial')->nullable();
$table->decimal('purchase_cost', 10, 2)->nullable();
$table->date('purchase_date')->nullable();
$table->string('order_number')->nullable();
$table->text('notes')->nullable();
```

**Relationships:**
- `belongsToMany` → `Asset` (via `asset_components` pivot with `qty` column)
- `belongsTo` → `Category`, `Supplier`, `Location`

**Key logic:**
- Components can be "installed" into an asset (e.g., 2× RAM sticks in a laptop)
- `qty_remaining` = `qty - sum(asset_components.qty)`

---

### 9. `item_requests`

```php
$table->foreignId('user_id')->constrained();         // requestor
$table->morphs('requestable');                       // polymorphic: Asset, License, Accessory, Component, Consumable
$table->string('status')->default('pending');        // pending, approved, denied, fulfilled, cancelled
$table->integer('qty')->default(1);
$table->text('reason')->nullable();
$table->text('deny_reason')->nullable();
$table->foreignId('handled_by')->nullable()->constrained('users');  // admin/IT who acted
$table->timestamp('handled_at')->nullable();
$table->timestamp('fulfilled_at')->nullable();
```

**Relationships:**
- `belongsTo` → `User` (requestor), `User` (handler)
- `morphTo` → `requestable` (any model with `requestable = true`)

**Key logic:**
- When `approved`, automatically trigger the checkout action for the item type
- Notification sent to requestor on status change
- Notification sent to IT Staff on new pending request

---

### Supporting / Lookup Models

These are simple lookup tables:

| Model | Fields |
|---|---|
| `AssetModel` | `name`, `manufacturer_id`, `category_id`, `model_number`, `image` |
| `Manufacturer` | `name`, `url`, `support_url`, `support_phone`, `image` |
| `Category` | `name`, `type` (asset/license/accessory/consumable/component) |
| `StatusLabel` | `name`, `color`, `type` (deployable, pending, archived, undeployable) |
| `Supplier` | `name`, `address`, `city`, `state`, `country`, `phone`, `email`, `url` |
| `Location` | `name`, `address`, `city`, `state`, `country`, `parent_id` (self-referential) |

---

## Filament Panel Structure

### Panel Configuration (`AdminPanelProvider`)

```php
Panel::make()
    ->id('admin')
    ->path('admin')
    ->login()
    ->colors(['primary' => Color::Blue])
    ->navigationGroups([
        'Inventory',
        'Assignments',
        'Requests',
        'Lookups',
        'Reports',
        'Settings',
    ])
    ->plugins([
        SpatieLaravelPermissionPlugin::make(),
    ]);
```

---

## Filament Resources

Build one `Resource` per primary model. Each resource must have:
- `ListRecords` page with global search, column filters, and bulk actions
- `CreateRecord` and `EditRecord` pages with full form validation
- `ViewRecord` page showing relationships and history

### Resource: `AssetResource`

**Table columns:**
- Asset Tag (searchable, copyable)
- Image (thumbnail)
- Name (searchable, link to view)
- Model → Manufacturer
- Serial (searchable)
- Category
- Status Label (badge with color)
- Location
- Assigned To (user avatar + name)
- Purchase Date
- Warranty Expires (highlighted in red if < 30 days)

**Form fields:**
- Asset Tag (auto-generated or manual)
- Name, Serial
- Asset Model (searchable select with create-inline)
- Category (filtered by type = asset)
- Status Label
- Supplier, Location
- Purchase Cost, Purchase Date, Order Number
- Warranty Expires, EOL Date
- Requestable (toggle)
- Notes (rich editor)
- Image upload

**Custom Actions:**
- `CheckoutAction` — opens a modal: select User or Asset (for license seats), set notes, confirm. Changes status to Deployed.
- `CheckinAction` — opens a modal: set new status, location, notes. Logs history.
- `CloneAction` — duplicates asset with a new unique tag
- `GenerateQrCodeAction` — generates a QR code linking to the asset view page
- `PrintLabelAction` — renders a dompdf label with asset tag + QR code

**Relation Managers:**
- `ComponentsRelationManager` — shows installed components
- `MaintenanceRelationManager` — log of repairs and maintenance
- `ActivityLogRelationManager` — full audit timeline

---

### Resource: `LicenseResource`

**Table columns:**
- Name, Product Key (masked, reveal on hover)
- Manufacturer
- License Type (badge)
- Seats Available (e.g., "12 / 25") with color: green if available, red if full
- Expiration Date (highlighted if < 30 days)
- Purchase Date

**Form fields:**
- Name, Product Key
- Manufacturer, Category
- License Type (select: per_seat, per_device, etc.)
- Seats (integer)
- Expiration Date, Purchase Date, Purchase Cost, Order Number
- Maintained (toggle), Requestable (toggle)
- Notes

**Custom Actions:**
- `AssignSeatAction` — select user or asset to assign a seat

**Relation Managers:**
- `SeatsRelationManager` — table of all seat assignments (user, asset, assigned_at, unassign action)

---

### Resource: `AccessoryResource`

**Table columns:**
- Name, Category, Supplier
- Qty Total | Qty Remaining (color-coded)
- Min Qty (badge: "Low Stock" if remaining ≤ min)
- Location

**Form fields:**
- Name, Model Number, Category, Supplier, Location
- Qty, Min Qty
- Purchase Cost, Purchase Date, Order Number
- Requestable (toggle), Notes

**Custom Actions:**
- `CheckoutAction` — select user, quantity, note
- `CheckinAction` — select checkout record to return, restore qty

**Relation Managers:**
- `CheckoutsRelationManager` — list of current checkouts with check-in action

---

### Resource: `ConsumableResource`

**Table columns:**
- Name, Category, Item No, Supplier, Location
- Qty Remaining (badge: "Low Stock" alert)

**Form fields:**
- Name, Item No, Model Number
- Category, Supplier, Location
- Qty, Min Qty
- Purchase Cost, Purchase Date, Order Number
- Requestable (toggle), Notes

**Custom Actions:**
- `IssueAction` — select user, qty, note. Permanently decrements stock. No return.

**Relation Managers:**
- `AssignmentsRelationManager` — readonly history of all issuances

---

### Resource: `ComponentResource`

**Table columns:**
- Name, Category, Supplier, Location
- Qty Total | Qty Remaining
- Serial

**Form fields:**
- Name, Serial, Category, Supplier, Location
- Qty, Min Qty
- Purchase Cost, Purchase Date, Order Number
- Notes

**Custom Actions:**
- `InstallIntoAssetAction` — select asset, qty to install

**Relation Managers:**
- `AssetsRelationManager` — list of assets this component is installed in, with qty and uninstall action

---

### Resource: `UserResource`

**Table columns:**
- Avatar, Name, Employee ID, Email
- Department, Job Title, Location
- # Assets, # Licenses, # Accessories assigned (counts)
- Active (badge)

**Form fields:**
- First Name, Last Name, Email, Password
- Employee ID, Department, Job Title, Phone, Location
- Avatar upload
- Is Active (toggle)
- Role (select via Spatie)

**Relation Managers:**
- `AssignedAssetsRelationManager` — assets checked out to this user
- `AssignedLicensesRelationManager` — license seats assigned
- `AssignedAccessoriesRelationManager` — accessories checked out
- `RequestsRelationManager` — request history

---

### Resource: `ItemRequestResource`

**Table columns:**
- Requestor (avatar + name)
- Item (polymorphic name + type badge)
- Qty
- Status (badge: pending=yellow, approved=green, denied=red, fulfilled=blue)
- Reason (truncated)
- Requested At

**Form fields (admin view):**
- Status (select)
- Deny Reason (shown only when status = denied)
- Handled By (auto-filled from current auth user)

**Custom Actions (on ListRecords):**
- `ApproveAction` — bulk approve selected requests
- `DenyAction` — bulk deny with reason modal

---

## Dashboard (`DashboardPage`)

Build the following Filament widgets:

### Stats Overview Widget
- Total Assets (with breakdown: available, deployed, in repair)
- Total Licenses (seats used vs total)
- Total Accessories (in stock)
- Open Requests (pending count)

### `AssetsStatusChart` (Donut chart)
- Segments: Available, Deployed, In Repair, Retired, Lost/Stolen

### `RecentActivityWidget`
- Last 10 activity log entries (user, action, item, timestamp)

### `ExpiringLicensesWidget`
- Table of licenses expiring within 30 days

### `LowStockWidget`
- Combined table: accessories + consumables + components below min_qty

### `RecentRequestsWidget`
- Last 5 pending requests with approve/deny quick actions

---

## Request Portal (End User Panel)

Create a **second Filament panel** (`/portal`) for End Users:

```php
Panel::make()
    ->id('portal')
    ->path('portal')
    ->login()
    ->colors(['primary' => Color::Indigo]);
```

### Portal Pages:

**`MyAssetsPage`** — Read-only list of the authenticated user's assigned assets, licenses, accessories.

**`BrowseRequestablesPage`** — Grid of all requestable items (assets, licenses, accessories, consumables, components) with:
- Thumbnail, name, category, availability status
- "Request" button → opens modal with qty (if applicable) and reason field
- Submits to `item_requests`

**`MyRequestsPage`** — History of the user's requests with status badges and cancel action (if still pending).

---

## Notifications

Use Laravel's notification system with database + mail drivers:

| Trigger | Recipient | Notification |
|---|---|---|
| New item request submitted | IT Staff (role) | `NewRequestNotification` |
| Request approved | Requestor | `RequestApprovedNotification` |
| Request denied | Requestor | `RequestDeniedNotification` |
| Asset checked out | Assignee | `AssetCheckedOutNotification` |
| License expiring in 30 days | Admin | `LicenseExpiringNotification` |
| Low stock (accessories/consumables/components) | IT Staff | `LowStockNotification` |
| Warranty expiring in 30 days | Admin | `WarrantyExpiringNotification` |

Schedule `LicenseExpiringNotification` and `WarrantyExpiringNotification` as daily console commands via `app/Console/Kernel.php`.

---

## Audit Logging

Use `spatie/laravel-activitylog` on all primary models:

```php
// In each model:
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
```

All checkout/checkin/assign/unassign actions must manually log with:
```php
activity()
    ->performedOn($asset)
    ->causedBy(auth()->user())
    ->withProperties(['action' => 'checkout', 'assigned_to' => $user->id])
    ->log('Asset checked out');
```

---

## Import / Export

### Import
Use `maatwebsite/laravel-excel` to build importers for:
- `AssetImport` — bulk import assets from CSV (map columns: asset_tag, name, serial, model, category, status, location, purchase_date, purchase_cost)
- `LicenseImport`
- `UserImport`

Each importer must:
- Validate each row and collect errors
- Skip duplicate serials/tags
- Report a summary: X imported, Y skipped, Z failed

Add an `ImportAction` button to the list page header of each resource.

### Export
Add an `ExportAction` to asset, license, and user list pages that exports the current filtered view to Excel using `maatwebsite/laravel-excel`.

---

## PDF Report: Asset Label

Using `barryvdh/laravel-dompdf`, create a Blade view at `resources/views/reports/asset-label.blade.php`:

```
┌─────────────────────────┐
│  [COMPANY LOGO]         │
│  Asset Tag: ICT-00123   │
│  Name: Dell Latitude     │
│  Serial: ABC123XYZ       │
│  [QR CODE]              │
└─────────────────────────┘
```

Generate QR codes using `SimpleSoftwareIO/simple-qrcode`.

---

## Policies

Create Laravel Policies for each model, bound to roles:

```php
// Example AssetPolicy
public function viewAny(User $user): bool
{
    return $user->hasAnyRole(['Admin', 'IT Staff']);
}
public function create(User $user): bool
{
    return $user->hasAnyRole(['Admin', 'IT Staff']);
}
public function update(User $user, Asset $asset): bool
{
    return $user->hasAnyRole(['Admin', 'IT Staff']);
}
public function delete(User $user, Asset $asset): bool
{
    return $user->hasRole('Admin');
}
```

Register policies in `AuthServiceProvider`.

---

## Seeders & Factories

Create the following:

- `DatabaseSeeder` — calls all seeders in order
- `RolePermissionSeeder` — creates Admin, IT Staff, End User roles
- `UserSeeder` — seeds 1 admin, 2 IT staff, 10 end users
- `CategorySeeder` — default categories for each type
- `StatusLabelSeeder` — Available, Deployed, In Repair, Retired, Lost/Stolen
- `LocationSeeder` — example locations (HQ, Branch A, etc.)
- `AssetFactory` + `AssetSeeder` — 50 demo assets
- `LicenseFactory` + `LicenseSeeder` — 10 demo licenses with seats
- `AccessoryFactory`, `ConsumableFactory`, `ComponentFactory`

---

## File Structure (Key Files)

```
app/
  Filament/
    Panels/
      AdminPanelProvider.php
      PortalPanelProvider.php
    Resources/
      AssetResource/
        Pages/
          ListAssets.php
          CreateAsset.php
          EditAsset.php
          ViewAsset.php
        RelationManagers/
          ComponentsRelationManager.php
          MaintenanceRelationManager.php
          ActivityLogRelationManager.php
        AssetResource.php
      LicenseResource/
      AccessoryResource/
      ConsumableResource/
      ComponentResource/
      UserResource/
      ItemRequestResource/
    Widgets/
      StatsOverviewWidget.php
      AssetsStatusChartWidget.php
      RecentActivityWidget.php
      ExpiringLicensesWidget.php
      LowStockWidget.php
      RecentRequestsWidget.php
    Pages/
      Portal/
        MyAssetsPage.php
        BrowseRequestablesPage.php
        MyRequestsPage.php
  Models/
    Asset.php
    License.php
    LicenseSeat.php
    Accessory.php
    AccessoryCheckout.php
    Consumable.php
    ConsumableAssignment.php
    Component.php
    ItemRequest.php
    User.php
    AssetModel.php
    Manufacturer.php
    Category.php
    StatusLabel.php
    Supplier.php
    Location.php
  Notifications/
    NewRequestNotification.php
    RequestApprovedNotification.php
    RequestDeniedNotification.php
    AssetCheckedOutNotification.php
    LicenseExpiringNotification.php
    LowStockNotification.php
    WarrantyExpiringNotification.php
  Policies/
    AssetPolicy.php
    LicensePolicy.php
    AccessoryPolicy.php
    ConsumablePolicy.php
    ComponentPolicy.php
    ItemRequestPolicy.php
  Imports/
    AssetImport.php
    LicenseImport.php
    UserImport.php
database/
  migrations/
    (one migration per table)
  seeders/
  factories/
resources/
  views/
    reports/
      asset-label.blade.php
```

---

## Acceptance Criteria

- [ ] All 7 primary models have full CRUD via Filament Resources
- [ ] Checkout/check-in workflow works for assets, licenses, and accessories
- [ ] Consumables and components have one-way issue/install workflows
- [ ] `item_requests` polymorphic request system works end-to-end with approval/denial
- [ ] Portal panel lets end users browse requestable items and submit requests
- [ ] All primary models log activity via Spatie ActivityLog
- [ ] Notifications fire on request state changes and threshold events
- [ ] Dashboard shows real-time stats, expiry alerts, low stock, recent activity
- [ ] CSV import works for assets with validation and error reporting
- [ ] Excel export works for filtered list views
- [ ] PDF asset label prints with QR code
- [ ] Role-based access enforced via Filament policies (Admin, IT Staff, End User)
- [ ] PestPHP tests cover: checkout logic, request workflow, seat limit enforcement, low-stock detection