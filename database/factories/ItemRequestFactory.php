<?php

namespace Database\Factories;

use App\Enums\ItemRequestStatus;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemRequest>
 */
class ItemRequestFactory extends Factory
{
    public function definition(): array
    {
        $requesterName = fake()->name();

        return [
            'user_id' => User::factory(),
            'requester_name' => $requesterName,
            'requested_by' => $requesterName,
            'department' => fake()->randomElement(['ICT', 'Operations', 'Finance']),
            'requestable_type' => null,
            'requestable_id' => null,
            'status' => ItemRequestStatus::Pending,
            'qty' => 1,
            'items' => fake()->randomElement(['Bond Paper', 'Printer Ink', 'Office Chair']),
            'unit_cost' => fake()->randomFloat(2, 50, 5000),
            'remarks' => fake()->sentence(),
            'source_of_fund' => fake()->randomElement(['General Fund', 'ICT Budget', 'Project Budget']),
            'purpose_project' => fake()->sentence(),
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
            'items' => 'Asset request',
            'requestable_type' => Asset::class,
            'requestable_id' => Asset::factory(),
            'qty' => 1,
        ]);
    }

    public function forLicense(int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'items' => 'License request',
            'requestable_type' => License::class,
            'requestable_id' => License::factory(),
            'qty' => $quantity,
        ]);
    }

    public function forAccessory(int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'items' => 'Accessory request',
            'requestable_type' => Accessory::class,
            'requestable_id' => Accessory::factory(),
            'qty' => $quantity,
        ]);
    }

    public function forConsumable(int $quantity = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'items' => 'Consumable request',
            'requestable_type' => Consumable::class,
            'requestable_id' => Consumable::factory(),
            'qty' => $quantity,
        ]);
    }

    public function externalRequester(?string $name = null): static
    {
        $requesterName = $name ?? fake()->name();

        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'requester_name' => $requesterName,
            'requested_by' => $requesterName,
        ]);
    }
}
