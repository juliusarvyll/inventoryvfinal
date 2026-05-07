# Codebase Structure

**Analysis Date:** 2026-05-07

## Directory Layout

```
inventoryvfinal/
├── app/                      # Laravel application code
│   ├── Actions/              # Action classes for business logic
│   │   └── Inventory/        # Inventory-specific actions
│   ├── Concerns/             # PHP traits for shared functionality
│   ├── Enums/                # PHP enums for typed constants
│   ├── Exports/              # Maatwebsite Excel exports
│   ├── Filament/             # Filament v4 panels, resources, components
│   │   ├── Actions/          # Filament action classes
│   │   ├── Admin/            # Admin panel pages, widgets
│   │   ├── Imports/          # Filament import classes
│   │   ├── Portal/           # Portal panel pages
│   │   └── Resources/        # Filament CRUD resources for all models
│   ├── Http/                 # HTTP-related code
│   │   ├── Controllers/      # Laravel controllers (currently empty)
│   │   └── Middleware/       # Custom middleware (e.g, SetDepartmentContext)
│   ├── Livewire/             # Custom Livewire components
│   ├── Models/               # Eloquent models
│   │   ├── Concerns/         # Model traits
│   │   └── Scopes/           # Model query scopes
│   ├── Policies/             # Model policy classes for authorization
│   ├── Providers/            # Service providers
│   │   └── Filament/         # Filament panel providers
│   └── Services/             # Business logic service classes
├── bootstrap/                # Laravel bootstrap files
│   └── cache/                # Bootstrap cache
├── config/                   # Laravel configuration files
├── database/                 # Database-related files
│   ├── factories/            # Model factories for testing
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── public/                   # Publicly accessible files
│   ├── css/                  # Compiled CSS (Filament, Tailwind)
│   ├── fonts/                # Font files (Filament Inter font)
│   └── js/                   # Compiled JS (Filament components)
├── resources/                # Frontend resources
│   ├── office-templates/     # Office document templates
│   └── views/                # Blade views (Filament overrides, exports)
│       ├── exports/          # PDF/Excel export Blade templates
│       ├── filament/         # Filament Blade overrides
│       └── livewire/         # Custom Livewire Blade views
├── routes/                   # Route definitions
│   ├── console.php           # Artisan command definitions
│   └── web.php               # Web routes (empty, Filament handles routing)
├── storage/                  # Laravel storage
│   ├── app/                  # Application files (private, public)
│   ├── framework/            # Framework cache, sessions, views
│   └── logs/                 # Application log files
├── tests/                    # Test suites
│   ├── Feature/              # Feature tests (Filament, API)
│   │   └── Inventory/        # Inventory-specific feature tests
│   └── Unit/                 # Unit tests
├── .agents/                  # Agent skill definitions
│   ├── rules/                # Agent rules
│   └── skills/               # Project-specific skills (Fortify, Inertia, etc.)
├── .claude/                  # Claude Code configuration
├── .github/                  # GitHub workflows
│   └── workflows/            # CI/CD workflow files
├── .planning/                # Project planning files
│   └── codebase/            # Codebase analysis documents (output directory)
├── scratch/                  # Temporary scratch files
├── vendor/                   # Composer dependencies (excluded from version control)
├── boost.json                # Laravel Boost configuration
├── components.json           # Component registry (shadcn/ui?)
├── composer.json             # PHP dependencies
├── eslint.config.js          # ESLint configuration
├── package.json              # Node.js dependencies
├── phpunit.xml               # PHPUnit/Pest configuration
├── pint.json                 # Laravel Pint code style configuration
├── tsconfig.json             # TypeScript configuration
└── vite.config.ts            # Vite build configuration
```

## Directory Purposes

**[app/]:**
- Purpose: Core Laravel application code
- Contains: Controllers, models, middleware, service providers, Filament panels/resources
- Key files: `app/Providers/Filament/AdminPanelProvider.php`, `app/Models/Asset.php`

**[app/Filament/Resources/]:**
- Purpose: Filament v4 CRUD resources for all inventory models
- Contains: Resource classes, schema (form) definitions, table definitions, pages
- Key files: `app/Filament/Resources/Assets/AssetResource.php`

**[database/]:**
- Purpose: Database migrations, seeders, and model factories
- Contains: Migration files, seeder classes, factory classes for testing
- Key files: `database/migrations/XXXX_XX_XX_XXXXXX_create_assets_table.php`

**[tests/]:**
- Purpose: Application tests (Pest PHP)
- Contains: Feature tests (Filament, HTTP), unit tests, inventory-specific tests
- Key files: `tests/Feature/Filament/`, `tests/Unit/`

**[resources/views/]:**
- Purpose: Blade view templates for Filament overrides, PDF exports, Livewire components
- Contains: Export templates, Filament widget/page overrides, Livewire views
- Key files: `resources/views/exports/`

## Key File Locations

**Entry Points:**
- `public/index.php`: Laravel HTTP entry point
- `bootstrap/app.php`: Laravel 12 application configuration

**Configuration:**
- `config/database.php`: Database connection settings
- `pint.json`: Laravel Pint code style rules
- `eslint.config.js`: ESLint rules for JavaScript/TypeScript
- `vite.config.ts`: Vite build tool configuration

**Core Logic:**
- `app/Filament/Resources/`: All inventory CRUD interfaces
- `app/Models/`: All Eloquent data models
- `app/Providers/Filament/`: Filament panel configurations

**Testing:**
- `tests/Feature/`: Feature tests for Filament and HTTP endpoints
- `tests/Unit/`: Unit tests for isolated logic
- `phpunit.xml`: Test runner configuration

## Naming Conventions

**Files:**
- PHP classes: TitleCase (e.g, `AssetResource.php`, `User.php`)
- Blade views: kebab-case (e.g, `low-stock-widget.blade.php`)
- Migration files: snake_case with timestamp prefix (e.g, `2026_05_07_000000_create_assets_table.php`)

**Directories:**
- PHP namespaces: TitleCase matching directory structure (e.g, `app/Filament/Resources/Assets` → `App\Filament\Resources\Assets`)
- Frontend directories: kebab-case (per JavaScript conventions)

## Where to Add New Code

**New Feature (Filament Resource):**
- Implementation: `app/Filament/Resources/[ModelName]/` (create Resource, Pages, Schemas, Tables subdirectories)
- Tests: `tests/Feature/Filament/[ModelName]Test.php`

**New Livewire Component:**
- Implementation: `app/Livewire/[ComponentName].php`
- Blade view: `resources/views/livewire/[component-name].blade.php`

**New Inertia React Page (Planned):**
- Implementation: `resources/js/Pages/[PageName].tsx`
- Wayfinder routes: Auto-generated via `php artisan wayfinder:generate`

**New Model:**
- Implementation: `app/Models/[ModelName].php`
- Factory: `database/factories/[ModelName]Factory.php`
- Migration: `database/migrations/XXXX_XX_XX_XXXXXX_create_[model_names]_table.php`

**Utilities/Shared Code:**
- PHP traits: `app/Concerns/[TraitName].php`
- Service classes: `app/Services/[ServiceName].php`

## Special Directories

**[vendor/]:**
- Purpose: Composer-installed PHP dependencies
- Generated: Yes (via `composer install`)
- Committed: No (in `.gitignore`)

**[node_modules/]:**
- Purpose: NPM-installed JavaScript dependencies
- Generated: Yes (via `npm install`)
- Committed: No (in `.gitignore`)

**[storage/]:**
- Purpose: Application storage (logs, cache, user uploads)
- Generated: Partially (logs, cache generated at runtime)
- Committed: No (storage/app/public may be linked via `php artisan storage:link`)

**[bootstrap/cache/]:**
- Purpose: Laravel bootstrap cache files
- Generated: Yes (via `php artisan config:cache`, etc.)
- Committed: No

---

*Structure analysis: 2026-05-07*
