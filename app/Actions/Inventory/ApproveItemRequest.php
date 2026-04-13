<?php

namespace App\Actions\Inventory;

use App\Enums\ItemRequestStatus;
use App\Models\Accessory;
use App\Models\Consumable;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApproveItemRequest
{
    public function __construct(
        protected CheckoutAsset $checkoutAsset,
    ) {
    }

    public function __invoke(ItemRequest $itemRequest, User $handler): ItemRequest
    {
        if ($itemRequest->status !== ItemRequestStatus::Pending) {
            throw new RuntimeException('Only pending requests can be approved.');
        }

        if (! $itemRequest->user_id) {
            throw new RuntimeException('Requests without a linked system user must be fulfilled manually.');
        }

        return DB::transaction(function () use ($itemRequest, $handler): ItemRequest {
            $requestable = $itemRequest->requestable()->getResults();

            match (true) {
                $requestable instanceof \App\Models\Asset => ($this->checkoutAsset)(
                    $requestable,
                    $itemRequest->user,
                    $handler,
                    $itemRequest->reason,
                ),
                $requestable instanceof License => $this->assignLicenseSeats($requestable, $itemRequest),
                $requestable instanceof Accessory => $this->assignAccessory($requestable, $itemRequest),
                $requestable instanceof Consumable => $this->assignConsumable($requestable, $itemRequest),
                default => throw new RuntimeException('This request type is not supported yet.'),
            };

            $itemRequest->forceFill([
                'status' => ItemRequestStatus::Fulfilled,
                'handled_by' => $handler->getKey(),
                'handled_at' => now(),
                'fulfilled_at' => now(),
            ])->save();

            return $itemRequest->load(['user', 'handler', 'requestable']);
        });
    }

    protected function assignLicenseSeats(License $license, ItemRequest $itemRequest): void
    {
        $license->assertSeatsAvailable($itemRequest->qty);

        foreach (range(1, $itemRequest->qty) as $seatNumber) {
            LicenseSeat::query()->create([
                'license_id' => $license->getKey(),
                'assigned_to' => $itemRequest->user_id,
                'assigned_at' => now(),
            ]);
        }
    }

    protected function assignAccessory(Accessory $accessory, ItemRequest $itemRequest): void
    {
        if ($accessory->qtyRemaining() < $itemRequest->qty) {
            throw new RuntimeException('The requested accessory quantity is not available.');
        }

        $accessory->checkouts()->create([
            'assigned_to' => $itemRequest->user_id,
            'qty' => $itemRequest->qty,
            'assigned_at' => now(),
            'note' => $itemRequest->reason,
        ]);
    }

    protected function assignConsumable(Consumable $consumable, ItemRequest $itemRequest): void
    {
        if ($consumable->qtyRemaining() < $itemRequest->qty) {
            throw new RuntimeException('The requested consumable quantity is not available.');
        }

        $consumable->assignments()->create([
            'assigned_to' => $itemRequest->user_id,
            'qty' => $itemRequest->qty,
            'assigned_at' => now(),
            'note' => $itemRequest->reason,
        ]);
    }
}
