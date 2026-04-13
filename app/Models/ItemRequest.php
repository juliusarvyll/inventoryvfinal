<?php

namespace App\Models;

use App\Enums\ItemRequestStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ItemRequest extends Model
{
    /** @use HasFactory<\Database\Factories\ItemRequestFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'requester_name',
        'requestable_type',
        'requestable_id',
        'status',
        'qty',
        'reason',
        'deny_reason',
        'handled_by',
        'handled_at',
        'fulfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ItemRequestStatus::class,
            'qty' => 'integer',
            'handled_at' => 'datetime',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function requesterDisplayName(): Attribute
    {
        return Attribute::get(fn (): string => $this->requester_name ?: ($this->user?->name ?? '-'));
    }

    protected function requestableDisplayName(): Attribute
    {
        return Attribute::get(fn (): string => $this->requestable?->name ?? class_basename($this->requestable_type));
    }
}
