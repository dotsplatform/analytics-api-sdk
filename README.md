# dotsplatform/analytics-api-sdk

PHP SDK for the Dots **analytics** service Read API v1 (`GET api/v1/accounts/{account}/...`). The Read API answers
from the daily MySQL rollups of the analytics service: overview, active users, events, platforms, items, pages and
hourly visits.

## Install

```bash
composer require dotsplatform/analytics-api-sdk
php artisan vendor:publish --tag=analytics-api-sdk-config
```

`config/analytics-api.php`:

| Key | Env | Meaning |
|---|---|---|
| `host` | `ANALYTICS_API_URL` | Base URL of the analytics service, e.g. `https://e.dots.live`. The SDK adds `api/v1/...`; a path prefix in the host is kept |
| `token` | `ANALYTICS_API_GATEWAY_TOKEN` | The internal gateway token, sent as `X-Internal-Gateway-Token` |
| `stub` | `ANALYTICS_API_STUB` | Empty: the HTTP client. `success`: sample data, no HTTP. `fail`: every call throws `AnalyticsServerException` (503) |
| `connect_timeout` | – | Seconds to connect, 2 by default |
| `timeout` | – | Seconds for a whole request, 8 by default |

Every request sends `X-Internal-Gateway-Token: <token>`. The analytics service answers **401** without it, and also
when its own token is not configured.

## Usage

```php
use Dotsplatform\AnalyticsApi\AnalyticsClient;
use Dotsplatform\AnalyticsApi\DTO\Requests\StatsRequestDTO;

$analyticsClient = app(AnalyticsClient::class);

$overview = $analyticsClient->stats()->getOverview(StatsRequestDTO::forAccount($accountId, '2026-11-01', '2026-11-07'));
$overview->getTotals()->getPurchases();
```

### The request

Every method takes one `StatsRequestDTO`:

| Field | Query | Meaning |
|---|---|---|
| `accountId` | path `{account}` | An account id, or `null` for all accounts (the path segment `all`; includes events with no resolved account) |
| `dateFrom`, `dateTo` | `date_from`, `date_to` | `Y-m-d`, both days included, at most 366 days |
| `platform` | `platform` | `Platform` enum: `WEB`, `IOS`, `ANDROID`, `SERVER`, `UNKNOWN` |
| `limit` | `limit` | Size of the top lists (events, platforms, items, pages), 1–500 |
| `dimension` | `dimension` | `BreakdownDimension` enum, sent **only** by `getPlatformBreakdown()`: `PLATFORM`, `OS`, `OS_VERSION`, `BROWSER`, `BROWSER_VERSION`, `APP_VERSION` |

A field left `null` is not sent, so the service default applies: the last 30 days in Kyiv time, every platform,
limit 50 and dimension `os`. The response `getFilters()` shows the account, period and platform the service used.

```php
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Enums\Platform;

$request = StatsRequestDTO::forAllAccounts('2026-10-01', '2026-10-31')->copy([
    'platform' => Platform::IOS,
    'limit' => 10,
    'dimension' => BreakdownDimension::APP_VERSION,
]);
// or StatsRequestDTO::fromArray(['accountId' => 42, 'dateFrom' => ..., 'platform' => Platform::WEB, ...])
```

### Methods

All are on `$analyticsClient->stats()`:

| Method | Endpoint | Returns |
|---|---|---|
| `getOverview($request)` | `overview` | `AccountOverviewDTO`: `getTotals()` (`CountersDTO`), `getPlatforms()` (list of `PlatformTotalsDTO`: platform + counters), `getDaily()` (list of `DailyTotalsDTO`: date + counters) |
| `getActiveUsers($request)` | `active-users` | `ActiveUsersDTO`: `getDaily()`, a list of `ActiveUsersDayDTO` (`getDate()`, `getDau()`, `getWau()`, `getMau()`) |
| `getEventStats($request)` | `events` | `EventStatsDTO`: `getEvents()`, a list of `EventStatDTO` (`getEvent()`, `getEvents()`, `getDeviceDays()`), most frequent first |
| `getPlatformBreakdown($request)` | `platforms` | `PlatformBreakdownDTO`: `getDimension()` (`?BreakdownDimension`), `getDimensionValue()` (raw string), `getRows()`, a list of `PlatformBreakdownRowDTO` (`getValue()`, `getEvents()`, `getDeviceDays()`), most device-days first |
| `getTopItems($request)` | `items` | `TopItemsDTO`: `getItems()`, a list of `TopItemDTO` (`getItemId()`, `getViews()`, `getAddToCart()`, `getDeviceDays()`, `getAddToCartRate()`), most viewed first |
| `getTopPages($request)` | `pages` | `TopPagesDTO`: `getPages()`, a list of `TopPageDTO` (`getPage()`, `getViews()`, `getDeviceDays()`), most viewed first |
| `getHourlyVisits($request)` | `hourly-visits` | `HourlyVisitsDTO`: `getHours()` (list of `HourVisitsDTO`: `getHour()` 0–23), `getWeekdays()` (list of `WeekdayVisitsDTO`: `getWeekday()` 1 = Monday … 7 = Sunday) |

Every response DTO also has `getFilters()` (`StatsFiltersDTO`: `getAccountId()`, `getDateFrom()`, `getDateTo()`,
`getPlatform()`).

`CountersDTO` has `getEvents()`, `getSessions()`, `getPageViews()`, `getItemViews()`, `getAddToCart()`,
`getCheckouts()`, `getPurchases()` and `getDeviceDays()`.

### Reading the numbers

- **Device-days** (`getDeviceDays()`) sum daily unique devices: a device active on 3 days counts 3. Compare it
  between periods; do not show it as a user count. In hourly visits it sums hourly devices, so a device active in two
  hours of a day counts twice.
- **Active users for all accounts** are per-account numbers summed: a device used in two accounts counts twice.
- **Sparse series**: overview `getDaily()`, active users `getDaily()` and `getHours()` leave out days and hours without
  data. Fill the gaps yourself for a chart. `getWeekdays()` always has 7 rows.
- **Breakdown values**: a device that did not report the dimension (no OS version, no app version) comes as `""`.
- **Add-to-cart rate** is `add_to_cart / views` rounded to 4 decimals, `0.0` when there were no views.
- Platforms and dimensions in responses are kept as **raw strings**, so a value this SDK version does not know never
  breaks a response (`PlatformBreakdownDTO::getDimension()` returns null for it). Requests take enums only.

## Errors

Every call may throw a subclass of `Dotsplatform\AnalyticsApi\Exceptions\AnalyticsApiException`:

| Exception | When |
|---|---|
| `AnalyticsValidationException` | 422: `getErrors()` / `getErrorsFor('date_to')`, Laravel style (bad date format, `date_to` before `date_from`, range over 366 days, unknown platform, limit out of 1–500) |
| `AnalyticsUnauthorizedException` | 401: wrong or missing token |
| `AnalyticsNotFoundException` | 404: the path does not exist |
| `AnalyticsRateLimitException` | 429: `getRetryAfterSeconds()` |
| `AnalyticsServerException` | any other error status |
| `AnalyticsConnectionException` | no response (DNS, timeout, refused) |
| `AnalyticsUnexpectedResponseException` | the body does not have the documented shape |
| `AnalyticsConfigurationException` | `analytics-api.host` or `analytics-api.token` is empty |

## Tests of consumers

Set `ANALYTICS_API_STUB=success` (or `fail`), or bind a stub in a test:

```php
use Dotsplatform\AnalyticsApi\AnalyticsClient;
use Dotsplatform\AnalyticsApi\Clients\FailAnalyticsStubClient;
use Dotsplatform\AnalyticsApi\Clients\SuccessAnalyticsStubClient;

$this->app->bind(AnalyticsClient::class, SuccessAnalyticsStubClient::class); // sample data, no HTTP
$this->app->bind(AnalyticsClient::class, FailAnalyticsStubClient::class);    // every call throws AnalyticsServerException (503)
```

The sample data is in `Clients\Stubs\StubResponses`. Its `filters` echo the request.

## Development

```bash
composer install
composer init-pre-commit   # fixer + phpstan + phpunit before each commit
composer test              # PHPUnit, HTTP mocked with Guzzle MockHandler
composer analyse           # PHPStan, level max
composer fix               # PHP CS Fixer (Laravel Shift style + strict rules)
```
