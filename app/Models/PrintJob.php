<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PrintJobStatus;
use App\Models\Concerns\BelongsToCountry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    use BelongsToCountry;
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'upload_job_id',
        'merchant_id',
        'pdf_upload_id',
        'country_code',
        'module',
        'status',
        'source_page_number',
        'output_disk',
        'output_path',
        'source_width_mm',
        'source_height_mm',
        'source_orientation',
        'output_width_mm',
        'output_height_mm',
        'checksum',
        'error_message',
        'downloaded_at',
        'shredded_at',
        'expires_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PrintJobStatus::class,
            'source_page_number' => 'integer',
            'source_width_mm' => 'float',
            'source_height_mm' => 'float',
            'output_width_mm' => 'float',
            'output_height_mm' => 'float',
            'metadata' => 'array',
            'downloaded_at' => 'datetime',
            'shredded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<UploadJob, $this>
     */
    public function uploadJob(): BelongsTo
    {
        return $this->belongsTo(UploadJob::class);
    }

    /**
     * @return BelongsTo<Merchant, $this>
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * @return BelongsTo<PdfUpload, $this>
     */
    public function pdfUpload(): BelongsTo
    {
        return $this->belongsTo(PdfUpload::class);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->merchant) {
            return $query->where('merchant_id', $user->merchant->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', PrintJobStatus::Ready);
    }
}
