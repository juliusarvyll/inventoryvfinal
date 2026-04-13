<?php

namespace App\Filament\Actions;

use App\Models\Supplier;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class ChangeSupplierBulkAction
{
    public static function make(string $recordLabel = 'records'): BulkAction
    {
        return BulkAction::make('changeSupplier')
            ->label('Change supplier')
            ->icon(Heroicon::OutlinedTruck)
            ->modalHeading('Change supplier for selected '.$recordLabel)
            ->modalDescription('Sets the same supplier on every selected '.$recordLabel.' you are allowed to update. Leave empty to unassign.')
            ->schema([
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(fn (): array => Supplier::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder('None'),
            ])
            ->authorizeIndividualRecords('update')
            ->action(function (Collection $records): void {
                $supplierId = $this->getData()['supplier_id'] ?? null;

                $supplier = filled($supplierId)
                    ? Supplier::query()->whereKey($supplierId)->first()
                    : null;

                if (filled($supplierId) && ! $supplier) {
                    $this->failure();

                    return;
                }

                foreach ($records as $record) {
                    if (! $record instanceof Model) {
                        $this->reportBulkProcessingFailure();

                        continue;
                    }

                    try {
                        if ($supplier) {
                            $record->supplier()->associate($supplier);
                        } else {
                            $record->supplier()->dissociate();
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
