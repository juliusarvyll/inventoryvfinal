<?php

namespace App\Filament\Actions;

use App\Enums\InventoryCategoryType;
use App\Models\Category;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

final class SetCategoryTypeBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('setCategoryType')
            ->label('Change category type')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->modalHeading('Change type for selected categories')
            ->modalDescription('Sets the inventory type for every selected category you are allowed to update.')
            ->schema([
                Select::make('type')
                    ->label('Type')
                    ->options(collect(InventoryCategoryType::cases())
                        ->mapWithKeys(fn (InventoryCategoryType $case): array => [$case->value => \Illuminate\Support\Str::headline($case->value)])
                        ->all())
                    ->required()
                    ->native(false),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (Collection $records): void {
                $typeValue = $this->getData()['type'] ?? null;

                if (blank($typeValue)) {
                    $this->failure();

                    return;
                }

                try {
                    $type = InventoryCategoryType::from((string) $typeValue);
                } catch (\ValueError) {
                    $this->failure();

                    return;
                }

                foreach ($records as $record) {
                    if (! $record instanceof Category) {
                        $this->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        $record->type = $type;
                        $record->save();
                    } catch (\Throwable) {
                        $this->reportBulkProcessingFailure();
                    }
                }

                $this->success();
            })
            ->deselectRecordsAfterCompletion();
    }
}
