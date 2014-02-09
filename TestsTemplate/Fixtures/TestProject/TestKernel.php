<?php
namespace Imatic\Bundle\TestsTemplateBundle\Tests\Fixtures\TestProject;

use Imatic\Bundle\TestingBundle\Test\TestKernel as BaseTestKernel;

class TestKernel extends BaseTestKernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $parentBundles = parent::registerBundles();

        $bundles = [
            new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),

            new \Imatic\Bundle\TestsTemplateBundle\ImaticTestsTemplateBundle(),
            new \Imatic\Bundle\TestsTemplateBundle\Tests\Fixtures\TestProject\ImaticTestsTemplateBundle\AppImaticTestsTemplateBundle(),
        ];

        return array_merge($parentBundles, $bundles);
    }
}
