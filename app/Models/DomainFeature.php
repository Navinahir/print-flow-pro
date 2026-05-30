<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $domain_setting_id
 * @property string $feature_key
 * @property bool $is_enabled
 * @property array<string, mixed>|null $config
 * @property-read DomainSetting $domainSetting
 */
class DomainFeature extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'domain_setting_id',
        'feature_key',
        'is_enabled',
        'config',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'config' => 'array',
        ];
    }

    /**
     * @return BelongsTo<DomainSetting, $this>
     */
    public function domainSetting(): BelongsTo
    {
        return $this->belongsTo(DomainSetting::class);
    }
}
