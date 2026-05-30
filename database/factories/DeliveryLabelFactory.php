<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UploadStatus;
use App\Models\DeliveryLabel;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryLabel>
 */
class DeliveryLabelFactory extends Factory
{
    protected $model = DeliveryLabel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'recipient_name' => fake()->name(),
            'address_line_1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country_code' => 'TW',
            'status' => UploadStatus::Completed,
            'metadata' => [
                'remarks' => fake()->optional()->sentence(),
            ],
        ];
    }
}
