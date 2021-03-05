<?php

namespace Spartan\Provisioner\Command\Password;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Hash Password
 *
 * @package Spartan\Console
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Hash extends Command
{
    protected function configure()
    {
        $this->withSynopsis('pass:hash', 'Generate hash for a password')
             ->withArgument('pass', 'Password to hash')
             ->withOption('algo', 'Defaults to ARGON2ID (or ARGON2I)', 'ARGON2ID');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $algorithm = strtoupper($input->getOption('algo')) == 'ARGON2I'
            ? PASSWORD_ARGON2I
            : PASSWORD_ARGON2ID;

        $output->writeln('' . password_hash($input->getArgument('pass'), $algorithm));

        return 0;
    }
}
