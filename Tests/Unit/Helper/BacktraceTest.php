<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Debug.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Debug\Tests\Unit\Helper;

use PHPUnit\Framework\Attributes\Test;
use PSBits\Debug\Helper\Backtrace;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class BacktraceTest
 *
 * @package PSBits\Debug\Tests\Unit\Helper
 */
class BacktraceTest extends UnitTestCase
{
    #[Test]
    public function getCallerReturnsTheClassAndFunctionOfTheGivenLevel(): void
    {
        $backtrace = $this->backtraceAtLevelTwo();

        self::assertSame(static::class . '::backtraceAtLevelOne', $backtrace->getCaller(1));
        self::assertSame(static::class . '::backtraceAtLevelTwo', $backtrace->getCaller(2));
    }

    #[Test]
    public function theFormattedOutputResolvesAllPlaceholdersAtTheGivenLevel(): void
    {
        $backtrace = $this->backtraceAtLevelTwo();

        $output = $backtrace->getFormattedOutput('{class}::{function} in {file}:{line}', 2);

        self::assertStringStartsWith(static::class . '::backtraceAtLevelTwo in ', $output);
        self::assertStringContainsString('BacktraceTest.php:', $output);
    }

    #[Test]
    public function theIndividualCallerAccessorsReportTheGivenLevel(): void
    {
        $backtrace = $this->backtraceAtLevelTwo();

        self::assertSame(static::class, $backtrace->getCallerClass(2));
        self::assertSame('backtraceAtLevelTwo', $backtrace->getCallerFunction(2));
        self::assertGreaterThan(0, $backtrace->getCallerLine(2));
        self::assertStringEndsWith('BacktraceTest.php', $backtrace->getCallerFile(2));
        self::assertSame('->', $backtrace->getCallerType(2));
        self::assertSame([], $backtrace->getCallerArgs(2));
        self::assertSame($this, $backtrace->getCallerObject(2));
        self::assertNotEmpty($backtrace->getBacktrace());
    }

    private function backtraceAtLevelOne(): Backtrace
    {
        return new Backtrace();
    }

    private function backtraceAtLevelTwo(): Backtrace
    {
        return $this->backtraceAtLevelOne();
    }
}
