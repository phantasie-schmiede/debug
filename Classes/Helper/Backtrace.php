<?php
declare(strict_types=1);

/*
 * This file is part of PSBits Debug.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Debug\Helper;

/**
 * Class Backtrace
 *
 * @package PSBits\Debug\Helper
 */
class Backtrace
{
    public const string DEFAULT_FORMAT = '{class}::{function} in {file}:{line}';

    protected array $backtrace;

    public function __construct()
    {
        $this->backtrace = debug_backtrace();
    }

    public function getBacktrace(): array
    {
        return $this->backtrace;
    }

    public function getCaller(int $level = 1): string
    {
        return $this->backtrace[$level]['class'] . '::' . $this->backtrace[1]['function'];
    }

    public function getCallerArgs(int $level = 1): array
    {
        return $this->backtrace[$level]['args'];
    }

    public function getCallerClass(int $level = 1): string
    {
        return $this->backtrace[$level]['class'];
    }

    public function getCallerFile(int $level = 1): string
    {
        return $this->backtrace[$level]['file'];
    }

    public function getCallerFunction(int $level = 1): string
    {
        return $this->backtrace[$level]['function'];
    }

    public function getCallerLine(int $level = 1): int
    {
        return $this->backtrace[$level]['line'];
    }

    public function getCallerObject(int $level = 1): object
    {
        return $this->backtrace[$level]['object'];
    }

    public function getCallerType(int $level = 1): string
    {
        return $this->backtrace[$level]['type'];
    }

    public function getFormattedOutput(string $format = self::DEFAULT_FORMAT, int $level = 1): string
    {
        return str_replace(
            [
                '{class}',
                '{function}',
                '{line}',
                '{file}',
            ],
            [
                $this->getCallerClass($level),
                $this->getCallerFunction($level),
                $this->getCallerLine($level),
                $this->getCallerFile($level),
            ],
            $format
        );
    }
}
