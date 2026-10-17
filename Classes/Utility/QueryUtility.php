<?php
declare(strict_types=1);

/*
 * This file is part of PSBits Debug.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Debug\Utility;

use PSBits\Debug\Helper\Backtrace;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

use function is_string;

/**
 * Class QueryUtility
 *
 * @package PSBits\Debug\Utility
 */
class QueryUtility
{
    /**
     * @param QueryInterface<object> $query
     * @param string|null            $title
     *
     * @return void
     */
    public static function debug(QueryInterface $query, ?string $title = null): void
    {
        $queryParser = GeneralUtility::makeInstance(Typo3DbQueryParser::class);
        $sql = $queryParser->convertQueryToDoctrineQueryBuilder($query)
                ->getSQL() . ';';
        $parameters = $queryParser->convertQueryToDoctrineQueryBuilder($query)
            ->getParameters();
        $searchArray = array_keys($parameters);
        array_walk($searchArray, static function(&$value) {
            $value = ':' . $value;
        });
        $replaceArray = array_values($parameters);
        array_walk($replaceArray, static function(&$value) {
            $value = is_string($value) ? '"' . $value . '"' : (string)$value;
        });

        /** @var ServerRequestInterface|null $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $view    = GeneralUtility::makeInstance(ViewFactoryInterface::class)->create(
            new ViewFactoryData(
                templatePathAndFilename: GeneralUtility::getFileAbsFileName('EXT:debug/Resources/Private/Templates/Message.html'),
                request: $request,
            )
        );
        $view->assignMultiple([
            // Revert search order so that "value10" gets replaced before "value1"!
            'message' => str_replace(array_reverse($searchArray), array_reverse($replaceArray), $sql),
            'title'   => $title ?: (new Backtrace())->getFormattedOutput(
                'Query from {class}::{function} in {file}:{line}',
                2
            ),
        ]);

        echo $view->render();
    }
}
