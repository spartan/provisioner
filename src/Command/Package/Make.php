<?php

namespace Spartan\Provisioner\Command\Package;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make Package
 *
 * @property string $name
 *
 * @package Spartan\Provisioner
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Make extends Command
{
    protected function configure()
    {
        $this->withSynopsis('pkg:make', 'Create a spartan package', ['make:pkg'])
             ->withArgument('name', 'Package name')
             ->withOption('path', 'Path to install into', '.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = rtrim($input->getOption('path'), '/');

        // we need an empty directory
        if (count(glob("{$path}/*"))) {
            throw new \RuntimeException('Directory is not empty!');
        }

        /*
         * Dirs
         */

        $dirs = [
            "{$path}/src/Definition",
            "{$path}/src/Exception",
            "{$path}/tests",
        ];

        foreach ($dirs as $dir) {
            @mkdir($dir, 0777, true);
        }

        /*
         * Files
         */

        $src = __DIR__ . '/../../../data/package';
        $this->process("cp -nr {$src}/. {$path}");

        /*
         * Replace
         */
        $this->replaceInFiles($path);

        return 0;
    }

    protected function replaceInFiles(string $path)
    {
        $replacements = [
            '{package}' => strtolower($this->name),
            '{Package}' => ucfirst(strtolower($this->name)),
            '{year}'    => date('Y'),
            '{author}'  => trim(shell_exec('git config user.name')),
            '{email}'   => trim(shell_exec('git config user.email')),
        ];

        $files = [
            'composer.json',
            'LICENSE',
            'README.md',
        ];

        foreach ($files as $file) {
            $filePath = "{$path}/{$file}";

            file_put_contents(
                $filePath,
                str_replace(array_keys($replacements), $replacements, file_get_contents($filePath)),
            );
        }
    }
}
