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
     * @return array{checklists: array<int, string>}
     */
    public static function executionFormData(PreventiveMaintenanceSchedule $schedule): array
    {
        $checklists = $schedule->checklists;
        
        return [
            'checklists' => $checklists->pluck('id')->mapWithKeys(fn ($id) => [$id => $schedule->checklists->find($id)->category->name . ' - ' . ($schedule->checklists->find($id)->instructions ?: 'No instructions')])->toArray(),
            'checklist_items' => $checklists->mapWithKeys(fn ($checklist) => [$checklist->id => $checklist->items->map(fn ($item): array => [
                'id' => $item->getKey(),
                'task' => $item->task,
                'input_label' => $item->input_label,
            ])->toArray()])->toArray(),
        ];
    }

    /**
     * @return array<int, Select|Repeater|Textarea>
     */
    public static function executionSchema(): array
    {
        return [
            Select::make('checklist_id')
                ->label('Checklist')
                ->options(fn (Get $get): array => $get('checklists') ?? [])
                ->required()
                ->live()
                ->reactive(),
            Repeater::make('items')
                ->label('Checklist Items')
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->collapsed(false)
                ->default(fn (Get $get): array => $get('checklist_items.' . $get('checklist_id')) ?? [])
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
