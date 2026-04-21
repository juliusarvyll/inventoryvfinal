<?php

use App\Enums\ItemRequestStatus;
use App\Filament\Admin\Widgets\ExpiringLicensesWidget;
use App\Filament\Admin\Widgets\LowStockWidget;
use App\Filament\Admin\Widgets\RecentRequestsWidget;
use App\Filament\Admin\Widgets\RequestStatusChartWidget;
use App\Filament\Admin\Widgets\StatsOverviewWidget;
use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Consumables\Pages\ListConsumables;
use App\Filament\Resources\ItemRequests\Pages\ListItemRequests;
use App\Filament\Resources\Licenses\Pages\ListLicenses;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Consumable;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin list pages expose quick-win filters', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(ListAssets::class)
        ->assertSee('Checked Out');

    Livewire::test(ListItemRequests::class)
        ->assertSee('Request Type');

    Livewire::test(ListUsers::class)
        ->assertSee('Email Verified');

    Livewire::test(ListLicenses::class)
        ->assertSee('Expiration date');

    Livewire::test(ListConsumables::class)
        ->assertSee('Low Stock');
});

test('admin widgets expose actionable shortcuts and clean stats copy', function () {
    $admin = User::factory()->admin()->create();
    $requester = User::factory()->create();
    $this->actingAs($admin);

    Consumable::factory()->create([
        'qty' => 1,
        'min_qty' => 5,
    ]);

    License::factory()->create([
        'expiration_date' => now()->addDays(7),
    ]);

    ItemRequest::factory()->create([
        'user_id' => $requester->getKey(),
        'requester_name' => $requester->name,
        'status' => ItemRequestStatus::Pending,
    ]);

    Livewire::test(StatsOverviewWidget::class)
        ->assertSee('available -')
        ->assertSee('Checked Out Assets')
        ->assertSee('Expiring Licenses')
        ->assertSee('Requests Pipeline')
        ->assertDontSee('·');

    Livewire::test(RequestStatusChartWidget::class)
        ->assertSee('Request Pipeline');

    Livewire::test(RecentRequestsWidget::class)
        ->assertSee('Requester')
        ->assertSee('Open');

    Livewire::test(ExpiringLicensesWidget::class)
        ->assertSee('Open');

    Livewire::test(LowStockWidget::class)
        ->assertSee('Manage');
});
