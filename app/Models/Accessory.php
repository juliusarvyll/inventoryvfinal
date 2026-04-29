<?php

namespace App\Models;

use App\Enums\InventoryCategoryType;
use App\Models\Concerns\BehavesAsTypedAsset;
use Database\Factories\AccessoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Accessory extends Asset
{
    /** @use HasFactory<AccessoryFactory> */
    use BehavesAsTypedAsset;

    use HasFactory;

    protected $table = 'assets';

    protected static function inventoryCategoryType(): InventoryCategoryType
    {
        return InventoryCategoryType::Accessory;
    }
}
