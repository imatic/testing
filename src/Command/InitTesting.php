<?php
namespace Imatic\Testing\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InitTesting extends Command
{
    /** @var Filesystem */
    private $fs;

    private $bundleName;

    protected function configure()
    {
        $this
            ->setName('init:testing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \define('ROOT_DIR', __DIR__ . '/../../../../..');
        \define('BUNDLE_NAME_HOLDER', 'TestsTemplate');
        \define('TESTS_DIR', ROOT_DIR . '/Tests');
        \define('TEST_PROJECT_ROOT_DIR', TESTS_DIR . '/Fixtures/TestProject');
        \define('TEMPLATES_DIR', ROOT_DIR . '/vendor/imatic/testing-bundle/resources/skeleton/TestsTemplate');

        $this->fs = new FileSystem();
        $this->bundleName = $this->getBundleName();

        $this->fs->mirror(TEMPLATES_DIR, TESTS_DIR);
        $this->removeGitKeepFiles(TESTS_DIR);

        $this->updateTemplateFiles(TESTS_DIR);
        $this->makeCacheAndLogDirs();
        $this->updateGitignore();
    }

    private function removeGitKeepFiles($path)
    {
        $files = \array_filter(\scandir($path), function ($fileName) {
            return !\in_array($fileName, ['.', '..'], true);
        });

        foreach ($files as $fileName) {
            $filePath = $this->filePath($path, $fileName);
            if (\is_dir($filePath)) {
                $this->removeGitKeepFiles($filePath);
            } elseif ($fileName === '.gitkeep') {
                $this->fs->remove($filePath);
            }
        }
    }

    private function updateTemplateFiles($path)
    {
        $newFiles = \array_filter(\scandir($path), function ($fileName) {
            return \strpos($fileName, '.') !== 0;
        });

        foreach ($newFiles as $fileName) {
            $filePath = $this->filePath($path, $fileName);

            if (\is_dir($filePath)) {
                $this->updateTemplateFiles($filePath);
            } elseif (\is_file($filePath)) {
                $this->updateFileContent($filePath, $fileName);
            }

            if (\strpos($fileName, BUNDLE_NAME_HOLDER) !== false) {
                $newFileName = \str_replace(BUNDLE_NAME_HOLDER, $this->bundleName, $fileName);
                $this->fs->rename($filePath, $this->filePath($path, $newFileName));
            }
        }
    }

    private function updateFileContent($filePath, $fileName)
    {
        $fileContent = \file_get_contents($filePath);
        $fileContent = \str_replace(BUNDLE_NAME_HOLDER, $this->bundleName, $fileContent);
        $fileContent = \str_replace(
            $this->camelToSnake(BUNDLE_NAME_HOLDER),
            $this->camelToSnake($this->bundleName),
            $fileContent
        );

        \file_put_contents($filePath, $fileContent);
    }

    private function makeCacheAndLogDirs()
    {
        $files = [
            $this->filePath(TEST_PROJECT_ROOT_DIR, 'cache'),
            $this->filePath(TEST_PROJECT_ROOT_DIR, 'logs'),
        ];
        $this->fs->mkdir($files);
        $this->fs->chmod($files, 0777);
    }

    private function getBundleName()
    {
        $rootFiles = \scandir(ROOT_DIR);
        $bundleFiles = \array_filter($rootFiles, function ($fileName) {
            return \preg_match('/Bundle.php$/', $fileName);
        });

        if (\count($bundleFiles) !== 1) {
            throw new \RuntimeException('Exactly one file "/^.*Bundle.php$/" not found in your project root!');
        }

        $bundleFile = \implode('/', [ROOT_DIR, \reset($bundleFiles)]);
        $matches = [];
        if (!\preg_match('/\bnamespace\s+\b([^\\\]+)\\\/i', \file_get_contents($bundleFile), $matches)) {
            throw new \RuntimeException('Cannot determine vendor name from namespace of the bundle classs.');
        }

        $vendorName = $matches[1];

        return \str_replace($vendorName, '', \str_replace('Bundle.php', '', \reset($bundleFiles)));
    }

    private function filePath($path, $file)
    {
        return $path . '/' . $file;
    }

    private function camelToSnake($string)
    {
        return \strtolower(\preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    private function updateGitignore()
    {
        $gitignoreAppend = <<<'END'
Tests/Fixtures/TestProject/web/bundles
Tests/Fixtures/TestProject/cache
Tests/Fixtures/TestProject/logs
Tests/Fixtures/TestProject/ProjectBundle/DataFixtures
Tests/Fixtures/TestProject/ProjectBundle/Entity
END;

        $gitignorePath = $this->filePath(ROOT_DIR, '.gitignore');

        if (!$this->fs->exists($gitignorePath)) {
            $this->fs->touch($gitignorePath);
        }

        $originalContent = \file_get_contents($gitignorePath);
        \file_put_contents($gitignorePath, $originalContent . $gitignoreAppend);
    }
}
