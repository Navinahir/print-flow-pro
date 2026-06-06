<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Domain\DomainSettingRepositoryInterface;
use App\Listeners\LogAuthenticationActivity;
use App\Models\DomainFeature;
use App\Models\DomainLocale;
use App\Models\DomainSetting;
use App\Models\UploadJob;
use App\Observers\UploadJobObserver;
use App\Repositories\Domain\DomainSettingRepository;
use App\Services\Domain\DomainConfigurationService;
use App\Support\Domains\DomainResolver;
use App\View\Components\Merchant\BrandMark;
use App\View\Components\Merchant\Form\FormError;
use App\View\Components\Merchant\Form\FormField;
use App\View\Components\Merchant\Form\FormLabel;
use App\View\Components\Merchant\LocaleSwitcher;
use App\View\Components\Merchant\PageLoader;
use App\View\Components\Merchant\Preview\PreviewAspectWarning;
use App\View\Components\Merchant\Preview\PreviewContainer;
use App\View\Components\Merchant\Preview\PreviewSafeZone;
use App\View\Components\Merchant\Preview\PreviewToolbar;
use App\View\Components\Merchant\Preview\PreviewWrapper;
use App\View\Components\Merchant\Preview\PrintButton;
use App\View\Components\Merchant\ThemeSwitch;
use App\View\Components\Merchant\UserAvatar;
use App\View\Components\Merchant\UserMenu;
use App\View\Composers\MarketingComposer;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DomainResolver::class);
        $this->app->singleton(DomainConfigurationService::class);
        $this->app->bind(DomainSettingRepositoryInterface::class, DomainSettingRepository::class);
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $isProduction = $this->app->isProduction();

        Model::shouldBeStrict(! $isProduction);
        Model::preventLazyLoading(! $isProduction);
        Model::preventSilentlyDiscardingAttributes(! $isProduction);

        UploadJob::observe(UploadJobObserver::class);

        $authListener = LogAuthenticationActivity::class;

        Event::listen(Login::class, [$authListener, 'handleLogin']);
        Event::listen(Logout::class, [$authListener, 'handleLogout']);
        Event::listen(Failed::class, [$authListener, 'handleFailed']);

        $this->registerDomainConfigurationCacheInvalidation();
        $this->registerMerchantPreviewComponents();
        $this->registerMerchantUiComponents();

        View::composer('marketing.*', MarketingComposer::class);
        View::composer('home', MarketingComposer::class);
    }

    private function registerMerchantUiComponents(): void
    {
        Blade::component('merchant.locale-switcher', LocaleSwitcher::class);
        Blade::component('merchant.theme-switch', ThemeSwitch::class);
        Blade::component('merchant.user-menu', UserMenu::class);
        Blade::component('merchant.brand-mark', BrandMark::class);
        Blade::component('merchant.user-avatar', UserAvatar::class);
        Blade::component('merchant.page-loader', PageLoader::class);
        Blade::component('merchant.form.label', FormLabel::class);
        Blade::component('merchant.form.error', FormError::class);
        Blade::component('merchant.form.field', FormField::class);
    }

    private function registerMerchantPreviewComponents(): void
    {
        Blade::component('merchant.preview.wrapper', PreviewWrapper::class);
        Blade::component('merchant.preview.toolbar', PreviewToolbar::class);
        Blade::component('merchant.preview.container', PreviewContainer::class);
        Blade::component('merchant.preview.safe-zone', PreviewSafeZone::class);
        Blade::component('merchant.preview.aspect-warning', PreviewAspectWarning::class);
        Blade::component('merchant.preview.print-button', PrintButton::class);
    }

    private function registerDomainConfigurationCacheInvalidation(): void
    {
        $forget = function (): void {
            $this->app->make(DomainConfigurationService::class)->forgetCache();
        };

        DomainSetting::saved($forget);
        DomainSetting::deleted($forget);
        DomainLocale::saved($forget);
        DomainLocale::deleted($forget);
        DomainFeature::saved($forget);
        DomainFeature::deleted($forget);
    }
}
