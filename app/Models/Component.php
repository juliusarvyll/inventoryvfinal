<?php

namespace App\Models;

use App\Enums\InventoryCategoryType;
use App\Models\Concerns\BehavesAsTypedAsset;
use Database\Factories\ComponentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Component extends Asset
{
    /** @use HasFactory<ComponentFactory> */
    use BehavesAsTypedAsset;

    use HasFactory;

    protected $table = 'assets';

    protected static function inventoryCategoryType(): InventoryCategoryType
    {
        return InventoryCategoryType::Component;
    }
}
