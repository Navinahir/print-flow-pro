<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $domain_setting_id
 * @property string $locale
 * @property string $label
 * @property bool $is_default
 * @property-read DomainSetting $domainSetting
 */
class DomainLocale extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'domain_setting_id',
        'locale',
        'label',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
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
