<?php

namespace App\Models;

use App\Enums\InventoryCategoryType;
use App\Models\Concerns\BehavesAsTypedAsset;
use Database\Factories\ConsumableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Consumable extends Asset
{
    /** @use HasFactory<ConsumableFactory> */
    use BehavesAsTypedAsset;

    use HasFactory;

    protected $table = 'assets';

    protected static function inventoryCategoryType(): InventoryCategoryType
    {
        return InventoryCategoryType::Consumable;
    }
}
