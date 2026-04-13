<?php

namespace App\Enums;

enum InventoryCategoryType: string
{
    case Asset = 'asset';
    case License = 'license';
    case Accessory = 'accessory';
    case Consumable = 'consumable';
    case Component = 'component';
}
