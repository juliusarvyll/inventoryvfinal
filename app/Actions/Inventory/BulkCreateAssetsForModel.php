<?php

namespace App\Actions\Inventory;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\StatusLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BulkCreateAssetsForModel
{
    public function __invoke(AssetModel $assetModel, ?string $serialNumbers): int
    {
        $normalizedSerialNumbers = $this->parseSerialNumbers($serialNumbers);

        if ($normalizedSerialNumbers === []) {
            return 0;
        }

        $existingAssets = Asset::query()
            ->whereIn('serial', $normalizedSerialNumbers)
            ->get()
            ->keyBy(fn (Asset $asset): string => Str::lower((string) $asset->serial));

        $conflictingSerialNumbers = collect($normalizedSerialNumbers)
            ->filter(function (string $serialNumber) use ($assetModel, $existingAssets): bool {
                $existingAsset = $existingAssets->get(Str::lower($serialNumber));

                return $existingAsset !== null && $existingAsset->asset_model_id !== $assetModel->getKey();
            })
            ->values()
            ->all();

        if ($conflictingSerialNumbers !== []) {
            throw ValidationException::withMessages([
                'serial_numbers' => 'These serial numbers already belong to another asset model: ' . implode(', ', $conflictingSerialNumbers),
            ]);
        }

        $availableStatus = StatusLabel::query()->firstOrCreate(
            ['name' => 'Available'],
            ['type' => 'deployable', 'color' => '#22c55e']
        );

        return DB::transaction(function () use ($assetModel, $normalizedSerialNumbers, $existingAssets, $availableStatus): int {
            $createdCount = 0;

            foreach ($normalizedSerialNumbers as $serialNumber) {
                if ($existingAssets->has(Str::lower($serialNumber))) {
                    continue;
                }

                Asset::query()->create([
                    'asset_tag' => $this->generateAssetTag($serialNumber),
                    'name' => $assetModel->name,
                    'asset_model_id' => $assetModel->getKey(),
                    'category_id' => $assetModel->category_id,
                    'status_label_id' => $availableStatus->getKey(),
                    'serial' => $serialNumber,
                    'requestable' => false,
                ]);

                $createdCount++;
            }

            return $createdCount;
        });
    }

    /**
     * @return list<string>
     */
    protected function parseSerialNumbers(?string $serialNumbers): array
    {
        if (blank($serialNumbers)) {
            return [];
        }

        return Collection::make(preg_split('/[\r\n,]+/', $serialNumbers) ?: [])
            ->map(fn (string $serialNumber): string => trim($serialNumber))
            ->filter()
            ->unique(fn (string $serialNumber): string => Str::lower($serialNumber))
            ->values()
            ->all();
    }

    protected function generateAssetTag(string $seed): string
    {
        $normalizedBase = Str::upper(Str::of($seed)->replaceMatches('/[^A-Za-z0-9]+/', '')->substr(0, 12));
        $assetTag = 'IMP-' . ($normalizedBase ?: Str::upper(Str::random(12)));

        if (! Asset::query()->where('asset_tag', $assetTag)->exists()) {
            return $assetTag;
        }

        do {
            $assetTag = 'IMP-' . Str::upper(Str::random(12));
        } while (Asset::query()->where('asset_tag', $assetTag)->exists());

        return $assetTag;
    }
}
