<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Debug.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Debug\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PSBits\Debug\Service\StopWatchService;
use RuntimeException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

use function ob_get_clean;
use function ob_start;
use function str_contains;

/**
 * Class StopWatchServiceTest
 *
 * @package PSBits\Debug\Tests\Unit\Service
 */
class StopWatchServiceTest extends UnitTestCase
{
    #[Test]
    public function resetAllowsRestarting(): void
    {
        $stopWatch = new StopWatchService();
        $stopWatch->start();
        $stopWatch->reset();
        $stopWatch->start();

        self::assertNotSame(0.0, $stopWatch->getStartTime());
    }

    #[Test]
    public function splitRecordsTheElapsedSplitTime(): void
    {
        $stopWatch = new StopWatchService();
        $stopWatch->start();
        usleep(1000);
        $stopWatch->split('split one');

        self::assertNotSame(0.0, $stopWatch->getSplitTime());
        self::assertCount(1, $stopWatch->getTimeLog());
        self::assertTrue(str_contains($stopWatch->getTimeLog()[0], 'Split time:'));
        self::assertTrue(str_contains($stopWatch->getTimeLog()[0], 'split one'));
    }

    #[Test]
    public function startSetsTheStartTime(): void
    {
        $stopWatch = new StopWatchService();
        $stopWatch->start();

        self::assertNotSame(0.0, $stopWatch->getStartTime());
    }

    #[Test]
    public function startingARunningStopWatchRaisesAnException(): void
    {
        $stopWatch = new StopWatchService();
        $stopWatch->start();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1551096470);

        $stopWatch->start();
    }

    #[Test]
    public function stopWritesTheTotalTimeAndResetsTheStopWatch(): void
    {
        $stopWatch = new StopWatchService();
        $stopWatch->start();
        usleep(1000);

        ob_start();
        $stopWatch->stop('', true);
        $output = (string)ob_get_clean();

        self::assertStringContainsString('Total time:', $output);
        self::assertStringContainsString('seconds', $output);
        self::assertSame(0.0, $stopWatch->getStartTime(), 'The stop watch has been reset.');
        self::assertSame(0.0, $stopWatch->getSplitTime());
        self::assertCount(1, $stopWatch->getTimeLog());
    }

    #[Test]
    public function theCommentIsAppendedToTheLogEntry(): void
    {
        $stopWatch = new StopWatchService();
        $stopWatch->start();

        ob_start();
        $stopWatch->stop('my comment', true);
        $output = (string)ob_get_clean();

        self::assertStringContainsString('my comment', $output);
        self::assertStringContainsString(' // my comment', $stopWatch->getTimeLog()[0]);
    }

    #[Test]
    public function theConstructorAcceptsCustomValues(): void
    {
        $stopWatch = new StopWatchService('Rendering', 2);

        self::assertSame('Rendering', $stopWatch->getHeader());
        self::assertSame(2, $stopWatch->getPrecision());
    }

    #[Test]
    public function theConstructorAppliesItsDefaults(): void
    {
        $stopWatch = new StopWatchService();

        self::assertSame('StopWatch', $stopWatch->getHeader());
        self::assertSame(4, $stopWatch->getPrecision());
        self::assertSame(0.0, $stopWatch->getStartTime());
        self::assertSame(0.0, $stopWatch->getSplitTime());
        self::assertSame([], $stopWatch->getTimeLog());
    }
}
