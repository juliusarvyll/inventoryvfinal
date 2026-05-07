# Technology Stack

**Analysis Date:** 2026-05-07

## Languages

**Primary:**
- PHP 8.3 - Backend logic, Laravel, Filament, Livewire, Fortify, and all PHP packages

**Secondary:**
- JavaScript (React/TypeScript) - Frontend SPA via Inertia.js and React 19 (`resources/js/Pages`)
- CSS - Styling via Tailwind CSS v4

## Runtime

**Environment:**
- PHP 8.3 (served via Laravel Herd locally at `https://inventoryvfinal.test`)
- Node.js (LTS, for frontend build tooling via Vite)

**Package Manager:**
- Composer 2.x - PHP dependency management
- Lockfile: `composer.lock` (present)
- npm 10.x - JavaScript dependency management
- Lockfile: `package-lock.json` (present)

## Frameworks

**Core:**
- Laravel 12 (`laravel/framework`) - Primary backend framework
- Filament v4 (`filament/filament`) - Admin panel UI framework (built on Livewire, Alpine.js, Tailwind)
- Inertia.js v3 (`inertiajs/inertia-laravel`) - SPA bridge between Laravel backend and React frontend
- React 19 (`react`) - Frontend component library
- Livewire v3 (`livewire/livewire`) - Interactive server-side components

**Testing:**
- Pest v4 (`pestphp/pest`) - PHP testing framework (with PHPUnit v12 `phpunit/phpunit` as foundation)
- `pestphp/pest-plugin-livewire` - Livewire component testing

**Build/Dev:**
- Vite - Frontend asset bundler (with `@laravel/vite-plugin-wayfinder` v0)
- Tailwind CSS v4 (`tailwindcss`) - Utility-first CSS framework
- ESLint v9 (`eslint`) - JavaScript/TypeScript linting
- Prettier v3 (`prettier`) - Code formatting
- Laravel Pint v1 (`laravel/pint`) - PHP code formatting
- Laravel Sail v1 (`laravel/sail`) - Docker-based local development environment

## Key Dependencies

**Critical:**
- `inertiajs/inertia-laravel` v3 - Laravel adapter for Inertia.js
- `@inertiajs/react` v3 - React adapter for Inertia.js
- `laravel/fortify` v1 - First-party authentication backend
- `livewire/livewire` v3 - Interactive PHP components
- `filament/filament` v4 - Admin panel framework
- `laravel/wayfinder` v0 - Auto-generated TypeScript route functions
- `laravel/boost` v2 - MCP server for Laravel tooling
- `laravel/mcp` v0 - MCP protocol support

**Infrastructure:**
- Laravel Herd - Local development server
- Laravel Pail v1 (`laravel/pail`) - Local log viewer
- Laravel Prompts v0 (`laravel/prompts`) - CLI prompt tools

## Configuration

**Environment:**
- Configured via `.env` file (gitignored, not committed to repo)
- Key configs: `config/app.php`, `config/database.php`, `config/fortify.php`, `config/filament.php`

**Build:**
- `vite.config.js` - Vite bundler configuration
- Tailwind CSS v4 uses PostCSS integration (no separate config file required for v4 zero-config setup)

## Platform Requirements

**Development:**
- PHP 8.3+, Composer, Node.js, npm, Laravel Herd

**Production:**
- Laravel Cloud (recommended per deployment rules)
- PHP 8.3+ environment, Node.js (for frontend build step)

---

*Stack analysis: 2026-05-07*
