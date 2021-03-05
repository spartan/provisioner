<?php

namespace Spartan\Provisioner\Command\App;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Serve App
 *
 * @package Spartan\Console
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Serve extends Command
{
    protected function configure()
    {
        $this->withSynopsis('app:serve', 'Start local dev server')
             ->withOption('path', 'public')
             ->withOption('port', 'Port', 3030);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pwd  = getcwd();
        $port = $input->getOption('port');
        $path = $input->getOption('path');

        passthru("cd {$pwd}/{$path} && php -S localhost:{$port}");

        return 0;
    }
}
