<?php


namespace Spartan\Common\Command\Remote;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run Remote
 *
 * @package Spartan\Common
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Remote extends Command
{
    protected function configure()
    {
        $this->withSynopsis('remote:run', 'Run a command on remote')
             ->withArgument('name', 'Remote name')
             ->withArgument('cmd', 'Command to run')
             ->withOption('path', 'Path on remote', '~');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $remote = self::sshRemotes()[$input->getArgument('name')];
        $cmd    = $input->getArgument('cmd');

        $this->process($cmd, $output, $remote);

        return 0;
    }

    /**
     * @param string $file
     *
     * @return array
     */
    public static function sshRemotes(string $file = '/home/$USER/.ssh/config')
    {
        $user = get_current_user();
        $file = str_replace('$USER', $user, $file);

        if (!file_exists($file)) {
            return [];
        }

        $lines = explode(PHP_EOL, trim(file_get_contents($file)));

        $remotes = [];
        $remote  = '*';
        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }

            if (strtolower(substr($line, 0, 4)) == 'host') {
                $remote = explode(' ', $line)[1];
            }

            if (strtolower(substr(trim($line), 0, 8)) == 'hostname') {
                $remotes[$remote]['host'] = explode(' ', trim($line))[1];
            }

            if (strtolower(substr(trim($line), 0, 4)) == 'port') {
                $remotes[$remote]['port'] = explode(' ', trim($line))[1];
            }

            if (strtolower(substr(trim($line), 0, 4)) == 'user') {
                $remotes[$remote]['user'] = explode(' ', trim($line))[1];
            }
        }

        $defaults = ($remotes['*'] ?? []) + ['user' => 'root', 'port' => 22, 'path' => '~'];

        foreach ($remotes as $name => &$config) {
            $config += $defaults;
        }

        unset($remotes['*']);

        return $remotes;
    }
}
