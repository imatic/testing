<?php

namespace Imatic\Testing\Test;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

class Console
{
    protected $kernelClass;

    public function __construct($kernelClass)
    {
        $this->kernelClass = $kernelClass;
    }

    public function run()
    {
        set_time_limit(0);

        $input = new ArgvInput();
        $env = $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ? : 'dev');
        $debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(array('--no-debug', '')) && $env !== 'prod';

        $kernel = new $this->kernelClass($env, $debug);
        $application = new Application($kernel);
        $application->run($input);
    }
}
