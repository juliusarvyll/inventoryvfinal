<?php

use App\Actions\Inventory\CheckoutAsset;
use App\Models\Asset;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checking out an asset creates a checkout record and deploys the asset', function () {
    $available = StatusLabel::firstOrCreate(['name' => 'Available'], ['color' => '#22c55e', 'type' => 'deployable']);
    $deployed = StatusLabel::firstOrCreate(['name' => 'Deployed'], ['color' => '#3b82f6', 'type' => 'deployable']);

    $asset = Asset::factory()->create(['status_label_id' => $available->getKey()]);
    $assignee = User::factory()->create();
    $actor = User::factory()->admin()->create();

    $checkout = app(CheckoutAsset::class)($asset, $assignee, $actor, 'Issued for onboarding');

    expect($checkout->asset_id)->toBe($asset->getKey())
        ->and($checkout->assigned_to)->toBe($assignee->getKey())
        ->and($checkout->checked_out_by)->toBe($actor->getKey());

    expect($asset->refresh()->status_label_id)->toBe($deployed->getKey());
    $this->assertDatabaseCount('asset_checkouts', 1);
});

test('an asset cannot be checked out twice while still assigned', function () {
    $available = StatusLabel::firstOrCreate(['name' => 'Available'], ['color' => '#22c55e', 'type' => 'deployable']);
    StatusLabel::firstOrCreate(['name' => 'Deployed'], ['color' => '#3b82f6', 'type' => 'deployable']);

    $asset = Asset::factory()->create(['status_label_id' => $available->getKey()]);
    $assignee = User::factory()->create();

    app(CheckoutAsset::class)($asset, $assignee);

    expect(fn () => app(CheckoutAsset::class)($asset, User::factory()->create()))
        ->toThrow(RuntimeException::class, 'This asset is already checked out.');
});
