<?php

namespace App\Filament\Actions;

use App\Enums\InventoryCategoryType;
use App\Models\Category;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class ChangeCategoryBulkAction
{
    public static function make(
        InventoryCategoryType $categoryType,
        string $recordLabel = 'records',
    ): BulkAction {
        return BulkAction::make('changeCategory')
            ->label('Change category')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->modalHeading('Change category for selected '.$recordLabel)
            ->modalDescription('Assigns the same category to every selected '.$recordLabel.' you are allowed to update.')
            ->schema([
                Select::make('category_id')
                    ->label('Category')
                    ->options(fn (): array => Category::query()
                        ->ofType($categoryType)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->required()
                    ->searchable()
                    ->preload(),
            ])
            ->action(function (BulkAction $action, Collection $records, array $data) use ($categoryType): void {
                $categoryId = $data['category_id'] ?? null;

                if (blank($categoryId)) {
                    $action->failure();

                    return;
                }

                $category = Category::query()
                    ->ofType($categoryType)
                    ->whereKey($categoryId)
                    ->first();

                if (! $category) {
                    $action->failure();

                    return;
                }

                $updated = 0;

                foreach ($records as $record) {
                    if (! $record instanceof Model) {
                        $action->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        $record->category()->associate($category);
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
