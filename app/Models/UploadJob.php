<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UploadJobType;
use App\Enums\UploadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UploadJob extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'merchant_id',
        'user_id',
        'uploaded_by',
        'type',
        'status',
        'file_count',
        'error_message',
        'metadata',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => UploadJobType::class,
            'status' => UploadStatus::class,
            'metadata' => 'array',
            'file_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return HasMany<PdfUpload, $this>
     */
    public function pdfUploads(): HasMany
    {
        return $this->hasMany(PdfUpload::class);
    }

    /**
     * @return HasMany<PickingList, $this>
     */
    public function pickingLists(): HasMany
    {
        return $this->hasMany(PickingList::class);
    }

    /**
     * @return HasMany<DeliveryLabel, $this>
     */
    public function deliveryLabels(): HasMany
    {
        return $this->hasMany(DeliveryLabel::class);
    }

    /**
     * @return MorphMany<AuditLog, $this>
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
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
}
