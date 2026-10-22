<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Debug.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Debug\Tests\Functional\Utility;

use Doctrine\DBAL\Exception;
use PHPUnit\Framework\Attributes\Test;
use PSBits\Debug\Tests\Functional\Fixtures\Domain\DebugModel;
use PSBits\Debug\Utility\QueryUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

use function get_class;
use function ob_get_clean;
use function ob_start;

/**
 * Class QueryUtilityTest
 *
 * Runs QueryUtility::debug() with a real Extbase query: the SQL is generated
 * by the Typo3DbQueryParser and rendered through the extension's own Fluid
 * template.
 *
 * @package PSBits\Debug\Tests\Functional\Utility
 */
class QueryUtilityTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/psbits/debug',
    ];

    #[Test]
    public function aQueryIsRenderedWithItsSqlParametersAndTitle(): void
    {
        $query = $this->queryWithConstraint();

        $output = $this->debug($query, 'Test query');

        self::assertStringContainsString('Test query', $output);
        self::assertStringContainsString('alert-info', $output, 'The extension template has been rendered.');
        self::assertStringContainsString('SELECT', $output);
        self::assertStringContainsString('LIKE &quot;Test&quot;', $output, 'The query parameter has been inlined.');
        self::assertStringNotContainsString(':dcValue', $output, 'No raw placeholder is left in the output.');
    }

    #[Test]
    public function withoutATitleTheCallingClassAndMethodAreUsed(): void
    {
        $query = $this->queryWithConstraint();

        $output = $this->debugWithoutTitle($query);

        self::assertStringContainsString('Query from ' . static::class . '::debugWithoutTitle in ', $output);
    }

    /**
     * @throws Exception
     */
    private function createFixtureTable(): void
    {
        $dataMap = GeneralUtility::makeInstance(DataMapFactory::class)
            ->buildDataMap(DebugModel::class);
        $tableName = $dataMap->getTableName();

        /*
         * The auto-increment syntax is dialect-specific; matching on the
         * lowercased platform class name keeps this compatible with both
         * doctrine/dbal 3 and 4 (the class casing differs between them).
         */
        $platform = strtolower(
            get_class(
                GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable('pages')
                    ->getDatabasePlatform(),
            )
        );
        $uidColumn = match (true) {
            str_contains($platform, 'sqlite')                                    => 'uid INTEGER PRIMARY KEY AUTOINCREMENT',
            str_contains($platform, 'maria') || str_contains($platform, 'mysql') => 'uid INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            default                                                              => 'uid SERIAL PRIMARY KEY',
        };

        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages')
            ->executeStatement(
                'CREATE TABLE IF NOT EXISTS ' . $tableName
                . ' (' . $uidColumn . ', pid INTEGER NOT NULL DEFAULT 0, header VARCHAR(255) NOT NULL DEFAULT \'\')',
            );
    }

    private function debug(Query $query, string $title): string
    {
        ob_start();
        QueryUtility::debug($query, $title);

        return (string)ob_get_clean();
    }

    private function debugWithoutTitle(Query $query): string
    {
        ob_start();
        QueryUtility::debug($query);

        return (string)ob_get_clean();
    }

    private function queryWithConstraint(): Query
    {
        $this->createFixtureTable();

        /*
         * A repository always attaches its query settings before the query reaches the parser -
         * mirror that here.
         */
        $query = GeneralUtility::makeInstance(Query::class);
        $query->setQuerySettings(GeneralUtility::makeInstance(Typo3QuerySettings::class));
        $query->setType(DebugModel::class);
        $query->matching($query->like('header', 'Test'));

        return $query;
    }
}
