<?php

declare(strict_types=1);

/**
 * Description of AnalyticsApiServiceProviderTest.php
 *
 * The service provider in a Laravel app (Testbench): config merged under analytics-api, AnalyticsClient bound to the
 * HTTP client or to a stub (analytics-api.stub), the transport built from the config, and the config file published.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Tests\Provider;

use Dotsplatform\AnalyticsApi\AnalyticsApiServiceProvider;
use Dotsplatform\AnalyticsApi\AnalyticsClient;
use Dotsplatform\AnalyticsApi\Clients\AnalyticsHttpClient;
use Dotsplatform\AnalyticsApi\Clients\FailAnalyticsStubClient;
use Dotsplatform\AnalyticsApi\Clients\SuccessAnalyticsStubClient;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;
use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsConfigurationException;
use Dotsplatform\AnalyticsApi\Http\AnalyticsHttpTransport;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase;

class AnalyticsApiServiceProviderTest extends TestCase
{
    public function testTheConfigIsMergedUnderAnalyticsApi(): void
    {
        $this->assertSame('https://analytics.example.test', config('analytics-api.host'));
        $this->assertSame('internal-gateway-secret', config('analytics-api.token'));
        $this->assertSame('', config('analytics-api.stub'));
        $this->assertSame(2, config('analytics-api.connect_timeout'));
        $this->assertSame(8, config('analytics-api.timeout'));
    }

    public function testTheClientIsTheHttpClientByDefault(): void
    {
        $this->assertInstanceOf(AnalyticsHttpClient::class, $this->app?->make(AnalyticsClient::class));
    }

    public function testTheSuccessStubIsPickedByConfig(): void
    {
        config(['analytics-api.stub' => AnalyticsApiServiceProvider::STUB_SUCCESS]);

        $this->assertInstanceOf(SuccessAnalyticsStubClient::class, $this->app?->make(AnalyticsClient::class));
    }

    public function testTheFailStubIsPickedByConfig(): void
    {
        config(['analytics-api.stub' => AnalyticsApiServiceProvider::STUB_FAIL]);

        $this->assertInstanceOf(FailAnalyticsStubClient::class, $this->app?->make(AnalyticsClient::class));
    }

    public function testAnUnknownStubValueUsesTheHttpClient(): void
    {
        config(['analytics-api.stub' => 'yes']);

        $this->assertInstanceOf(AnalyticsHttpClient::class, $this->app?->make(AnalyticsClient::class));
    }

    public function testAnEmptyTokenFailsLoudly(): void
    {
        config(['analytics-api.token' => '']);
        $this->app?->forgetInstance(AnalyticsHttpTransport::class);

        $this->expectException(AnalyticsConfigurationException::class);

        $analyticsClient = $this->app?->make(AnalyticsClient::class);
        $this->assertInstanceOf(AnalyticsClient::class, $analyticsClient);
        $analyticsClient->stats()->getOverview(StatsRequestDTO::forAllAccounts());
    }

    public function testTheConfigFileIsPublished(): void
    {
        $publishedPaths = ServiceProvider::pathsToPublish(AnalyticsApiServiceProvider::class, 'analytics-api-sdk-config');

        $this->assertCount(1, $publishedPaths);
        $publishedPath = array_values($publishedPaths)[0];
        $this->assertIsString($publishedPath);
        $this->assertStringEndsWith('config/analytics-api.php', $publishedPath);
    }

    /**
     * @param  Application  $app
     * @return list<class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [AnalyticsApiServiceProvider::class];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $config = $app->make(ConfigRepository::class);
        $config->set('analytics-api.host', 'https://analytics.example.test');
        $config->set('analytics-api.token', 'internal-gateway-secret');
    }
}
