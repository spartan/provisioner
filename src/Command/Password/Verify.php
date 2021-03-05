<?php

namespace Spartan\Provisioner\Command\Password;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Verify Password
 *
 * @package Spartan\Console
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Verify extends Command
{
    protected function configure()
    {
        $this->withSynopsis(
            'pass:verify',
            'Generate hash for a password',
            ['pass:check']
        )
             ->withArgument('pass', 'Password to check')
             ->withArgument('hash', 'Hash to check');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!password_verify($input->getArgument('pass'), trim($input->getArgument('hash'), "'"))) {
            $output->writeln('<danger>Password does not match the hash!</danger>');
            return 1;
        } else {
            $output->writeln('<success>Password matches the hash!</success>');
        }

        return 0;
    }
}
