<?php

namespace Spartan\Provisioner\Command;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Init Command
 *
 * @package Spartan\Provisioner
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Init extends Command
{
    protected function configure()
    {
        $this->withSynopsis('init', 'Initialize provisioner settings');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
         * [x] Create recipes.json
         */

        if (file_exists('/home/' . get_current_user() . '/.spartan/recipes.json')) {
            throw new \InvalidArgumentException('Recipe file already exists!');
        }

        file_put_contents(
            '/home/' . get_current_user() . '/.spartan/recipes.json',
            json_encode(
                [
                    'options' => [],
                    'recipes' => [
                        'default' => [],
                        'api'     => [],
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        return 0;
    }
}
