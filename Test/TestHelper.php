<?php

namespace Imatic\Bundle\TestingBundle\Test;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;

class TestHelper
{
    /**
     * Reload database
     *
     * @param Application $application
     */
    public function reloadDatabase(Application $application)
    {
        // drop database
        $application->run(
            new ArrayInput(
                array(
                    //'-q' => null,
                    '-e' => 'test',
                    '--force' => null,
                    'command' => 'doctrine:database:drop'
                )
            )
        );

        // create database
        $application->run(
            new ArrayInput(
                array(
                    //'-q' => null,
                    '-e' => 'test',
                    'command' => 'doctrine:database:create'
                )
            )
        );

        // create schema
        $application->run(
            new ArrayInput(
                array(
                    //'-q' => null,
                    '-e' => 'test',
                    'command' => 'doctrine:schema:create'
                )
            )
        );

        // load fixtures
        $application->run(
            new ArrayInput(
                array(
                    //'-q' => null,
                    '-e' => 'test',
                    'command' => 'doctrine:fixtures:load',
                    '--no-interaction' => true,
                )
            )
        );
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
}
