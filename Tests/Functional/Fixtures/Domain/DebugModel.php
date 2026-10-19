<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Debug.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Debug\Tests\Functional\Fixtures\Domain;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class DebugModel
 *
 * A bare fixture model without TCA; QueryUtility only turns the query into
 * SQL and renders it, so no database record or table definition is needed.
 *
 * @package PSBits\Debug\Tests\Functional\Fixtures\Domain
 */
class DebugModel extends AbstractEntity
{
    protected string $header = '';

    public function getHeader(): string
    {
        return $this->header;
    }

    public function setHeader(string $header): void
    {
        $this->header = $header;
    }
}
