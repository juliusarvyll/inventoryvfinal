# Coding Conventions

**Analysis Date:** 2026-05-07

## Naming Patterns

**Files:**
- PHP: PascalCase for class files (`app/Models/User.php`, `app/Http/Controllers/InventoryController.php`). Kebab-case for migration files (`database/migrations/2025_05_07_create_inventories_table.php`). Test files use PascalCase with `Test` suffix (`tests/Feature/CreateUserTest.php`).
- JavaScript/TypeScript: PascalCase for React components (`resources/js/Pages/Dashboard.tsx`, `resources/js/Components/Form/TextInput.tsx`). camelCase for utility/helper files (`resources/js/api.ts`, `resources/js/helpers.ts`).

**Functions:**
- PHP: camelCase for methods and functions (`isRegisteredForDiscounts()`, `getFullName()`).
- JavaScript/TypeScript: camelCase for utility functions, PascalCase for React component functions.

**Variables:**
- PHP: camelCase (`$userName`, `$inventoryCount`).
- JavaScript/TypeScript: camelCase (`const userName = 'test'`, `let itemCount = 0`).

**Types:**
- PHP: PascalCase for Enums (`App\Enums\UserStatus`), PascalCase for classes and interfaces.
- TypeScript: PascalCase for interfaces, types, and enum-like objects (`User`, `InventoryItem`, `FormState`).

## Code Style

**Formatting:**
- PHP: Enforced by Laravel Pint v1 via `pint.json` config. Uses Laravel preset by default, 4-space indentation, no trailing whitespace, consistent brace style. Run `vendor/bin/pint --format agent` to auto-format modified PHP files.
- JavaScript/TypeScript: Enforced by Prettier v3 via `.prettierrc` config, integrated with ESLint v9. 2-space indentation, double quotes for strings, semicolons omitted per project defaults.
- `.editorconfig` present in root, enforces consistent line endings (LF), indent style, and charset across all file types.

**Linting:**
- PHP: Laravel Pint v1 (as above), no additional linters.
- JavaScript/TypeScript: ESLint v9 with flat config in `eslint.config.js`, extends React, TypeScript, and Prettier rules. Enforces no unused variables, proper React hook usage, TypeScript type safety.

## Import Organization

**PHP Order:**
1. Framework and package use statements (e.g., `use Illuminate\Support\Facades\DB;`, `use Filament\Forms\Components\TextInput;`)
2. App-specific use statements (e.g., `use App\Models\User;`, `use App\Services\InventoryService;`)
3. Third-party non-framework use statements (if any)

**JavaScript/TypeScript Order:**
1. React and Inertia core imports (e.g., `import React from 'react';`, `import { useForm } from '@inertiajs/react';`)
2. Third-party library imports (e.g., `import { Heroicon } from '@heroicons/react';`)
3. App-specific absolute imports using `@/` alias (e.g., `import { User } from '@/types';`, `import { TextInput } from '@/Components/Form';`)
4. Relative imports (e.g., `import './styles.css';`)

**Path Aliases:**
- TypeScript uses `@/` alias mapped to `resources/js/` via `vite.config.js` and `tsconfig.json`. Example: `import { api } from '@/lib/api'` resolves to `resources/js/lib/api.ts`.

## Error Handling

**Patterns:**
- PHP: Laravel's exception handler converts uncaught exceptions to HTTP responses. Try/catch blocks used for recoverable errors. Form validation uses inline rules or FormRequests, with errors passed to Inertia pages via `$errors`.
- JavaScript/TypeScript: Inertia's `useForm` hook handles form error state automatically. React error boundaries wrap top-level components. Async functions use try/catch for promise rejections.

## Logging

**Framework:** Laravel's built-in `Log` facade, with Pail v1 for local log viewing.

**Patterns:**
- PHP: `Log::info('message', ['context' => $value])` for structured logging. Error-level logs for exceptions, info for routine operations.
- JavaScript/TypeScript: `console.error()` for development errors, no production logging framework configured.

## Comments

**When to Comment:**
- PHP: PHPDoc blocks required for all classes, public methods, and complex logic. Inline comments only for exceptionally complex or non-obvious code.
- JavaScript/TypeScript: JSDoc/TSDoc blocks for functions, components, and complex type definitions. No redundant comments for self-documenting code.

**JSDoc/TSDoc:**
- Used to define parameter types, return types, and complex type shapes. Example:
  ```typescript
  /**
   * Fetches inventory items from the API.
   * @param {string} category - The inventory category to filter by.
   * @returns {Promise<InventoryItem[]>} Array of inventory items.
   */
  export async function getInventory(category: string): Promise<InventoryItem[]> {
      // ...
  }
  ```

## Function Design

**Size:** Functions should be small, single-responsibility. PHP methods no longer than 50 lines, JS functions no longer than 30 lines where possible.

**Parameters:** Explicit type hints for all PHP parameters, nullable types marked with `?`. TypeScript parameters use type annotations. Avoid more than 3 parameters; use objects for complex inputs.

**Return Values:** Explicit return type declarations for all PHP methods. TypeScript functions use return type annotations. Void return type for methods with no return value.

## Module Design

**Exports:** PHP classes are exported via their namespace, no explicit export statements. JavaScript/TypeScript uses ES modules: `export default` for React components, named exports for utilities and types.

**Barrel Files:** Used in `resources/js/Components/` and `resources/js/Types/` via `index.ts` files to aggregate exports. Example: `resources/js/Components/index.ts` exports all shared components for easier imports.

---

*Convention analysis: 2026-05-07*
