<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Component extends Model
{
    /** @use HasFactory<\Database\Factories\ComponentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'category_id',
        'supplier_id',
        'location_id',
        'qty',
        'min_qty',
        'serial',
        'purchase_cost',
        'purchase_date',
        'order_number',
        'requestable',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'min_qty' => 'integer',
            'purchase_cost' => 'decimal:2',
            'purchase_date' => 'date',
            'requestable' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class)
            ->withPivot(['qty', 'installed_at'])
            ->withTimestamps();
    }

    public function installedQuantity(): int
    {
        return (int) $this->assets()->sum('asset_component.qty');
    }

    public function qtyRemaining(): int
    {
        return max(0, $this->qty - $this->installedQuantity());
    }

    public function isLowStock(): bool
    {
        return $this->qtyRemaining() <= $this->min_qty;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw(
            'qty - (select coalesce(sum(qty), 0) from asset_component where asset_component.component_id = components.id) <= min_qty'
        );
    }
}
