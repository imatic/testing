<?php

namespace Imatic\Bundle\TestingBundle\Test;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;

class WebTestCase extends BaseWebTestCase
{
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

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $helper = new TestHelper();
        $helper->reloadDatabase($application);

        return $client;
    }
}
