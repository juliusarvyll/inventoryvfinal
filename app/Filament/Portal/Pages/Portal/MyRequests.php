<?php

namespace App\Filament\Portal\Pages\Portal;

use App\Models\ItemRequest;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class MyRequests extends Page
{
    private const MAX_RESULTS = 25;

    protected string $view = 'filament.portal.pages.portal.my-requests';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'My Requests';

    protected static ?int $navigationSort = 3;

    public function getTitle(): string|Htmlable
    {
        return 'Request History';
    }

    /**
     * @return array<string, Collection<int, ItemRequest>>
     */
    protected function getViewData(): array
    {
        return [
            'requests' => ItemRequest::query()
                ->with(['handler'])
                ->where('user_id', auth()->id())
                ->latest()
                ->limit(self::MAX_RESULTS)
                ->get(),
        ];
    }
}
