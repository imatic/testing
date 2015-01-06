<?php
namespace Imatic\Bundle\TestingBundle\Test;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class WebTestCase extends BaseWebTestCase
{
    use WebTestCaseExtension;

    /**
     * @var ContainerInterface
     */
    protected static $firstContainer;

    protected function tearDown()
    {
        static::rollbackTransaction();
    }

    /**
     * {@inheritDoc}
     */
    protected static function getKernelClass()
    {
        throw new \LogicException(sprintf('You must implement method %s in your bundle.', __METHOD__));
    }

    /**
     * {@inheritDoc}
     */
    protected static function createClient(array $options = array(), array $server = array())
    {
        $server = array_merge($server, array('PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'password'));
        $client = parent::createClient($options, $server);
        $kernel = $client->getKernel();

        if (static::$firstContainer === null) {
            static::$firstContainer = $client->getContainer();
            static::initData($kernel);
        }
        static::startTransaction();

        return $client;
    }

    protected static function initData(KernelInterface $kernel)
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $helper = new TestHelper();
        $helper->reloadDatabase($application);
    }

    protected static function startTransaction()
    {
        foreach (static::$firstContainer->get('doctrine')->getManagers() as $om) {
            if ($om->getConnection()->isTransactionActive()) {
                continue;
            }
            $om->clear();
            $om->getConnection()->beginTransaction();
        }
    }

    protected static function rollbackTransaction()
    {
        if (static::$firstContainer === null) {
            return;
        }

        foreach (static::$firstContainer->get('doctrine')->getManagers() as $om) {
            $connection = $om->getConnection();

            while ($connection->isTransactionActive()) {
                $connection->rollback();
            }
        }
    }
}
