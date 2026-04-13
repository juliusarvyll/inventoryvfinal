<?php

namespace App\Filament\Actions;

use App\Models\Location;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class SetParentLocationBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('setParentLocation')
            ->label('Set parent location')
            ->icon(Heroicon::OutlinedRectangleStack)
            ->modalHeading('Set parent for selected locations')
            ->modalDescription('Assigns the same parent to every selected location you are allowed to update. Leave empty for a root location. A location cannot be its own parent.')
            ->schema([
                Select::make('parent_id')
                    ->label('Parent location')
                    ->options(fn (): array => Location::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder('None (root)'),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (Collection $records): void {
                $parentId = $this->getData()['parent_id'] ?? null;

                $parent = filled($parentId)
                    ? Location::query()->whereKey($parentId)->first()
                    : null;

                if (filled($parentId) && ! $parent) {
                    $this->failure();

                    return;
                }

                foreach ($records as $record) {
                    if (! $record instanceof Location) {
                        $this->reportBulkProcessingFailure();

                        continue;
                    }

                    if ($parent && (int) $parent->getKey() === (int) $record->getKey()) {
                        $this->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        if ($parent) {
                            $record->parent()->associate($parent);
                        } else {
                            $record->parent()->dissociate();
                        }

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
