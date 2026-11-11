# dotsplatform/analytics-api-sdk

PHP SDK for the Dots **analytics** service Read API v1 (`GET api/v1/accounts/{account}/...`). The Read API answers
from the daily MySQL rollups of the analytics service: overview, active users, events, platforms, items, pages,
hourly visits, the order funnel, the activity heatmap, traffic sources and retention cohorts.

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

// livesite: $account->token (accounts.token, a uuid)
$overview = $analyticsClient->stats()->getOverview(StatsRequestDTO::forAccount($account->token, '2026-11-01', '2026-11-07'));
$overview->getTotals()->getPurchases();
```

**v2.0** addresses accounts by their livesite token instead of the numeric id (the service no longer accepts ids),
adds `getAccounts()` and `getTopCategories()`, and item names and categories. Upgrading from 1.x: pass
`$account->token` to `forAccount()` and read `getFilters()->getAccountToken()` instead of `getAccountId()`.

**v2.4** adds `getPushStats()`: push notifications sent, failed, delivered and opened, per kind (campaign / order)
and day, with the total of the period before.

**v2.3** adds `getInstalls()`: app installs and updates ("Application Installed" / "Application Updated") per
platform and day, with the totals of the period before.

**v2.2** adds `getDevices()`: the top device models (iPhone identifiers with a readable label), OS versions, browsers
and app versions of the period.

**v2.1** adds `getFunnel()`, `getHeatmap()`, `getSources()` and `getRetention()` with the `FunnelChannel`,
`HeatmapMetric`, `RetentionView` and `SourceKind` enums. Existing calls are unchanged; only a class of your own that
implements `StatsClient` needs the four new methods.

### The request

Every method takes one `StatsRequestDTO`:

| Field | Query | Meaning |
|---|---|---|
| `accountToken` | path `{account}` | The livesite account token, a uuid (anything else throws `InvalidArgumentException`; a uuid the service does not know throws `AnalyticsNotFoundException`), or `null` for all accounts (the path segment `all`; includes events with no resolved account) |
| `dateFrom`, `dateTo` | `date_from`, `date_to` | `Y-m-d`, both days included, at most 366 days |
| `platform` | `platform` | `Platform` enum: `WEB`, `IOS`, `ANDROID`, `SERVER`, `UNKNOWN` |
| `limit` | `limit` | Size of the top lists (events, platforms, items, pages), 1–500 |
| `dimension` | `dimension` | `BreakdownDimension` enum, sent **only** by `getPlatformBreakdown()`: `PLATFORM`, `OS`, `OS_VERSION`, `BROWSER`, `BROWSER_VERSION`, `APP_VERSION` |

The funnel channel, heatmap metric and retention view are not request fields but a second argument of their
method (`getFunnel($request, FunnelChannel::WEB)`), always sent; its default is the service default.

A field left `null` is not sent, so the service default applies: the last 30 days in Kyiv time, every platform,
limit 50 and dimension `os`. The response `getFilters()` shows the account, period, platform and limit the service used.

```php
use Dotsplatform\AnalyticsApi\Enums\BreakdownDimension;
use Dotsplatform\AnalyticsApi\Enums\Platform;

$request = StatsRequestDTO::forAllAccounts('2026-10-01', '2026-10-31')->copy([
    'platform' => Platform::IOS,
    'limit' => 10,
    'dimension' => BreakdownDimension::APP_VERSION,
]);
// or StatsRequestDTO::fromArray(['accountToken' => $account->token, 'dateFrom' => ..., 'platform' => Platform::WEB, ...])
```

### Methods

All are on `$analyticsClient->stats()`:

| Method | Endpoint | Returns |
|---|---|---|
| `getAccounts()` | `GET api/v1/accounts` | `AccountListDTO`: `getAccounts()`, a list of `AccountDTO` (`getToken()`, `getName()`, `getDomain()`, `getLogoUrl()`), by name |
| `getOverview($request)` | `overview` | `AccountOverviewDTO`: `getTotals()` (`CountersDTO`), `getPlatforms()` (list of `PlatformTotalsDTO`: platform + counters), `getDaily()` (list of `DailyTotalsDTO`: date + counters) |
| `getActiveUsers($request)` | `active-users` | `ActiveUsersDTO`: `getDaily()`, a list of `ActiveUsersDayDTO` (`getDate()`, `getDau()`, `getWau()`, `getMau()`) |
| `getEventStats($request)` | `events` | `EventStatsDTO`: `getEvents()`, a list of `EventStatDTO` (`getEvent()`, `getEvents()`, `getDeviceDays()`), most frequent first |
| `getPlatformBreakdown($request)` | `platforms` | `PlatformBreakdownDTO`: `getDimension()` (`?BreakdownDimension`), `getDimensionValue()` (raw string), `getRows()`, a list of `PlatformBreakdownRowDTO` (`getValue()`, `getEvents()`, `getDeviceDays()`), most device-days first |
| `getTopItems($request)` | `items` | `TopItemsDTO`: `getItems()`, a list of `TopItemDTO` (`getItemId()` = the livesite dish token, `getName()`, `getCategory()`, `getViews()`, `getAddToCart()`, `getDeviceDays()`, `getAddToCartRate()`), most viewed first |
| `getTopCategories($request)` | `categories` | `TopCategoriesDTO`: `getCategories()`, a list of `TopCategoryDTO` (`getCategory()`, `getCategoryToken()`, `getItems()`, `getViews()`, `getAddToCart()`, `getDeviceDays()`, `getAddToCartRate()`), most viewed first |
| `getTopPages($request)` | `pages` | `TopPagesDTO`: `getPages()`, a list of `TopPageDTO` (`getPage()`, `getViews()`, `getDeviceDays()`), most viewed first |
| `getHourlyVisits($request)` | `hourly-visits` | `HourlyVisitsDTO`: `getHours()` (24 `HourVisitsDTO`: `getHour()` 0–23, `getEvents()`, `getDeviceHours()`), `getWeekdays()` (7 `WeekdayVisitsDTO`: `getWeekday()` 1 = Monday … 7 = Sunday, `getEvents()`, `getDeviceHours()`) |

| `getFunnel($request, $channel)` | `funnel?channel=` | `FunnelDTO`, see [Funnel](#funnel) |
| `getHeatmap($request, $metric)` | `heatmap?metric=` | `HeatmapDTO`, see [Heatmap](#heatmap) |
| `getSources($request)` | `sources` | `SourcesDTO`, see [Traffic sources](#traffic-sources) |
| `getRetention($request, $view)` | `retention?view=` | `RetentionDTO`, see [Retention](#retention) |

Every response DTO except `RetentionDTO` also has `getFilters()` (`StatsFiltersDTO`: `getAccountToken()`, `getDateFrom()`, `getDateTo()`,
`getPlatform()`, `getLimit()`).

`CountersDTO` has `getEvents()`, `getSessions()`, `getPageViews()`, `getItemViews()`, `getAddToCart()`,
`getCheckouts()`, `getPurchases()` and `getDeviceDays()`.

### Funnel

```php
use Dotsplatform\AnalyticsApi\Enums\FunnelChannel;

$funnel = $analyticsClient->stats()->getFunnel($request);                          // FunnelChannel::ALL
$appsFunnel = $analyticsClient->stats()->getFunnel($request, FunnelChannel::APPS); // ALL, WEB, APPS, IOS, ANDROID

foreach ($funnel->getSteps() as $step) {
    echo $step->getKey(), ' ', $step->getSessions(), ' ', $step->getPercentOfStart() ?? '–';
}
$funnel->getTracking()->getCoveragePercent(); // null unless FunnelChannel::ALL
$funnel->getRevenue()->getAverageOrderCents(); // null when there were no orders
```

`FunnelDTO`: `getChannel()` (`?FunnelChannel`), `getChannelValue()`, `getSteps()` and `getCheckoutSteps()` (lists of
`FunnelStepDTO`), `getOrders()`, `getTracking()`, `getRevenue()`.

- `FunnelStepDTO`: `getKey()`, `getLabel()` (English), `getSessions()`, `getPercentOfPrevious()`, `getPercentOfStart()`
  (both `?float`), `isOptional()`. Step keys in order: `sessions`, `company_lists` (only when the account sends it),
  `menus`, `item_views` (optional: people can add to cart from the menu), `add_to_cart`, `cart_views`, `checkouts`,
  `orders`. Checkout step keys: `checkouts`, `verification_shown`, `sms_sent`, `captchas`, `payment_info`, `orders`;
  their percent of the previous step is always null and the percent of start is the percent of checkouts.
- `FunnelOrdersDTO`: `getTracked()`, `getOutcomes()` and `getCancelReasons()` (lists of `OrderOutcomeDTO`: `getKey()`,
  `getLabel()`, `getOrders()`, `getPercentOfOrders()`). Outcome keys: `completed`, `cancelled` (always), `in_progress`,
  `not_in_livesite` (only when not zero). A cancel reason key is the livesite `orders.declineReasonType` as a string
  (`"1"`..`"13"`) or `"none"`, most orders first.
- `FunnelTrackingDTO`: `getLivesiteOrders()`, `getMatchedOrders()`, `getCoveragePercent()`, all null unless the channel
  is `ALL` (livesite cannot tell the channel of an order).
- `FunnelRevenueDTO`: `getTrackedCents()`, `getCompletedCents()`, `getAverageOrderCents()`, in cents of the account
  currency.

### Heatmap

```php
use Dotsplatform\AnalyticsApi\Enums\HeatmapMetric;

$heatmap = $analyticsClient->stats()->getHeatmap($request, HeatmapMetric::ORDERS); // SESSIONS (default), ORDERS, CONVERSION

foreach ($heatmap->getWeekdays() as $weekdayRow) {
    $mondayEvening = $weekdayRow->getHours()[19]; // float; the index is the Kyiv hour
}
$heatmap->getMaxValue(); // the largest cell, to scale the colours
```

`HeatmapDTO`: `getMetric()` (`?HeatmapMetric`), `getMetricValue()`, `getMaxValue()`, `getWeekdays()`: always 7
`HeatmapWeekdayDTO` (`getWeekday()` 1 = Monday … 7 = Sunday, `getHours()` 24 floats). `SESSIONS` and `ORDERS` are the
average per day of that weekday in the period; `CONVERSION` is the percent of sessions with an order.

### Traffic sources

```php
use Dotsplatform\AnalyticsApi\DTO\Sources\SourceDTO;
use Dotsplatform\AnalyticsApi\Enums\SourceKind;

$sources = $analyticsClient->stats()->getSources($request);
foreach ($sources->getSources() as $source) {
    $isPaid = $source->getKind() === SourceKind::PAID;
    $isDirect = $source->getName() === SourceDTO::NAME_DIRECT;
}
```

`SourcesDTO`: `getTotalSessions()`, `getSources()` (list of `SourceDTO`: `getName()`, `getKind()` (`?SourceKind`:
`APP`, `PAID`, `CAMPAIGN`, `SEARCH`, `SOCIAL`, `AI`, `REFERRAL`, `DIRECT`), `getKindValue()`, `getSessions()`,
`getOrderingSessions()`, `getConversionPercent()`), `getCampaigns()` (at most 10 `CampaignDTO`: `getName()` = the
utm_campaign, `getSourceName()` = the name of its source, `getSessions()`, `getOrderingSessions()`,
`getConversionPercent()`). Source names are network names or domains, except `SourceDTO::NAME_DIRECT` ("Direct"),
`NAME_BACK_FROM_PAYMENT` ("Back from payment"), `NAME_APP_IOS` ("App (iOS)") and `NAME_APP_ANDROID` ("App (Android)"):
translate those. The conversion percent is null when there were no sessions.

### Retention

```php
use Dotsplatform\AnalyticsApi\Enums\RetentionView;

$retention = $analyticsClient->stats()->getRetention($request); // RetentionView::REPEAT_BUYERS
$devices = $analyticsClient->stats()->getRetention($request, RetentionView::DEVICES_RETURNING);

foreach ($retention->getCohorts() as $cohort) {
    $cohort->getStartDate(); // '2026-09-01'
    $cohort->getPercents();  // one ?float per offset; null = that period has not happened yet
}
```

`RetentionDTO`: `getAccountToken()` (null = all accounts; there is no `getFilters()`), `getView()`
(`?RetentionView`: `REPEAT_BUYERS`, `DEVICES_RETURNING`, `DEVICES_ORDERING`), `getViewValue()`, `getPeriod()`
(`RetentionDTO::PERIOD_MONTH` for repeat buyers, `PERIOD_WEEK` for the device views), `getOffsets()` (`"M1"`, `"M2"`, …
or `"W1"`..`"W8"`), `getCohorts()`, oldest first: `RetentionCohortDTO` (`getStartDate()`, `getLabel()` in English,
`getSize()`, `getPercents()`, `isMixed()` = the first period of the data, which also holds earlier customers).
Retention ignores the request dates; the platform applies to the device views only.

### Reading the numbers

- **Device-days** (`getDeviceDays()`) sum daily unique devices: a device active on 3 days counts 3. Compare it
  between periods; do not show it as a user count.
- **Device-hours** (`getDeviceHours()`, hourly visits only) sum hourly unique devices: a device active in two hours of
  a day counts twice. Use it to compare hours and weekdays.
- **Active users for all accounts** are per-account numbers summed: a device used in two accounts counts twice.
- **Gap-free series**: overview `getDaily()` and active users `getDaily()` have one row per day of the period,
  `getHours()` always has 24 rows and `getWeekdays()` 7; days and hours without data have zeros.
- **Missing values are null**: a breakdown row of devices that did not report the dimension (no OS version, no app
  version) has `getValue() === null`; likewise `EventStatDTO::getEvent()`, `TopItemDTO::getItemId()` and
  `TopPageDTO::getPage()` return null when the events carried no value. `TopItemDTO::getName()` and
  `getCategory()` are null while the service has not named the item yet (it copies names from livesite hourly).
- **Add-to-cart rate** is `add_to_cart / views` rounded to 4 decimals, `0.0` when there were no views.
- Platforms, dimensions, funnel channels, heatmap metrics, retention views and source kinds in responses are kept as
  **raw strings**, so a value this SDK version does not know never breaks a response (the enum getters, e.g.
  `PlatformBreakdownDTO::getDimension()` or `SourceDTO::getKind()`, return null for it; the `...Value()` getters
  return the raw string). Requests take enums only.
- **Percents** (funnel, sources, retention, heatmap conversion) are 0–100 and null when there is nothing to divide
  by. Funnel and sources percents are not rounded (round them for display); heatmap and retention values are rounded
  to 2 decimals. The add-to-cart rate is the exception: a 0–1 ratio.

## Errors

Every call may throw a subclass of `Dotsplatform\AnalyticsApi\Exceptions\AnalyticsApiException`:

| Exception | When |
|---|---|
| `AnalyticsValidationException` | 422: `getErrors()` / `getErrorsFor('date_to')`, Laravel style (bad date format, `date_to` before `date_from`, range over 366 days, unknown platform, limit out of 1–500) |
| `AnalyticsUnauthorizedException` | 401: wrong or missing token |
| `AnalyticsNotFoundException` | 404: the path does not exist (the service also answers 404 for account 0) |
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
