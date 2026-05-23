<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\UploadJob;
use App\Observers\UploadJobObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
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
    }
}
