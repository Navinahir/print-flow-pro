<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MerchantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Merchant extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'billing_plan_id',
        'name',
        'shop_name',
        'email',
        'phone',
        'status',
        'settings',
        'onboarded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MerchantStatus::class,
            'settings' => 'array',
            'onboarded_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<BillingPlan, $this>
     */
    public function billingPlan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class);
    }

    /**
     * @return HasMany<UploadJob, $this>
     */
    public function uploadJobs(): HasMany
    {
        return $this->hasMany(UploadJob::class);
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
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * @return MorphMany<AuditLog, $this>
     */
    public function auditableLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
