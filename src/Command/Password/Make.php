<?php

namespace Spartan\Provisioner\Command\Password;

use RandomLib\Generator;
use SecurityLib\Strength;
use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make Password
 *
 * @package Spartan\Console
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Make extends Command
{
    protected function configure()
    {
        $this->withSynopsis(
            'pass:make',
            'Generate a password',
            ['pass:create', 'pass:gen'],
            [
                'Simple password' => 'pass:make',
                'Medium password' => 'pass:make --len=32 --medium --symbols --brackets',
            ]
        )
             ->withOption('len', 'Length of the password', 16)
             ->withOption('low', 'Low strength')
             ->withOption('medium', 'Medium strength')
             ->withOption('symbols', 'Use symbols')
             ->withOption('brackets', 'Use brackets')
             ->withOption('easy', 'Easy to read');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $len = $input->getOption('len');

        $strength = \SecurityLib\Strength::MEDIUM;
        if ($this->isOptionPresent('low')) {
            $strength = \SecurityLib\Strength::LOW;
        }
        if ($this->isOptionPresent('strong')) {
            $strength = \SecurityLib\Strength::HIGH;
        }

        $options = Generator::CHAR_ALNUM;
        if ($this->isOptionPresent('symbols')) {
            $options |= Generator::CHAR_SYMBOLS;
        }
        if ($this->isOptionPresent('brackets')) {
            $options |= Generator::CHAR_BRACKETS;
        }
        if ($this->isOptionPresent('easy')) {
            $options |= Generator::EASY_TO_READ;
        }

        $output->writeln(self::generate($len, $strength, $options));

        return 0;
    }

    public static function generate(
        int $len,
        int $strength = Strength::MEDIUM,
        int $options = Generator::CHAR_ALNUM
    ) {
        $factory   = new \RandomLib\Factory();
        $generator = $factory->getGenerator(new \SecurityLib\Strength($strength));

        return $generator->generateString($len, $options);
    }
}
