<?php

declare(strict_types=1);

/**
 * Description of AccountListDTO.php
 *
 * GET accounts: every account the analytics service knows, by name.
 *
 * @copyright Copyright (c) DOTSPLATFORM, LLC
 * @author    Bogdan Mamontov <bohdan.mamontov@dotsplatform.com>
 */

namespace Dotsplatform\AnalyticsApi\DTO\Accounts;

use Dots\Data\DTO;
use Dotsplatform\AnalyticsApi\Support\ResponseField;

class AccountListDTO extends DTO
{
    /** @var list<AccountDTO> */
    protected array $accounts = [];

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromResponse(array $data): static
    {
        $accounts = [];
        foreach (ResponseField::listOfObjects($data, 'accounts') as $rowData) {
            $accounts[] = AccountDTO::fromResponse($rowData);
        }

        return static::fromArray(['accounts' => $accounts]);
    }

    /**
     * @return list<AccountDTO>
     */
    public function getAccounts(): array
    {
        return $this->accounts;
    }
}
