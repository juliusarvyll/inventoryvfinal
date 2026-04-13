<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumableAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\ConsumableAssignmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'consumable_id',
        'assigned_to',
        'qty',
        'assigned_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'assigned_at' => 'datetime',
        ];
    }

    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
