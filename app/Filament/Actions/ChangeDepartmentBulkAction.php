<?php

namespace App\Filament\Actions;

use App\Models\Department;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class ChangeDepartmentBulkAction
{
    public static function make(
        string $recordLabel = 'records',
    ): BulkAction {
        return BulkAction::make('changeDepartment')
            ->label('Change department')
            ->icon(Heroicon::OutlinedBuildingOffice2)
            ->modalHeading('Change department for selected '.$recordLabel)
            ->modalDescription('Assigns the same department to every selected '.$recordLabel.' you are allowed to update.')
            ->schema([
                Select::make('department_id')
                    ->label('Department')
                    ->options(fn (): array => Department::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->required()
                    ->searchable()
                    ->preload(),
            ])
            ->action(function (BulkAction $action, Collection $records, array $data): void {
                $departmentId = $data['department_id'] ?? null;

                if (blank($departmentId)) {
                    $action->failure();

                    return;
                }

                $department = Department::query()
                    ->where('is_active', true)
                    ->whereKey($departmentId)
                    ->first();

                if (! $department) {
                    $action->failure();

                    return;
                }

                $updated = 0;
                $failed = 0;

                foreach ($records as $record) {
                    if (! $record instanceof Model) {
                        $action->reportBulkProcessingFailure();
                        $failed++;

                        continue;
                    }

                    try {
                        if (! method_exists($record, 'department')) {
                            $action->reportBulkProcessingFailure();
                            $failed++;

                            continue;
                        }

                        $record->department_id = $department->getKey();
                        $record->save();
                        $updated++;
                    } catch (\Throwable $e) {
                        report($e);
                        $action->reportBulkProcessingFailure();
                        $failed++;
                    }
                }

                if ($updated > 0) {
                    $action->success();
                } else {
                    $action->failure();
                }
            })
            ->successNotificationTitle('Department updated successfully')
            ->failureNotificationTitle(function (int $successCount, int $totalCount) use ($recordLabel): string {
                if ($successCount) {
                    return "{$successCount} of {$totalCount} {$recordLabel} updated";
                }

                return "Failed to update department for any {$recordLabel}";
            })
            ->deselectRecordsAfterCompletion();
    }
}
