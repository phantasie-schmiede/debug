<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Debug.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Debug\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\Attributes\Test;
use PSBits\Debug\Service\StopWatchService;
use PSBits\Debug\ViewHelpers\Debug\StopWatchViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

use function ob_get_clean;
use function ob_start;

/**
 * Class StopWatchViewHelperTest
 *
 * @package PSBits\Debug\Tests\Unit\ViewHelpers
 */
class StopWatchViewHelperTest extends UnitTestCase
{
    #[Test]
    public function headerAndPrecisionAreRegisteredAsOptionalArguments(): void
    {
        $viewHelper = $this->createViewHelper();

        $definitions = $viewHelper->prepareArguments();

        self::assertArrayHasKey('header', $definitions);
        self::assertSame('string', $definitions['header']->getType());
        self::assertFalse($definitions['header']->isRequired());
        self::assertSame('Stopwatch', $definitions['header']->getDefaultValue());

        self::assertArrayHasKey('precision', $definitions);
        self::assertSame('integer', $definitions['precision']->getType());
        self::assertFalse($definitions['precision']->isRequired());
        self::assertSame(4, $definitions['precision']->getDefaultValue());
    }

    #[Test]
    public function renderReturnsTheChildrenAndMeasuresTheDuration(): void
    {
        $stopWatch  = new StopWatchService();
        $viewHelper = $this->createViewHelper(
            $stopWatch,
            [
                'header'    => 'My Header',
                'precision' => 2,
            ],
        );

        ob_start();
        $rendered = $viewHelper->render();
        ob_get_clean();

        self::assertSame('child output', $rendered);
        self::assertSame('My Header', $stopWatch->getHeader());
        self::assertSame(2, $stopWatch->getPrecision());
        self::assertCount(1, $stopWatch->getTimeLog(), 'The rendered children have been measured.');
        self::assertSame(0.0, $stopWatch->getStartTime(), 'The stop watch has been stopped and reset.');
    }

    #[Test]
    public function theDefaultArgumentsAreUsedWithoutExplicitArguments(): void
    {
        $stopWatch  = new StopWatchService();
        $viewHelper = $this->createViewHelper($stopWatch);

        ob_start();
        $rendered = $viewHelper->render();
        ob_get_clean();

        self::assertSame('child output', $rendered);
        self::assertSame('Stopwatch', $stopWatch->getHeader());
        self::assertSame(4, $stopWatch->getPrecision());
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function createViewHelper(?StopWatchService $stopWatch = null, array $arguments = []): StopWatchViewHelper
    {
        $viewHelper = new StopWatchViewHelper($stopWatch ?? new StopWatchService());
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure(static fn() => 'child output');
        $viewHelper->initializeArguments();

        /*
         * The ViewHelperInvoker applies the registered defaults for the arguments that were not
         * passed in the template - mirror that here.
         */
        $viewHelper->setArguments(
            $arguments + array_map(
                static fn($definition) => $definition->getDefaultValue(),
                $viewHelper->prepareArguments(),
            ),
        );

        return $viewHelper;
    }
}
