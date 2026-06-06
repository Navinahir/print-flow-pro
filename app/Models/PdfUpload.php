<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UploadStatus;
use App\Models\Concerns\BelongsToCountry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfUpload extends Model
{
    use BelongsToCountry;
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'merchant_id',
        'country_code',
        'upload_job_id',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
        'status',
        'checksum',
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
            'size_bytes' => 'integer',
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
