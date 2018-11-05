<?php declare(strict_types=1);
namespace Imatic\Bundle\TestsTemplateBundle\Tests\Fixtures\TestProject\ImaticTestsTemplateBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app_imatic_tests_template');

        return $treeBuilder;
    }
}
