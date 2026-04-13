<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends Model
{
    /** @use HasFactory<\Database\Factories\ManufacturerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'url',
        'support_url',
        'support_phone',
        'image',
    ];

    public function assetModels(): HasMany
    {
        return $this->hasMany(AssetModel::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
