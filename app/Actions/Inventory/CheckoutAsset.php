<?php

namespace App\Actions\Inventory;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\StatusLabel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutAsset
{
    public function __invoke(Asset $asset, User $assignee, ?User $actor = null, ?string $note = null): AssetCheckout
    {
        if ($asset->activeCheckout()->exists()) {
            throw new RuntimeException('This asset is already checked out.');
        }

        return DB::transaction(function () use ($asset, $assignee, $actor, $note): AssetCheckout {
            $checkout = $asset->checkouts()->create([
                'assigned_to' => $assignee->getKey(),
                'checked_out_by' => $actor?->getKey(),
                'assigned_at' => now(),
                'note' => $note,
            ]);

            $deployedStatus = StatusLabel::query()->named('Deployed')->first();

            if ($deployedStatus !== null) {
                $asset->update(['status_label_id' => $deployedStatus->getKey()]);
            }

            return $checkout->load(['asset', 'assignee', 'checkedOutBy']);
        });
    }
}
