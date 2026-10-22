<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Debug.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Debug\ViewHelpers\Debug;

use PSBits\Debug\Service\StopWatchService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class StopWatchViewHelper
 *
 * @package PSBits\Debug\ViewHelpers\Debug
 */
class StopWatchViewHelper extends AbstractViewHelper
{
    protected                  $escapeOutput = false;
    protected StopWatchService $stopWatchService;

    public function __construct(
        StopWatchService $stopWatchService,
    ) {
        $this->stopWatchService = $stopWatchService;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('header', 'string', 'Header for the debug output', false, 'Stopwatch');
        $this->registerArgument('precision', 'integer', 'Number of decimals in output', false, 4);
    }

    public function render(): mixed
    {
        $this->stopWatchService->setHeader($this->arguments['header']);
        $this->stopWatchService->setPrecision($this->arguments['precision']);

        $this->stopWatchService->start();
        $output = $this->renderChildren();
        $this->stopWatchService->stop();

        return $output;
    }
}
