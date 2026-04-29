<?php

use App\Actions\Inventory\ApproveItemRequest;
use App\Enums\ItemRequestStatus;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('license assignments cannot exceed the licensed seat count', function () {
    $license = License::factory()->create(['seats' => 1]);
    LicenseSeat::factory()->create(['license_id' => $license->getKey()]);

    $requestor = User::factory()->create();
    $handler = User::factory()->itStaff()->create();

    $itemRequest = ItemRequest::query()->create([
        'user_id' => $requestor->getKey(),
        'requestable_type' => License::class,
        'requestable_id' => $license->getKey(),
        'status' => ItemRequestStatus::Pending,
        'qty' => 1,
        'reason' => 'Need software access',
    ]);

    expect(fn () => app(ApproveItemRequest::class)($itemRequest, $handler))
        ->toThrow(RuntimeException::class, 'No license seats are available for this assignment.');

    expect($itemRequest->refresh()->status)->toBe(ItemRequestStatus::Pending);
    $this->assertDatabaseCount('license_seats', 1);
});

test('license seat availability reuses preloaded counts without extra queries', function () {
    $license = License::factory()->create(['seats' => 3]);
    LicenseSeat::factory()->count(2)->create(['license_id' => $license->getKey()]);

    $hydratedLicense = License::query()
        ->withCount('licenseSeats')
        ->findOrFail($license->getKey());

    $connection = DB::connection();
    $connection->flushQueryLog();
    $connection->enableQueryLog();

    expect($hydratedLicense->assignedSeatsCount())->toBe(2)
        ->and($hydratedLicense->seatsAvailable())->toBe(1)
        ->and($connection->getQueryLog())->toHaveCount(0);
});
