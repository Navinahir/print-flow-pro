<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UploadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickingList extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'merchant_id',
        'upload_job_id',
        'source_name',
        'source_disk',
        'source_path',
        'status',
        'row_count',
        'output_disk',
        'output_path',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => UploadStatus::class,
            'metadata' => 'array',
            'row_count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Merchant, $this>
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * @return BelongsTo<UploadJob, $this>
     */
    public function uploadJob(): BelongsTo
    {
        return $this->belongsTo(UploadJob::class);
    }
}
