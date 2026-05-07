# Architecture Patterns

**Domain:** University Inventory Management System
**Researched:** 2026-05-07
**Overall confidence:** LOW (external verification unavailable; current system analysis HIGH confidence, ecosystem patterns based on unverified training data)

## Recommended Architecture

Updated architecture building on existing Laravel 12 + Filament v4 system, adding Inertia React v3 SPA and audit/usability components:

```text
┌─────────────────────────────────────────────────────────────┐
│                      Client (Browser)                        │
├──────────────────┬──────────────────┬───────────────────────┤
│   Filament Admin Panel  │   Filament Portal Panel  │    Inertia React SPA         │
│  `app/Filament/Admin`   │  `app/Filament/Portal`   │   `resources/js/Pages`       │
└────────┬─────────┴────────┬─────────┴──────────┬────────────┘
         │                  │                     │
         ▼                  ▼                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    Laravel 12 Backend                        │
│         `app/Http`, `app/Models`, `app/Filament`            │
└─────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Database (SQLite default, MySQL supported)                 │
│  `database/migrations`, `app/Models`                        │
└─────────────────────────────────────────────────────────────┘
```

### Component Boundaries

| Component | Responsibility | Communicates With | Confidence |
|-----------|---------------|-------------------|------------|
| Admin Filament Panel | Primary admin UI for inventory management, user management, reports | Laravel Backend, Database | HIGH (verified via existing codebase) |
| Portal Filament Panel | Restricted user portal for requesting assets, viewing own assets | Laravel Backend, Database | HIGH (verified via existing codebase) |
| Inertia React SPA | Mobile-friendly UI for asset lookup, audits, check-in/out | Laravel Backend (Inertia routes), Database | MEDIUM (based on Inertia/Filament docs knowledge) |
| Eloquent Models | Data access layer for all inventory entities (assets, licenses, etc.) | Laravel Backend, Database | HIGH (verified via existing codebase) |
| Filament Resources | CRUD interfaces for all inventory models, using Filament v4 components | Admin/Portal Panels, Eloquent Models | HIGH (verified via existing codebase) |
| Audit Log Service | Track all asset changes, generate audit reports | Eloquent Models (AuditLog), Laravel Backend | MEDIUM (training data pattern) |
| Preventive Maintenance Service | Manage scheduled maintenance, alerts | Eloquent Models (Asset, Maintenance), Laravel Backend | HIGH (existing system component) |
| Barcode/QR Service | Generate and scan asset tags | Inertia SPA, Eloquent Models (Asset) | MEDIUM (training data pattern) |
| Department Hierarchy | Manage Department->Location->Asset relationships | Eloquent Models, All Panels | HIGH (existing system component) |

### Data Flow

#### Existing Filament Panel Flow (Verified HIGH confidence)
1. Browser sends request to `/admin` or `/portal`
2. Laravel routes to corresponding Filament panel via `bootstrap/app.php`
3. Filament Livewire page queries Eloquent models
4. Database returns data to Eloquent
5. Filament renders HTML response to browser

#### New Inertia SPA Flow (MEDIUM confidence)
1. Browser loads React SPA from `/app` (configurable path)
2. Inertia XHR request to Laravel route registered in `routes/web.php`
3. Laravel controller uses `Inertia::render()` to return page data + Wayfinder generated route
4. React renders page client-side, uses `useHttp` hook for subsequent requests
5. State managed via React context/local state, auth shared with Filament via Laravel `web` guard

#### Audit Log Flow (MEDIUM confidence)
1. Asset model modified via Filament resource or Inertia SPA
2. Eloquent model observer triggers, creates `AuditLog` entry with user, timestamp, changes
3. Audit reports generated via Filament widget or Inertia SPA page
4. Audit checklists created, tracked, and reconciled against audit logs

## Patterns to Follow

### Pattern 1: Inertia-Filament Coexistence
**What:** Separate UI systems sharing the same Laravel backend and auth guards, with non-overlapping route prefixes.
**When:** Adding Inertia SPA to existing Filament panel project.
**Example:**
```typescript
// resources/js/Pages/Asset/Index.tsx (Inertia SPA)
import { usePage } from '@inertiajs/react';
import { assetIndex } from '@/actions/asset-controller';

export default function AssetIndex() {
  const { assets } = usePage().props;
  // Render asset list
}
```

```php
// routes/web.php (Laravel 12)
use Inertia\Inertia;

Route::middleware(['auth', 'setDepartmentContext'])->group(function () {
    Route::get('/app/assets', function () {
        return Inertia::render('Asset/Index', [
            'assets' => Asset::with('department', 'location')->paginate(20)
        ]);
    })->name('app.assets.index');
});
```

### Pattern 2: Audit Logging via Model Observers
**What:** Automatically log all model changes using Laravel model observers, store in `AuditLog` model.
**When:** Need comprehensive audit trails for inventory changes.
**Example:**
```php
// app/Observers/AssetObserver.php
namespace App\Observers;

use App\Models\Asset;
use App\Models\AuditLog;

class AssetObserver
{
    public function updated(Asset $asset): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => Asset::class,
            'model_id' => $asset->id,
            'changes' => $asset->getDirty(),
            'original' => $asset->getOriginal(),
        ]);
    }
}
```

### Pattern 3: Department-Scoped Access
**What:** Limit users to assets in their assigned department via model policies and global scopes.
**When:** Enforcing department-level data isolation (current system uses `SetDepartmentContext` middleware).
**Example:**
```php
// app/Policies/AssetPolicy.php
public function view(User $user, Asset $asset): bool
{
    return $user->department_id === $asset->department_id || $user->hasRole('admin');
}
```

## Anti-Patterns to Avoid

### Anti-Pattern 1: Duplicate Auth Systems
**What:** Creating separate authentication for Inertia SPA instead of reusing Laravel's existing auth guards.
**Why bad:** Increases maintenance overhead, causes session conflicts, breaks SSO workflows.
**Instead:** Use Laravel `web` guard for both Filament and Inertia SPA, configure Filament to use same guard.

### Anti-Pattern 2: Overlapping Route Paths
**What:** Registering Inertia routes under `/admin` or `/portal` paths used by Filament panels.
**Why bad:** Route conflicts, unexpected 404s, broken panel functionality.
**Instead:** Prefix Inertia SPA routes with `/app` or separate domain, verify via `php artisan route:list`.

### Anti-Pattern 3: Missing Inertia Frontend (Current System)
**What:** Inertia dependencies installed but no `resources/js` directory or React components exist.
**Why bad:** Inertia setup is non-functional, breaks planned SPA workflows.
**Instead:** Create `resources/js/Pages` directory, configure Vite entry point, implement base React pages.

## Scalability Considerations

| Concern | At 100 users | At 10K users | At 1M users | Confidence |
|---------|--------------|--------------|-------------|------------|
| Asset Query Performance | Eloquent eager loading | Add Redis caching for frequent queries | Database sharding by department | MEDIUM |
| Audit Log Storage | Single `audit_logs` table | Partition table by month | Archive logs > 2 years old | MEDIUM |
| Concurrent Users | SQLite (dev) / MySQL (prod) | MySQL read replicas | Load-balanced Laravel instances | MEDIUM |
| File Storage (Attachments) | Local storage | Laravel Cloud or S3 | CDN for public files | MEDIUM |

## Suggested Build Order (Dependencies Between Components)

Confidence: MEDIUM (based on typical dependency chains for this stack)

1. **Complete Inertia SPA Base Setup**
   - Dependencies: None (uses existing Laravel 12, Vite, React config)
   - Tasks: Create `resources/js/Pages`, configure Vite entry, run `wayfinder:generate`, implement base layout
   - Rationale: Foundation for all subsequent SPA features

2. **Expand Audit Log Coverage**
   - Dependencies: Existing `AuditLog` model, all Eloquent models
   - Tasks: Add model observers for all inventory models, verify logging for create/update/delete/transfer
   - Rationale: Required for all audit-related features

3. **Add Audit Checklist & Discrepancy Reporting**
   - Dependencies: Audit Log Service, Inertia SPA base
   - Tasks: Build audit checklist UI in Inertia SPA, add discrepancy tracking to `AuditLog`
   - Rationale: Core auditing requirement for university compliance

4. **Integrate Barcode/QR Scanning**
   - Dependencies: Inertia SPA base, `Asset` model
   - Tasks: Add QR code generation to asset creation, implement mobile scanning in Inertia SPA
   - Rationale: Major usability improvement for physical audits

5. **Enhance Preventive Maintenance UI**
   - Dependencies: Existing Maintenance models, Inertia SPA base
   - Tasks: Add scheduling calendar, alert notifications, maintenance history to Inertia SPA
   - Rationale: Completes existing maintenance system with user-facing UI

## Confidence Assessment

| Area | Level | Reason |
|------|-------|--------|
| Current System Components | HIGH | Verified via reading existing ARCHITECTURE.md and STRUCTURE.md |
| Inertia-Filament Integration | MEDIUM | Based on Inertia v3/Filament v4 docs knowledge, no external verification |
| Audit System Components | MEDIUM | Training data pattern, aligns with existing AuditLog model |
| University Inventory Standards | LOW | No external verification, based on unverified training data |
| Scalability Patterns | MEDIUM | Standard Laravel scaling patterns, no specific university system verification |

## Sources

- Existing system analysis: `/home/julius/inventoryvfinal/.planning/codebase/ARCHITECTURE.md`, `/home/julius/inventoryvfinal/.planning/codebase/STRUCTURE.md` (HIGH confidence)
- Inertia.js v3 Documentation (unverified, training data)
- Filament v4 Documentation (unverified, training data)
- Laravel 12 Documentation (unverified, training data)
- Note: WebSearch and WebFetch unavailable due to model constraints, no external verification performed
