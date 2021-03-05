<?php

namespace Spartan\Provisioner\Command\Package;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update Package (only missing files)
 *
 * @property string $name
 *
 * @package Spartan\Provisioner
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Update extends Command
{
    protected function configure()
    {
        $this->withSynopsis('pkg:update', 'Copy missing files in a spartan package')
             ->withOption('path', 'Path to update into', '.');
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

        $src = __DIR__ . '/../../../data/package';
        $this->process("cp -nr {$src}/. {$path}");

        return 0;
    }
}
