<?php

declare(strict_types=1);

/**
 * Description of StubFailure.php
 *
 * The error every Fail*StubClient method throws: the analytics service answered 503.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\Clients\Stubs\Fail;

use Dotsplatform\AnalyticsApi\Exceptions\AnalyticsServerException;

final class StubFailure
{
    public const int STATUS_CODE = 503;

    public const string MESSAGE = 'The analytics service is unavailable (FailAnalyticsStubClient).';

    public static function make(): AnalyticsServerException
    {
        return new AnalyticsServerException(self::MESSAGE, self::STATUS_CODE);
    }
}
