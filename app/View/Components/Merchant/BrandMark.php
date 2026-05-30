<?php

declare(strict_types=1);

namespace App\View\Components\Merchant;

use App\Support\BrandMark as BrandMarkHelper;
use App\Support\MerchantConfig;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BrandMark extends Component
{
    public string $brandName;

    public string $initials;

    public ?string $logoUrl;

    public function __construct(
        public string $size = 'sm',
    ) {
        $this->brandName = (string) MerchantConfig::get('brand.name', __('merchant.brand.name'));
        $this->initials = BrandMarkHelper::initials($this->brandName);
        $this->logoUrl = BrandMarkHelper::logoUrl();
    }

    public function render(): View
    {
        return view('merchant.components.brand-mark');
    }
}
