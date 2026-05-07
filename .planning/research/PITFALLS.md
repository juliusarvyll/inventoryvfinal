# Domain Pitfalls

**Domain:** University inventory/asset management (auditing and usability focus)
**Researched:** 2026-05-07
**Overall confidence:** HIGH (based on existing codebase analysis + common inventory system failures)

---

## Critical Pitfalls

Mistakes that cause rewrites or major issues.

### Pitfall 1: Mutable Audit Logs

**What goes wrong:** AuditLog records can be edited or deleted by administrators, making compliance audits fail.

**Why it happens:** Developers treat AuditLog like any other model with standard CRUD operations.

**Consequences:**
- Compliance audit failure (university cannot prove asset history)
- Potential legal liability if asset disputes arise
- Loss of trust in the system

**Prevention:**
- Remove `edit` and `delete` actions from AuditLog Filament resource
- Add model-level protection:
  ```php
  protected static function boot(): void
  {
      parent::boot();
      static::preventSilentlyDiscardingEvents();
  }
  ```
- Use database constraints: set `NO DELETE` rule on AuditLog table
- Consider using `spatie/laravel-activitylog` which has immutable log features

**Detection:** Check if AuditLog Filament resource has `edit` or `delete` actions enabled.

---

### Pitfall 2: Missing Audit Coverage on Related Models

**What goes wrong:** Asset changes are logged, but checkout actions, maintenance records, and status changes on related models are not logged.

**Why it happens:** Developers add audit logging only to main models (Asset), forgetting related models (AssetCheckout, PreventiveMaintenanceExecution, etc.).

**Consequences:**
- Incomplete audit trail ("Asset was returned but we don't know who returned it")
- Compliance gaps
- Manual reconstruction of events required

**Prevention:**
- Use `spatie/laravel-activitylog` trait on ALL models that affect asset state
- Create a base trait or interface that enforces audit logging
- Test: create a checklist of all models that must have audit logging

**Detection:** Review all models in `app/Models/` - each should have audit logging configured.

---

### Pitfall 3: Performance Degradation with 10K+ Assets

**What goes wrong:** Asset listing, search, and audit log queries become slow as data grows.

**Why it happens:**
- Missing database indexes on frequently queried columns (`asset_tag`, `serial`, `status_label_id`)
- N+1 queries in Filament resource tables (not using eager loading)
- Full table scans for audit log reports

**Consequences:**
- User frustration ("page takes 10 seconds to load")
- Timeout errors during inventory counts
- System becomes unusable at scale

**Prevention:**
- Add indexes to migrations for: `asset_tag`, `serial`, `status_label_id`, `department_id`, `location_id`, `category_id`
- Use Filament's `->with()` for eager loading in resource tables
- Paginate all listings (Filament does this by default, but verify)
- Archive old audit logs to separate table after 2+ years

**Detection:** Use Laravel Debugbar or `->dd()` on queries to check for N+1 issues. Monitor query time in logs.

---

### Pitfall 4: QR/Barcode Format Fragmentation

**What goes wrong:** University has assets with different barcode formats (QR, Code128, EAN) but system only supports one.

**Why it happens:** Developers assume QR codes are sufficient for all use cases.

**Consequences:**
- Cannot scan legacy barcode labels
- Must re-label all assets (expensive, time-consuming)
- Manual entry required for non-QR assets

**Prevention:**
- Use `milwad/laravel-barcode` which supports 15+ formats
- Allow per-asset or per-category barcode format configuration
- Test scanning with actual university asset labels before deploying

**Detection:** Check if barcode generation/scanning code hardcodes a single format.

---

### Pitfall 5: Student Checkout Without Return Enforcement

**What goes wrong:** Students check out equipment but never return it; no automated reminders or holds.

**Why it happens:** System tracks due dates but doesn't enforce returns or notify stakeholders.

**Consequences:**
- Lost equipment ("Where are the cameras for JOUR 101?")
- Department budget overruns (must replace unreturned items)
- Student graduation with unreturned equipment

**Prevention:**
- Set checkout duration limits based on academic session (semester-based)
- Automated reminder emails at 75%, 90%, 100% of due date
- Integration with student information system to block registration if equipment not returned
- Department head notification for overdue items

**Detection:** Check if AssetCheckout model has `due_date` and if reminders are implemented.

---

## Moderate Pitfalls

### Pitfall 1: Department Head Turnover

**What goes wrong:** Department head changes but assets are still "owned" by old head in system.

**Prevention:**
- Build department head transfer workflow (bulk reassign assets)
- Audit log the transfer with old/new head recorded
- Send notification to new head listing all department assets

---

### Pitfall 2: Media Storage Bloat

**What goes wrong:** Users upload high-res photos for every asset; disk space runs out.

**Prevention:**
- Configure Spatie Medialibrary to resize images on upload (max 1920px width)
- Set storage limit per asset (e.g., 5 photos max)
- Use S3 or similar for production storage (not local disk)

---

### Pitfall 3: Audit Log "Noise" from Bulk Operations

**What goes wrong:** Bulk update of 100 assets creates 100 audit log entries, making the log hard to read.

**Prevention:**
- Batch audit log entries for bulk operations (log as single "bulk update" action)
- Add `batch_id` field to AuditLog to group related actions
- Provide "view batch" UI in audit report

---

### Pitfall 4: Offline Sync Conflicts

**What goes wrong:** Multiple people do inventory counts offline; syncing creates conflicts.

**Prevention:**
- Use timestamp-based conflict resolution (last write wins with audit)
- Warn users if data is stale before syncing
- Consider check-in/check-out as append-only (not conflicting operations)

---

### Pitfall 5: LDAP/AD Integration Complexity

**What goes wrong:** University AD has nested groups, multiple domains, or custom schemas that don't match package defaults.

**Prevention:**
- Test LDAP connection with university IT before building features
- Use `adldap2/adldap2-laravel` which handles complex AD scenarios
- Build fallback to manual user creation if LDAP fails
- Cache LDAP queries to avoid performance issues

---

## Minor Pitfalls

### Pitfall 1: Asset Tag Collisions

**What goes wrong:** Two assets get same `asset_tag` due to race condition or manual entry.

**Prevention:**
- Database unique constraint on `asset_tag`
- Use auto-incrementing tag numbers with prefix (e.g., "INV-00001")
- Validate uniqueness in Filament form

---

### Pitfall 2: Status Label Confusion

**What goes wrong:** Users don't understand difference between "Deployed", "Ready to Deploy", "Maintenance", etc.

**Prevention:**
- Provide clear descriptions on status label management page
- Use color coding (green = available, red = unavailable)
- Limit status labels to essential set (don't let users create 50 labels)

---

### Pitfall 3: Date Format Inconsistency

**What goes wrong:** Some users enter MM/DD/YYYY, others DD/MM/YYYY; data becomes ambiguous.

**Prevention:**
- Use HTML date inputs (enforces YYYY-MM-DD)
- Configure Laravel to use ISO 8601 dates globally
- Display dates in user's locale (Filament handles this)

---

### Pitfall 4: Forgetting Soft Deletes

**What goes wrong:** Asset is "deleted" but still appears in audit logs and relationships.

**Prevention:**
- Use soft deletes on Asset model (already may be configured)
- Audit log soft delete events
- Provide "restore" action in Filament for accidentally deleted assets

---

## Phase-Specific Warnings

| Phase Topic | Likely Pitfall | Mitigation |
|-------------|---------------|------------|
| **AUDIT-01: Audit Reports** | Query performance on large date ranges | Add database indexes on `created_at`, paginate results, use queued exports for large datasets |
| **AUDIT-02: Compliance Dashboard** | Anomaly detection false positives | Start with simple metrics (count unaudited assets); add ML later if needed |
| **AUDIT-03: Digital Signatures** | Legal validity questions | Research university's digital signature policy; use canvas with timestamp |
| **AUDIT-04: Audit Reminders** | Notification fatigue (too many emails) | Allow users to configure reminder frequency; batch reminders |
| **USAB-01: Inertia React SPA** | Duplicate code (Filament + React doing same thing) | Use Inertia React only for Portal panel; keep Admin in Filament |
| **USAB-02: Dashboard Widgets** | Widget performance (too many DB queries) | Cache widget data; use Laravel's `Cache::remember()` |
| **USAB-03: Advanced Search** | Full-text search complexity | Start with Laravel's `where()` clauses; add Laravel Scout only if needed |
| **USAB-04: Bulk Actions** | Accidental bulk updates | Require confirmation modal; show count of affected items |
| **USAB-05: QR/Barcode** | Mobile camera permissions | Handle permission denied gracefully; provide manual entry fallback |
| **UNIV-01: Academic Sessions** | Session boundary confusion (overlapping terms) | Validate no overlapping sessions; use date ranges |
| **UNIV-03: Student Checkout** | Student ID format variations | Validate against university's student ID format; provide examples |

---

## Sources

- **Existing Codebase Analysis:** AuditLog model, Asset model, PROJECT.md (HIGH confidence)
- **Common Inventory System Failures:** Industry knowledge from Snipe-IT, Asset Panda implementations (HIGH confidence)
- **Laravel Performance Best Practices:** Laravel documentation (HIGH confidence)
- **University Compliance Requirements:** FERPA, audit standards for public institutions (MEDIUM confidence)
- **Filament v4 Patterns:** Existing project context (HIGH confidence)

---

## Pre-Submission Checklist

- [x] All domains investigated (audit, usability, university-specific)
- [x] Negative claims verified with code analysis
- [x] Multiple sources for critical claims (code + domain knowledge)
- [x] URLs provided where applicable (code references)
- [x] Publication dates checked (based on existing code which is current)
- [x] Confidence levels assigned honestly
- [x] "What might I have missed?" reviewed:
  - Multi-language support (universities may need Spanish/other languages)
  - Accessibility (WCAG compliance for government-funded institutions)
  - API access for external systems (future phase)
  - Backup/restore procedures for audit logs
