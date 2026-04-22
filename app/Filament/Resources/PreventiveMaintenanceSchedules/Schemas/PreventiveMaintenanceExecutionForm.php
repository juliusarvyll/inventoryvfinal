<?php

namespace App\Filament\Resources\PreventiveMaintenanceSchedules\Schemas;

use App\Models\PreventiveMaintenanceChecklist;
use App\Models\PreventiveMaintenanceSchedule;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

class PreventiveMaintenanceExecutionForm
{
    /**
     * @return array{checklist_items: array}
     */
    public static function executionFormData(PreventiveMaintenanceSchedule $schedule): array
    {
        $schedule->loadMissing('checklist.items');
        
        return [
            'checklist_items' => $schedule->checklist->items->map(fn ($item): array => [
                'id' => $item->getKey(),
                'task' => $item->task,
                'input_label' => $item->input_label,
            ])->toArray(),
        ];
    }

    /**
     * @return array<int, Repeater|Textarea>
     */
    public static function executionSchema(): array
    {
        return [
            Repeater::make('items')
                ->label('Checklist Items')
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->collapsed(false)
                ->default(fn (Get $get): array => $get('checklist_items') ?? [])
                ->schema([
                    Hidden::make('id')->required(),
                    Hidden::make('input_label'),
                    Textarea::make('task')->disabled()->dehydrated(false)->rows(2)->columnSpanFull(),
                    Select::make('is_passed')->label('Result')->options(['1' => 'Pass', '0' => 'Fail'])->placeholder('Pending'),
                    TextInput::make('input_value')->label(fn (Get $get) => (string) $get('input_label'))->visible(fn (Get $get) => filled($get('input_label'))),
                    FileUpload::make('evidence_path')->label('Evidence')->disk('public')->directory('preventive-maintenance/evidence')->visibility('public')->acceptedFileTypes(['image/*', 'application/pdf'])->maxSize(5120)->columnSpanFull(),
                ])->columnSpanFull(),
            Textarea::make('general_notes')->label('General notes')->rows(4)->columnSpanFull(),
        ];
    }
}
