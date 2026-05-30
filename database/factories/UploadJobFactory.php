<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use App\Models\UploadJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UploadJob>
 */
class UploadJobFactory extends Factory
{
    protected $model = UploadJob::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'merchant_id' => null,
            'user_id' => User::factory(),
            'uploaded_by' => User::factory(),
            'type' => UploadJobType::OrderPdf,
            'status' => UploadStatus::Pending,
            'file_count' => 1,
            'metadata' => [],
        ];
    }
}
