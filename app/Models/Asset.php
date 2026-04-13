<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'asset_tag',
        'name',
        'asset_model_id',
        'category_id',
        'status_label_id',
        'supplier_id',
        'location_id',
        'serial',
        'purchase_cost',
        'purchase_date',
        'warranty_expires',
        'eol_date',
        'notes',
        'requestable',
    ];

    protected function casts(): array
    {
        return [
            'purchase_cost' => 'decimal:2',
            'purchase_date' => 'date',
            'warranty_expires' => 'date',
            'eol_date' => 'date',
            'requestable' => 'boolean',
        ];
    }

    public function assetModel(): BelongsTo
    {
        return $this->belongsTo(AssetModel::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function statusLabel(): BelongsTo
    {
        return $this->belongsTo(StatusLabel::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(AssetCheckout::class);
    }

    public function activeCheckout(): HasOne
    {
        return $this->hasOne(AssetCheckout::class)
            ->whereNull('returned_at')
            ->latestOfMany('assigned_at');
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class)
            ->withPivot(['qty', 'installed_at'])
            ->withTimestamps();
    }
}
