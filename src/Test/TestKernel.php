<?php declare(strict_types=1);
namespace Imatic\Testing\Test;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    protected $rootDir;
    private $config;

    public function __construct()
    {
        parent::__construct('test', true);
        $config = $this->getRootDir() . '/config/config.yml';

        if (!\file_exists($config)) {
            throw new \RuntimeException(\sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->config);
    }

    public function serialize()
    {
        return \serialize([$this->config]);
    }

    public function unserialize($str)
    {
        \call_user_func_array([$this, '__construct'], \unserialize($str));
    }

    public static function getClass()
    {
        return \get_called_class();
    }

    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $r = new \ReflectionObject($this);
            $this->rootDir = \dirname($r->getFileName());
        }

        return $this->rootDir;
    }
}
