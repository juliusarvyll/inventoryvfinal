<?php

use App\Filament\Resources\PreventiveMaintenanceChecklists\Pages\CreatePreventiveMaintenanceChecklist;
use App\Filament\Resources\PreventiveMaintenanceChecklists\Pages\EditPreventiveMaintenanceChecklist;
use App\Filament\Resources\PreventiveMaintenanceChecklists\Pages\ListPreventiveMaintenanceChecklists;
use App\Filament\Resources\PreventiveMaintenanceSchedules\Pages\CreatePreventiveMaintenanceSchedule;
use App\Filament\Resources\PreventiveMaintenanceSchedules\Pages\ListPreventiveMaintenanceSchedules;
use App\Models\Category;
use App\Models\PreventiveMaintenanceChecklist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('pm checklist resource pages render redesigned filament ui components', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->asset()->create();
    $checklist = PreventiveMaintenanceChecklist::query()->create([
        'category_id' => $category->getKey(),
        'is_active' => true,
        'created_by' => $admin->getKey(),
        'updated_by' => $admin->getKey(),
    ]);
    $checklist->categories()->attach($category);

    $this->actingAs($admin);

    Livewire::test(ListPreventiveMaintenanceChecklists::class)
        ->assertSee('Categories')
        ->assertSee('Items')
        ->assertSee('Active');

    Livewire::test(CreatePreventiveMaintenanceChecklist::class)
        ->assertSee('Checklist Setup')
        ->assertSee('Pick one or more categories that can share this checklist.');

    Livewire::test(EditPreventiveMaintenanceChecklist::class, ['record' => $checklist->getKey()])
        ->assertSee('Checklist Setup')
        ->assertSee('Active');
});

test('pm schedule resource pages render redesigned filament ui components', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->asset()->create();
    $checklist = PreventiveMaintenanceChecklist::query()->create([
        'category_id' => $category->getKey(),
        'is_active' => true,
        'created_by' => $admin->getKey(),
        'updated_by' => $admin->getKey(),
    ]);
    $checklist->categories()->attach($category);

    $this->actingAs($admin);

    Livewire::test(ListPreventiveMaintenanceSchedules::class)
        ->assertSee('Location')
        ->assertSee('Categories')
        ->assertSee('Executions');

    Livewire::test(CreatePreventiveMaintenanceSchedule::class)
        ->assertSee('Schedule Scope')
        ->assertSee('Checklist Coverage');
});
