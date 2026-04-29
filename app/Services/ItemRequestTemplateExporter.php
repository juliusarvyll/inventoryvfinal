<?php

namespace App\Services;

use AnourValar\Office\DocumentService;
use AnourValar\Office\Format;
use AnourValar\Office\SheetsService;
use App\Models\ItemRequest;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemRequestTemplateExporter
{
    public function __construct(
        public SheetsService $sheetsService,
    ) {}

    public function generate(ItemRequest $itemRequest): string
    {
        $itemRequest = $this->loadRelationships($itemRequest);

        $filePath = storage_path('app/generated.docx');

        (new DocumentService())
            ->generate(
                $this->templatePath(),
                $this->templateData($itemRequest)
            )
            ->saveAs($filePath);

        return $filePath;
    }

    public function download(ItemRequest $itemRequest): StreamedResponse
{
    $filename = Str::slug($this->documentReference($itemRequest)).'.docx';
    $filePath = $this->generate($itemRequest);

    return response()->streamDownload(
        function () use ($filePath): void {
            echo file_get_contents($filePath);
        },
        $filename,
        [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
    );
}

    public function templatePath(): string
    {
        return resource_path('office-templates/template.docx');
    }

    protected function loadRelationships(ItemRequest $itemRequest): ItemRequest
    {
        return $itemRequest->loadMissing(['user', 'handler']);
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function templateData(ItemRequest $itemRequest): array
    {
        return [
            'document' => [
                'reference' => $this->documentReference($itemRequest),
                'generated_at' => now()->format('F j, Y g:i A'),
            ],
            'request' => [
                'id' => (string) $itemRequest->getKey(),
                'status' => Str::headline($itemRequest->status->name),
                'qty' => (string) $itemRequest->qty,
                'items' => $this->stringValue($itemRequest->items),
                'unit_cost' => $this->stringValue($itemRequest->unit_cost),
                'remarks' => $this->stringValue($itemRequest->remarks),
                'source_of_fund' => $this->stringValue($itemRequest->source_of_fund),
                'purpose_project' => $this->stringValue($itemRequest->purpose_project),
                'deny_reason' => $this->stringValue($itemRequest->deny_reason),
                'submitted_at' => $this->formatDateTime($itemRequest->created_at),
                'handled_at' => $this->formatDateTime($itemRequest->handled_at),
                'fulfilled_at' => $this->formatDateTime($itemRequest->fulfilled_at),
                'requested_by' => $itemRequest->requester_display_name,
                'requester_email' => $this->stringValue($itemRequest->user?->email),
                'department' => $this->stringValue($itemRequest->department ?: $itemRequest->user?->department),
                'handler_name' => $this->stringValue($itemRequest->handler?->name),
                'handler_email' => $this->stringValue($itemRequest->handler?->email),
            ],
        ];
    }

    protected function documentReference(ItemRequest $itemRequest): string
    {
        return 'IR-'.str_pad((string) $itemRequest->getKey(), 6, '0', STR_PAD_LEFT);
    }

    protected function formatDateTime(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        return $value->format('F j, Y g:i A');
    }

    protected function stringValue(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        return (string) $value;
    }
}
