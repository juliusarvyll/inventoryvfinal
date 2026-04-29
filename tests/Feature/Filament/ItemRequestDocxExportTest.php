<?php

use App\Enums\ItemRequestStatus;
use App\Filament\Resources\ItemRequests\Pages\ViewItemRequest;
use App\Models\ItemRequest;
use App\Models\User;
use App\Services\ItemRequestTemplateExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('item request pdf export renders from the spreadsheet template', function () {
    $requester = User::factory()->create([
        'name' => 'Jane Requester',
        'email' => 'jane.requester@example.com',
        'department' => 'ICT',
        'job_title' => 'QA Analyst',
    ]);

    $handler = User::factory()->create([
        'name' => 'Mark Handler',
        'email' => 'mark.handler@example.com',
    ]);

    $itemRequest = ItemRequest::factory()->create([
        'user_id' => $requester->getKey(),
        'requested_by' => $requester->name,
        'department' => 'ICT',
        'status' => ItemRequestStatus::Approved,
        'qty' => 2,
        'items' => 'Adobe Creative Cloud License',
        'unit_cost' => 2499.50,
        'remarks' => 'For the design workstation refresh.',
        'source_of_fund' => 'ICT Budget',
        'purpose_project' => 'Creative Suite rollout for the multimedia lab.',
        'deny_reason' => null,
        'handled_by' => $handler->getKey(),
        'handled_at' => now()->subDay(),
        'fulfilled_at' => now(),
    ]);

    $document = app(ItemRequestTemplateExporter::class)->generate($itemRequest);

    expect($document)
        ->not->toBeEmpty()
        ->toStartWith('%PDF');
});

test('item request view page exposes the pdf export action', function () {
    $admin = User::factory()->admin()->create();
    $itemRequest = ItemRequest::factory()->create();

    $this->actingAs($admin);

    Livewire::test(ViewItemRequest::class, ['record' => $itemRequest->getKey()])
        ->assertSee('Export PDF');
});
