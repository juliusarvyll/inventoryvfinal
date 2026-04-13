<?php

namespace Database\Factories;

use App\Enums\ItemRequestStatus;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemRequest>
 */
class ItemRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'requester_name' => fake()->name(),
            'requestable_type' => Asset::class,
            'requestable_id' => Asset::factory(),
            'status' => ItemRequestStatus::Pending,
            'qty' => 1,
            'reason' => fake()->sentence(),
            'deny_reason' => null,
            'handled_by' => null,
            'handled_at' => null,
            'fulfilled_at' => null,
        ];
    }

    public function forAsset(): static
    {
        return $this->state(fn (array $attributes) => [
            'requestable_type' => Asset::class,
            'requestable_id' => Asset::factory(),
            'qty' => 1,
        ]);
    }

    public function forLicense(int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'requestable_type' => License::class,
            'requestable_id' => License::factory(),
            'qty' => $quantity,
        ]);
    }

    public function forAccessory(int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'requestable_type' => Accessory::class,
            'requestable_id' => Accessory::factory(),
            'qty' => $quantity,
        ]);
    }

    public function forConsumable(int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'requestable_type' => Consumable::class,
            'requestable_id' => Consumable::factory(),
            'qty' => $quantity,
        ]);
    }

    public function externalRequester(?string $name = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'requester_name' => $name ?? fake()->name(),
        ]);
    }
}
