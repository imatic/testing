<?php
namespace Imatic\Testing\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;

class TestHelper
{
    protected $onUpdateSchema = [];

    /**
     * Reload database
     *
     * @param Application $application
     * @param bool        $loadData
     */
    public function reloadDatabase(Application $application, $loadData = true)
    {
        $updateSchema = true;

        /*
         * because of the dbal issue, mysql can't be updated before tests inside the same application
         *
         * @link http://www.doctrine-project.org/jira/browse/DBAL-1067
         */
        if ($this->isRunningOnMysql($application)) {
            $this->truncateMysqlTables($application);
            $updateSchema = false;
        }

        if ($updateSchema) {
            $this->updateSchema($application);
        }

        if ($loadData) {
            $this->loadData($application);
        }
    }

    protected function updateSchema(Application $application)
    {
        $dropDatabaseOptions = [
            '-e' => 'test',
            '--force' => null,
            'command' => 'doctrine:database:drop',
        ];
        if ($this->supportsListingDatabases($application)) {
            $dropDatabaseOptions['--if-exists'] = null;
        }

        // drop database
        $application->run(new ArrayInput($dropDatabaseOptions));

        // create database
        $application->run(
            new ArrayInput(
                [
                    //'-q' => null,
                    '-e' => 'test',
                    'command' => 'doctrine:database:create',
                ]
            )
        );

        // create schema
        $application->run(
            new ArrayInput(
                [
                    //'-q' => null,
                    '-e' => 'test',
                    'command' => 'doctrine:schema:create',
                ]
            )
        );

        foreach ($this->onUpdateSchema as $cb) {
            $cb($application);
        }
    }

    protected function loadData(Application $application)
    {
        if (\array_key_exists(
            'DoctrineFixturesBundle',
            $application->getKernel()->getContainer()->getParameter('kernel.bundles')
        )) {
            // load fixtures
            $application->run(
                new ArrayInput(
                    [
                        '-e' => 'test',
                        'command' => 'doctrine:fixtures:load',
                        '--no-interaction' => true,
                    ]
                )
            );
        }
    }

    protected function isRunningOnMysql(Application $application)
    {
        $container = $application->getKernel()->getContainer();
        if ($container->has('doctrine.dbal.default_connection')) {
            /* @var $connection Connection */
            $connection = $container->get('doctrine.dbal.default_connection');
            if ($connection->getDatabasePlatform() instanceof MySqlPlatform) {
                return true;
            }
        }

        return false;
    }

    protected function truncateMysqlTables(Application $application)
    {
        /* @var $connection Connection */
        $connection = $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection');

        $result = $connection
            ->executeQuery('
                SELECT table_name
                FROM information_schema.tables
                WHERE table_schema = :db
            ', [
                ':db' => $connection->getDatabase(),
            ])
            ->fetchAll();

        if ($result) {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0')->execute();
            $tables = \array_map([$connection, 'quoteIdentifier'], \array_map('current', $result));
            foreach ($tables as $table) {
                $connection->executeQuery(\sprintf('TRUNCATE TABLE %s', $table))->execute();
            }

            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1')->execute();
        }
    }

    /**
     * Create symlink
     *
     * @param string $origin
     * @param string $target
     */
    public function symlink($origin, $target)
    {
        $fs = new Filesystem();
        $fs->symlink($origin, $target);
    }

    public function registerOnUpdateSchemaCb(\Closure $cb)
    {
        $this->onUpdateSchema[] = $cb;
    }

    private function supportsListingDatabases(Application $application)
    {
        /* @var $connection Connection */
        $connection = $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection');

        try {
            $connection->getDatabasePlatform()->getListDatabasesSQL();
        } catch (DBALException $e) {
            return false;
        }

        return true;
    }
}
