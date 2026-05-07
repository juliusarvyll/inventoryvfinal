# Testing Patterns

**Analysis Date:** 2026-05-07

## Test Framework

**Runner:**
- Pest v4 (pestphp/pest v4) as primary test framework
- PHPUnit v12 (phpunit/phpunit v12) as underlying runner
- Config: `phpunit.xml` (root), `tests/pest.php` (Pest configuration)

**Assertion Library:**
- Pest's built-in `expect()` syntax
- PHPUnit core assertions (`assertDatabaseHas`, `assertTrue`, etc.)
- Filament-specific assertions via `Pest\Livewire` (`assertNotified`, `assertHasFormErrors`, `assertCanSeeTableRecords`)

**Run Commands:**
```bash
php artisan test --compact              # Run all tests
php artisan test --compact --filter=testName  # Run specific test by name
php artisan test --coverage             # Run tests with coverage report
```

## Test File Organization

**Location:** Separate `tests/` directory (not co-located with source code)
- `tests/Feature/`: Feature tests for HTTP endpoints, Inertia pages, Filament resources, and authentication
- `tests/Unit/`: Unit tests for services, helpers, and standalone classes
- `tests/Browser/`: Not present (no E2E testing configured)

**Naming:**
- PascalCase with `Test` suffix: `CreateUserTest.php`, `InventoryServiceTest.php`
- Matches the class or feature under test

**Structure:**
```
tests/
├── Feature/
│   ├── Auth/
│   │   └── LoginTest.php
│   ├── Inventory/
│   │   ├── CreateInventoryTest.php
│   │   └── ListInventoryTest.php
│   └── ...
├── Unit/
│   ├── Services/
│   │   └── InventoryServiceTest.php
│   └── ...
├── pest.php                # Pest configuration
└── TestCase.php            # Base test case (extends Laravel TestCase)
```

## Test Structure

**Suite Organization:**
```php
<?php

use function Pest\Livewire\livewire;
use App\Models\User;
use App\Filament\Resources\UserResource\Pages\ListUsers;

test('authenticated users can list users', function () {
    $this->actingAs(User::factory()->create());
    $users = User::factory()->count(3)->create();

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});
```

**Patterns:**
- Setup: `$this->actingAs(User::factory()->create())` for authenticated panel tests. `RefreshDatabase` trait for tests requiring database state.
- Teardown: Handled automatically by Pest and Laravel's test case, no explicit teardown required.
- Assertion pattern: Prefer Pest's `expect()` syntax for simple assertions, Laravel/Filament assertions for domain-specific checks.

## Mocking

**Framework:** Pest's built-in mocking, PHPUnit mock objects, Laravel's fake facades (`Mail::fake()`, `Queue::fake()`, `Event::fake()`).

**Patterns:**
```php
test('welcome email is sent when user registers', function () {
    Mail::fake();

    $user = User::factory()->create();

    // Trigger registration action

    Mail::assertSent(WelcomeEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});
```

**What to Mock:**
- External API calls, mail, queues, events, and time-sensitive operations.

**What NOT to Mock:**
- Eloquent models, database queries (use `RefreshDatabase` instead), and Filament form components.

## Fixtures and Factories

**Test Data:**
- Laravel model factories in `database/factories/` (e.g., `UserFactory.php`, `InventoryFactory.php`). Support custom states for specific scenarios:
  ```php
  $admin = User::factory()->admin()->create();
  $lowStockItem = Inventory::factory()->lowStock()->create();
  ```

**Location:**
- Factories: `database/factories/`
- Test-specific fixtures: Inline in test files, no shared fixture directory.

## Coverage

**Requirements:** No enforced coverage threshold, but every code change must have corresponding tests per project test enforcement rules.

**View Coverage:**
```bash
php artisan test --coverage
```

## Test Types

**Unit Tests:**
- Scope: Isolated classes, methods, and pure functions with no framework dependencies. Use mocks for external dependencies.
- Location: `tests/Unit/`

**Integration Tests:**
- Scope: Feature tests covering HTTP requests, Inertia page rendering, Filament resource CRUD, and authentication flows. Use `RefreshDatabase` and `actingAs` for state.
- Location: `tests/Feature/`

**E2E Tests:**
- Not configured (no Laravel Dusk, Cypress, or Playwright).

## Common Patterns

**Async Testing:**
- PHP: No native async support. Use Laravel's fake facades for async operations (queues, mail, events) and assert they were dispatched correctly.

**Error Testing:**
```php
test('user creation fails with invalid email', function () {
    $this->actingAs(User::factory()->create());

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'invalid-email',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'email'])
        ->assertNotNotified();
});
```

---

*Testing analysis: 2026-05-07*
