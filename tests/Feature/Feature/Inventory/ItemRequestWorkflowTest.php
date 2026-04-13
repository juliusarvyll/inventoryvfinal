<?php

use App\Actions\Inventory\ApproveItemRequest;
use App\Enums\ItemRequestStatus;
use App\Models\Asset;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('approving an asset request fulfills it and checks out the asset', function () {
    $available = StatusLabel::factory()->available()->create();
    StatusLabel::factory()->deployed()->create();

    $requestor = User::factory()->create();
    $handler = User::factory()->itStaff()->create();
    $asset = Asset::factory()->create(['status_label_id' => $available->getKey()]);

    $itemRequest = ItemRequest::query()->create([
        'user_id' => $requestor->getKey(),
        'requester_name' => $requestor->name,
        'requestable_type' => Asset::class,
        'requestable_id' => $asset->getKey(),
        'status' => ItemRequestStatus::Pending,
        'qty' => 1,
        'reason' => 'New starter kit',
    ]);

    app(ApproveItemRequest::class)($itemRequest, $handler);

    expect($itemRequest->refresh()->status)->toBe(ItemRequestStatus::Fulfilled)
        ->and($itemRequest->handled_by)->toBe($handler->getKey())
        ->and($itemRequest->fulfilled_at)->not->toBeNull();

    $this->assertDatabaseHas('asset_checkouts', [
        'asset_id' => $asset->getKey(),
        'assigned_to' => $requestor->getKey(),
    ]);
});

test('external requester item requests can be stored without a linked user for any requestable model', function () {
    $license = License::factory()->create(['requestable' => true]);

    $itemRequest = ItemRequest::query()->create([
        'user_id' => null,
        'requester_name' => 'Walk-in requester',
        'requestable_type' => License::class,
        'requestable_id' => $license->getKey(),
        'status' => ItemRequestStatus::Pending,
        'qty' => 1,
        'reason' => 'Front desk request',
    ]);

    expect($itemRequest->requester_display_name)->toBe('Walk-in requester')
        ->and($itemRequest->user)->toBeNull()
        ->and($itemRequest->requestable_display_name)->toBe($license->name);
});

test('external requester item requests must be fulfilled manually', function () {
    $available = StatusLabel::factory()->available()->create();
    StatusLabel::factory()->deployed()->create();

    $handler = User::factory()->itStaff()->create();
    $asset = Asset::factory()->create([
        'requestable' => true,
        'status_label_id' => $available->getKey(),
    ]);

    $itemRequest = ItemRequest::query()->create([
        'user_id' => null,
        'requester_name' => 'Walk-in requester',
        'requestable_type' => Asset::class,
        'requestable_id' => $asset->getKey(),
        'status' => ItemRequestStatus::Pending,
        'qty' => 1,
        'reason' => 'Front desk request',
    ]);

    expect(fn () => app(ApproveItemRequest::class)($itemRequest, $handler))
        ->toThrow(\RuntimeException::class, 'Requests without a linked system user must be fulfilled manually.');
});
