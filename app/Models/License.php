<?php

namespace App\Models;

use Database\Factories\LicenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

class License extends Model
{
    /** @use HasFactory<LicenseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'product_key',
        'category_id',
        'manufacturer_id',
        'license_type',
        'seats',
        'expiration_date',
        'purchase_date',
        'purchase_cost',
        'order_number',
        'maintained',
        'requestable',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'expiration_date' => 'date',
            'purchase_date' => 'date',
            'purchase_cost' => 'decimal:2',
            'maintained' => 'boolean',
            'requestable' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function licenseSeats(): HasMany
    {
        return $this->hasMany(LicenseSeat::class);
    }

    public function assignedSeatsCount(): int
    {
        $loadedCount = $this->getAttribute('license_seats_count');

        if ($loadedCount !== null) {
            return (int) $loadedCount;
        }

        return $this->licenseSeats()->count();
    }

    public function seatsAvailable(): int
    {
        return max(0, $this->seats - $this->assignedSeatsCount());
    }

    public function assertSeatsAvailable(int $quantity = 1): void
    {
        if ($this->seatsAvailable() < $quantity) {
            throw new RuntimeException('No license seats are available for this assignment.');
        }
    }
}
