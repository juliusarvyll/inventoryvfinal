<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessoryCheckout extends Model
{
    /** @use HasFactory<\Database\Factories\AccessoryCheckoutFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'accessory_id',
        'assigned_to',
        'qty',
        'assigned_at',
        'returned_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'assigned_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function accessory(): BelongsTo
    {
        return $this->belongsTo(Accessory::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
