<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Domain\DomainConfigurationService;
use App\Support\Domains\DomainResolver;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class DomainValidateCommand extends Command
{
    protected $signature = 'domain:validate {--fix-hosts : Update domain_settings.host from config/domains.php fallback_merchants}';

    protected $description = 'Validate multi-domain configuration for the current environment';

    public function handle(DomainConfigurationService $domainConfiguration, DomainResolver $resolver): int
    {
        $issues = 0;

        $this->info('Multi-domain configuration check');
        $this->newLine();

        $this->line('APP_ENV: '.config('app.env'));
        $this->line('DOMAIN_ROUTING_ENABLED: '.(config('domains.routing_enabled') ? 'true' : 'false'));
        $this->line('DOMAIN_PORT_ROUTING: '.(config('domains.port_routing') ? 'true' : 'false'));
        $domainConfiguration->syncInfrastructureToConfig();

        $infrastructure = $domainConfiguration->infrastructureForDisplay();

        $this->line('Marketing host (domain_settings / fallback): '.($infrastructure['marketing']['host'] ?? '—'));
        $this->line('Admin host (domain_settings / fallback): '.($infrastructure['admin']['host'] ?? '—'));
        $this->line('Admin path prefix: '.$domainConfiguration->adminPathPrefix());
        $this->newLine();

        if (! config('domains.routing_enabled', true)) {
            $this->error('DOMAIN_ROUTING_ENABLED is false — marketing routes are exposed on every host.');
            $issues++;
        }

        if (app()->environment('production', 'staging')) {
            foreach (['marketing' => $infrastructure['marketing']['host'] ?? '', 'admin' => $infrastructure['admin']['host'] ?? ''] as $label => $host) {
                $host = (string) $host;
                if ($this->isLoopbackHost($host)) {
                    $this->error("{$label} host is \"{$host}\" — update domain_settings or run domain:validate --fix-hosts.");
                    $issues++;
                }
            }

            if (config('domains.port_routing', false)) {
                $this->error('DOMAIN_PORT_ROUTING must be false in production.');
                $issues++;
            }
        }

        if (app()->environment('local') && ! config('domains.port_routing', false)) {
            $this->warn('DOMAIN_PORT_ROUTING is false — enable it for artisan serve on ports 8000/8001/8002.');
        }

        $this->newLine();
        $this->info('Merchant hosts (domain_settings):');

        $regions = $domainConfiguration->merchantRegionsForRouting();
        $fallback = config('domains.fallback_merchants', []);

        foreach ($regions as $regionKey => $region) {
            $host = (string) ($region['domain'] ?? '');
            $active = (bool) ($region['active'] ?? false);
            $expected = (string) ($fallback[$regionKey]['domain'] ?? '');

            $status = $active ? 'active' : 'inactive';
            $this->line("  [{$regionKey}] {$host} ({$status})");

            if ($expected !== '' && $host !== $expected && app()->environment('production', 'staging')) {
                $this->error("    Expected \"{$expected}\" for APP_ENV=".config('app.env').' — run domain:validate --fix-hosts');
                $issues++;
            }
        }

        if ($this->option('fix-hosts')) {
            $this->call('db:seed', ['--class' => 'DomainSettingSeeder', '--force' => true]);
            $domainConfiguration->forgetCache();
            $this->info('Re-seeded domain_settings (marketing, admin, merchant regions) from config/domains.php.');
            $domainConfiguration->syncInfrastructureToConfig();
        }

        $this->newLine();
        $this->info('Resolved surfaces (sample hosts):');

        foreach ([
            'tw.xycubic.com' => 'merchant',
            'manage-xy.xycubic.com' => 'admin',
            'xycubic.com' => 'marketing',
        ] as $sampleHost => $expectedSurface) {
            if (! app()->environment('production', 'staging')) {
                continue;
            }

            $context = $resolver->resolve(Request::create("https://{$sampleHost}/", 'GET'));
            $ok = $context->surface === $expectedSurface;
            $line = "  {$sampleHost} → {$context->surface}";

            if ($ok) {
                $this->line($line);
            } else {
                $this->error("{$line} (expected {$expectedSurface})");
                $issues++;
            }
        }

        $this->newLine();

        if ($issues === 0) {
            $this->info('No domain configuration issues detected.');

            return self::SUCCESS;
        }

        $this->error("Found {$issues} issue(s). Update domain_settings (or .env fallbacks) and run:");
        $this->line('  php artisan config:clear');
        $this->line('  php artisan domain:validate --fix-hosts');

        return self::FAILURE;
    }

    private function isLoopbackHost(string $host): bool
    {
        $host = strtolower(trim($host));
        $name = str_contains($host, ':') ? explode(':', $host, 2)[0] : $host;

        return in_array($name, ['localhost', '127.0.0.1'], true);
    }
}
