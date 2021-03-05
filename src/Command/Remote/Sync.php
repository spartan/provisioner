<?php


namespace Spartan\Common\Command\Remote;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sync Remote
 *
 * @package Spartan\Common
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Sync extends Command
{
    protected function configure()
    {
        $this->withSynopsis('remote:sync', 'Sync to remote', ['sync'])
             ->withOption('env', 'Environment name', 'dev')
             ->withOption('exclude', 'Comma separated additional exclusions');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadEnv();
        $envName      = $input->getOption('env');
        $remoteString = (string)getenv(strtoupper("APP_REMOTE_{$envName}"));
        [$remoteServer, $remotePath] = explode(':', $remoteString) + ['', '~'];

        $remote  = Remote::sshRemotes()[$remoteServer] ?? null;

        if (!$remote) {
            throw new \InvalidArgumentException('Unknown remote.');
        }

        $remote['path'] = $remotePath;

        $excludes = [
            ...['.git', '.idea', 'node_modules', '.env'],
            ...array_filter(explode(',', (string)$input->getOption('exclude'))),
        ];

        $cmdExcludes = [];
        foreach ($excludes as $exclude) {
            $cmdExcludes[] = '--exclude';
            $cmdExcludes[] = $exclude;
        }

        // check composer json
        $extra = explode(
            ' ',
            json_decode(file_get_contents('./composer.json'), true)['extra']['spartan']['sync'] ?? ''
        );

        $src  = rtrim(getcwd(), '/') . '/';
        $dst  = "{$remote['user']}@{$remote['host']}:{$remote['path']}";
        $port = $remote['port'];

        $cmd = [
            ...['rsync', '-av'],
            ...$cmdExcludes,
            ...$extra,
            ...[
                '-e',
                "ssh -p {$port}",
                '--no-perms',
                '--no-o',
                '--no-g',
                $src,
                $dst,
            ],
        ];

        $output->setVerbosity(ConsoleOutput::VERBOSITY_VERY_VERBOSE);

        $this->process($cmd, $output);

        return 0;
    }
}
