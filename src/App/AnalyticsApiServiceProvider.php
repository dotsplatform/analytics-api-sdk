<?php

declare(strict_types=1);

/**
 * Description of AnalyticsApiServiceProvider.php
 *
 * Binds AnalyticsClient and merges / publishes config/analytics-api.php
 * (php artisan vendor:publish --tag=analytics-api-sdk-config). Config analytics-api.stub picks the client:
 * "" = the HTTP client, "success" = SuccessAnalyticsStubClient, "fail" = FailAnalyticsStubClient.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi;

use Dotsplatform\AnalyticsApi\Clients\AnalyticsHttpClient;
use Dotsplatform\AnalyticsApi\Clients\FailAnalyticsStubClient;
use Dotsplatform\AnalyticsApi\Clients\SuccessAnalyticsStubClient;
use Dotsplatform\AnalyticsApi\Http\AnalyticsHttpTimeouts;
use Dotsplatform\AnalyticsApi\Http\AnalyticsHttpTransport;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;

class AnalyticsApiServiceProvider extends ServiceProvider
{
    public const string CONFIG_KEY = 'analytics-api';

    public const string STUB_SUCCESS = 'success';

    public const string STUB_FAIL = 'fail';

    private const string CONFIG_FILE = __DIR__.'/../config/analytics-api.php';

    protected function getConfigRepository(): ConfigRepository
    {
        return $this->app->make(ConfigRepository::class);
    }

    protected function getHttpClient(): AnalyticsHttpClient
    {
        return $this->app->make(AnalyticsHttpClient::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_FILE, self::CONFIG_KEY);

        $this->app->singleton(AnalyticsHttpTransport::class, fn (): AnalyticsHttpTransport => $this->makeTransport());
        $this->app->bind(AnalyticsClient::class, fn (): AnalyticsClient => $this->makeClient());
    }

    public function boot(): void
    {
        $this->publishes([
            self::CONFIG_FILE => $this->app->configPath('analytics-api.php'),
        ], 'analytics-api-sdk-config');
    }

    private function makeClient(): AnalyticsClient
    {
        $stub = $this->readString($this->getConfigRepository(), 'stub');

        if ($stub === self::STUB_SUCCESS) {
            return new SuccessAnalyticsStubClient();
        }

        if ($stub === self::STUB_FAIL) {
            return new FailAnalyticsStubClient();
        }

        return $this->getHttpClient();
    }

    private function makeTransport(): AnalyticsHttpTransport
    {
        $config = $this->getConfigRepository();

        return new AnalyticsHttpTransport(
            $this->readString($config, 'host'),
            $this->readString($config, 'token'),
            $this->makeTimeouts($config),
        );
    }

    private function makeTimeouts(ConfigRepository $config): AnalyticsHttpTimeouts
    {
        return new AnalyticsHttpTimeouts(
            $this->readSeconds($config, 'connect_timeout', AnalyticsHttpTimeouts::DEFAULT_CONNECT_SECONDS),
            $this->readSeconds($config, 'timeout', AnalyticsHttpTimeouts::DEFAULT_REQUEST_SECONDS),
        );
    }

    private function readString(ConfigRepository $config, string $key): string
    {
        $value = $config->get(self::CONFIG_KEY.'.'.$key);
        if (! is_string($value)) {
            return '';
        }

        return $value;
    }

    private function readSeconds(ConfigRepository $config, string $key, float $defaultSeconds): float
    {
        $value = $config->get(self::CONFIG_KEY.'.'.$key);
        if (! is_numeric($value)) {
            return $defaultSeconds;
        }

        return (float) $value;
    }
}
