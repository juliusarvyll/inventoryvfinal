<!-- refreshed: 2026-05-07 -->
# Architecture

**Analysis Date:** 2026-05-07

## System Overview

```text
┌─────────────────────────────────────────────────────────────┐
│                      Client (Browser)                        │
├──────────────────┬──────────────────┬───────────────────────┤
│   Filament Admin Panel  │   Filament Portal Panel  │    (Inertia SPA - Planned)     │
│  `app/Filament/Admin`   │  `app/Filament/Portal`   │   `resources/js/Pages` (missing) │
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

## Component Responsibilities

| Component | Responsibility | File |
|-----------|----------------|------|
| Admin Filament Panel | Primary admin UI for inventory management, user management, reports | `app/Providers/Filament/AdminPanelProvider.php`, `app/Filament/Admin/*` |
| Portal Filament Panel | Restricted user portal for requesting assets, viewing own assets | `app/Providers/Filament/PortalPanelProvider.php`, `app/Filament/Portal/*` |
| Eloquent Models | Data access layer for all inventory entities (assets, licenses, etc.) | `app/Models/*.php` |
| Filament Resources | CRUD interfaces for all inventory models, using Filament v4 components | `app/Filament/Resources/*` |
| Middleware | Request handling, auth, CSRF protection, department context setting | `app/Http/Middleware/*.php`, `bootstrap/app.php` |
| Routes | Web routes (empty, Filament handles its own routing), console commands | `routes/web.php`, `routes/console.php` |

## Pattern Overview

**Overall:** Model-View-Controller (MVC) with Filament's Livewire-based component pattern.

**Key Characteristics:**
- Two isolated Filament panels (Admin, Portal) with separate auth and middleware
- Laravel 12 streamlined configuration via `bootstrap/app.php` (no `app/Http/Kernel.php`)
- Eloquent ORM for all data persistence
- Filament Shield for role-based access control (RBAC)
- Inertia React SPA planned but not yet implemented (no `resources/js` directory)
- Pest/PHPUnit for testing

## Layers

**Presentation Layer:**
- Purpose: User-facing UI for admin and portal users
- Location: `app/Filament/Admin`, `app/Filament/Portal`, `resources/js/Pages` (planned)
- Contains: Filament pages, widgets, Livewire components
- Depends on: Application Layer, Laravel Blade/Filament components
- Used by: Client browsers

**Application Layer:**
- Purpose: Business logic, request handling, data processing
- Location: `app/Http`, `app/Services`, `app/Actions`, `app/Filament/Resources`
- Contains: Controllers, middleware, Filament resources/actions, service classes
- Depends on: Data Layer, Laravel framework
- Used by: Presentation Layer

**Data Layer:**
- Purpose: Persistent storage of inventory and user data
- Location: `app/Models`, `database/migrations`, `database/seeders`
- Contains: Eloquent models, database migrations, seeders, factories
- Depends on: Database (SQLite/MySQL)
- Used by: Application Layer

## Data Flow

### Primary Request Path (Admin Panel)
1. Browser sends GET request to `/` (admin panel root) (`public/index.php:1`)
2. Laravel `bootstrap/app.php` routes request to Filament admin panel
3. Filament `Dashboard` page (Livewire component) renders (`app/Filament/Admin/Pages/Dashboard.php`)
4. Page queries Eloquent models (e.g, `Asset::class`) via Filament resource tables
5. Database returns data to Eloquent model
6. Filament renders Livewire HTML response to browser

### Portal Panel Request Path
1. Browser sends GET request to `/portal` (`public/index.php:1`)
2. Laravel routes to Filament portal panel
3. Portal page (e.g, `MyAssets`) queries user's assigned assets via Eloquent
4. Response rendered as Livewire HTML to browser

### (Planned) Inertia SPA Request Path
1. Browser loads React SPA from `resources/js/Pages`
2. Inertia XHR request to Laravel route
3. Controller uses `Inertia::render()` to return page data
4. React renders page client-side

**State Management:**
- Livewire component state for Filament pages/forms
- Laravel sessions for auth state
- (Planned) React state for Inertia SPA

## Key Abstractions

**Filament Resource:**
- Purpose: Standardized CRUD interface for Eloquent models
- Examples: `app/Filament/Resources/Assets/AssetResource.php`, `app/Filament/Resources/Users/UserResource.php`
- Pattern: Extends `Filament\Resources\Resource`, defines `form()`, `table()`, `pages()` methods

**Filament Panel:**
- Purpose: Isolated admin/portal interface with separate auth, middleware, and routing
- Examples: `app/Providers/Filament/AdminPanelProvider.php`, `app/Providers/Filament/PortalPanelProvider.php`
- Pattern: Extends `Filament\PanelProvider`, configures panel via fluent API

## Entry Points

**Laravel Application Entry:**
- Location: `public/index.php`
- Triggers: All HTTP requests
- Responsibilities: Bootstrap Laravel application, handle request/response cycle

**Admin Panel:**
- Location: `app/Providers/Filament/AdminPanelProvider.php`
- Triggers: Requests to `/` (root path)
- Responsibilities: Configure admin UI, register resources/pages/widgets, auth middleware

**Portal Panel:**
- Location: `app/Providers/Filament/PortalPanelProvider.php`
- Triggers: Requests to `/portal`
- Responsibilities: Configure restricted user portal, register pages, auth middleware

## Architectural Constraints

- **Threading:** PHP single-threaded event loop, no worker threads used
- **Global state:** No module-level singletons detected; Filament panels are isolated
- **Circular imports:** None detected
- **Filament panel isolation:** Admin and Portal panels have separate middleware stacks and auth, no cross-panel dependencies

## Anti-Patterns

### Missing Inertia Frontend
**What happens:** CLAUDE.md specifies Inertia React v3, but `resources/js` directory is missing; no React components exist
**Why it's wrong:** Inertia dependencies are installed but frontend is non-functional
**Do this instead:** Create `resources/js/Pages` directory, set up Inertia entry point in `vite.config.ts`, implement React pages per Inertia v3 patterns

### Empty Web Routes
**What happens:** `routes/web.php` is empty; all web routing is handled by Filament
**Why it's wrong:** Non-Filament web routes cannot be registered without modifying Filament config
**Do this instead:** Add non-Filament routes to `routes/web.php` if needed, or keep as-is if all web UI is Filament-based

## Error Handling

**Strategy:** Layered error handling via Laravel exceptions and Filament built-in error handling

**Patterns:**
- Laravel `bootstrap/app.php` configures exception handling (currently empty)
- Filament handles form validation errors, auth errors, and 404s automatically
- (Planned) Inertia error handling via `httpException` event (v3 rename from `invalid`)

## Cross-Cutting Concerns

**Logging:** Laravel default logging to `storage/logs/laravel.log`
**Validation:** Filament form validation (`->required()`, etc.), Laravel request validation for custom endpoints
**Authentication:** Filament built-in auth with login routes, Fortify v1 installed but not discovered (see `composer.json` extra section)
**Authorization:** Filament Shield for RBAC, per-resource policies in `app/Policies/`

---

*Architecture analysis: 2026-05-07*
