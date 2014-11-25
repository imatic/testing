<?php

namespace Imatic\Bundle\TestingBundle\Composer;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ScriptHandler
{
    protected static $defaultOptions = [
        'source-dir' => 'vendor/imatic/view-bundle/Imatic/Bundle/ViewBundle/Tests/Fixtures/TestProject',
        'target-dir' => 'Tests/Fixtures/TestProject',
    ];

    public static function symlinkBowerIfNotExists()
    {
        $options = static::getOptions();
        static::createSymlinkIfNotExists(sprintf('%s/bower.json', $options['source-dir']), sprintf('%s/bower.json', $options['target-dir']));
        static::createSymlinkIfNotExists(sprintf('%s/.bowerrc', $options['source-dir']), sprintf('%s/.bowerrc', $options['target-dir']));
    }

    protected static function createSymlinkIfNotExists($source, $target)
    {
        $source = sprintf('%s/%s', getcwd(), $source);
        if (!file_exists($target) && file_exists($source)) {
            symlink($source, $target);
        }
    }

    protected static function getOptions()
    {
        return static::$defaultOptions;
    }
}
