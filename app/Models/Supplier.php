<?php

namespace App\Models;

use App\Enums\InventoryCategoryType;
use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'phone',
        'email',
        'url',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(Asset::class)->whereHas('category', fn ($query) => $query->where('type', InventoryCategoryType::Accessory));
    }

    public function consumables(): HasMany
    {
        return $this->hasMany(Asset::class)->whereHas('category', fn ($query) => $query->where('type', InventoryCategoryType::Consumable));
    }

    public function components(): HasMany
    {
        return $this->hasMany(Asset::class)->whereHas('category', fn ($query) => $query->where('type', InventoryCategoryType::Component));
    }
}
