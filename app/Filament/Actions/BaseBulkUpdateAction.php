<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\AuditLog;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

abstract class BaseBulkUpdateAction
{
    /**
     * @param  Collection<int, Model>  $records
     * @param  array<string, mixed>  $data
     */
    protected static function processRecords(
        BulkAction $action,
        Collection $records,
        array $data,
        callable $updateCallback,
        ?string $actionType = null
    ): void {
        $updated = 0;
        $updatedRecords = [];

        foreach ($records as $record) {
            if (! $record instanceof Model) {
                $action->reportBulkProcessingFailure();

                continue;
            }

            try {
                $oldValues = $record->getAttributes();
                $updateCallback($record, $data);
                $record->save();
                $updated++;
                $updatedRecords[] = [
                    'id' => $record->getKey(),
                    'type' => get_class($record),
                    'old' => $oldValues,
                    'new' => $record->getAttributes(),
                ];
            } catch (\Throwable) {
                $action->reportBulkProcessingFailure();
            }
        }

        if ($updated > 0) {
            if ($actionType && auth()->check()) {
                AuditLog::query()->create([
                    'user_id' => auth()->id(),
                    'action_type' => $actionType,
                    'subject_type' => $updatedRecords[0]['type'] ?? null,
                    'subject_id' => null,
                    'old_values' => null,
                    'new_values' => [
                        'count' => $updated,
                        'changes' => $data,
                    ],
                    'description' => "Bulk updated {$updated} record(s)",
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::userAgent(),
                ]);
            }

            $action->success();
        } else {
            $action->failure();
        }
    }
}
