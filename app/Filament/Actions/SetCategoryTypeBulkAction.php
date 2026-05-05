<?php

namespace App\Filament\Actions;

use App\Enums\InventoryCategoryType;
use App\Models\Category;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
                        ->mapWithKeys(fn (InventoryCategoryType $case): array => [$case->value => Str::headline($case->value)])
                        ->all())
                    ->required()
                    ->native(false),
            ])
            ->action(function (BulkAction $action, Collection $records, array $data): void {
                $typeValue = $data['type'] ?? null;

                if (blank($typeValue)) {
                    $action->failure();

                    return;
                }

                try {
                    $type = InventoryCategoryType::from((string) $typeValue);
                } catch (\ValueError) {
                    $action->failure();

                    return;
                }

                $updated = 0;

                foreach ($records as $record) {
                    if (! $record instanceof Category) {
                        $action->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        $record->type = $type;
                        $record->save();
                        $updated++;
                    } catch (\Throwable) {
                        $action->reportBulkProcessingFailure();
                    }
                }

                if ($updated > 0) {
                    $action->success();
                } else {
                    $action->failure();
                }
            })
            ->deselectRecordsAfterCompletion();
    }
}
