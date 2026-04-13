<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accessory extends Model
{
    /** @use HasFactory<\Database\Factories\AccessoryFactory> */
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
        'model_number',
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

    public function checkouts(): HasMany
    {
        return $this->hasMany(AccessoryCheckout::class);
    }

    public function checkedOutQuantity(): int
    {
        return (int) $this->checkouts()->whereNull('returned_at')->sum('qty');
    }

    public function qtyRemaining(): int
    {
        return max(0, $this->qty - $this->checkedOutQuantity());
    }

    public function isLowStock(): bool
    {
        return $this->qtyRemaining() <= $this->min_qty;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw(
            'qty - (select coalesce(sum(qty), 0) from accessory_checkouts where accessory_checkouts.accessory_id = accessories.id and returned_at is null) <= min_qty'
        );
    }
}
