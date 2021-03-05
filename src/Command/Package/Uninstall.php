<?php

namespace Spartan\Provisioner\Command\App;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Un-provision package
 *
 * @package Spartan\Provisioner
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Uninstall extends Command
{
    protected function configure()
    {
        $this->withSynopsis(
            'pkg:uninstall',
            'Provision app with spartan packages',
            [],
            ['Example provision one: provisioner pkg:provision spartan/console',]
        )
             ->withArgument('package', 'Which package to install/provision');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $package = $input->getArgument('package');

        [$packageName, $packageVersion] = explode(':', $package) + ['', 'dev-master'];

        // install if not already
        $composer = json_decode(file_get_contents('./composer.json'), true);
        if (!isset($composer['require'][$packageName])) {
            passthru("composer require {$package}");
        }

        $packageJson   = json_decode(file_get_contents("./vendor/{$packageName}/composer.json"), true);
        $installConfig = $packageJson['extra']['spartan']['install'] ?? [];

        /*
         * Automated Uninstall based on installation setup
         */

        if ($installConfig['env'] ?? false) {
            self::handleEnv($installConfig['env']);
        }

        if ($installConfig['copy'] ?? false) {
            self::handleCopy($installConfig['copy']);
        }

        if ($installConfig['commands'] ?? false) {
            self::handleCommands($installConfig['commands']);
        }

        if ($installConfig['routes'] ?? false) {
            self::handleRoutes($installConfig['routes']);
        }

        if ($installConfig['middleware'] ?? false) {
            self::handleMiddleware($installConfig['middleware']);
        }

        if ($installConfig['services'] ?? false) {
            self::handleServices($installConfig['services']);
        }

        /*
         * Additional Uninstall
         */

        $uninstallConfig = $packageJson['extra']['spartan']['uninstall'] ?? [];

        if ($uninstallConfig['scripts'] ?? false) {
            self::handleScripts($uninstallConfig['scripts']);
        }

        if ($uninstallConfig['help'] ?? false) {
            $this->panel(implode("\n", $uninstallConfig['help']));
        }

//        passthru("composer remove {$packageName}");

        return 0;
    }

    public static function handleEnv(array $config, string $file = './config/.env')
    {
        if (!file_exists($file)) {
            file_put_contents($file, '');
        }

        $env = parse_ini_file($file, false, INI_SCANNER_RAW);

        foreach ($config as $key => $value) {
            if (isset($config[$key])) {
                unset($env[$key]);
            }
        }

        $contents      = '';
        $prevNamespace = null;
        foreach ($env as $key => $value) {
            $contents .= "{$key}={$value}\n";
        }

        file_put_contents($file, $contents);
    }

    public static function handleCopy(array $config)
    {
        foreach ($config as $vendorPath => $projectPath) {
            passthru("rm -Rf ./{$projectPath}");
        }
    }

    public static function handleCommands(array $config, string $file = './config/commands.php')
    {
        foreach ($config as $line) {
            self::removeLineFromArray($line, $file);
        }
    }

    public static function handleRoutes(array $config, string $file = './config/routes.php')
    {
        foreach ($config as $line) {
            self::removeLineFromArray($line, $file);
        }
    }

    public static function handleMiddleware(array $config, string $file = './config/middleware.php')
    {
        foreach ($config as $line) {
            self::removeLineFromArray($line, $file);
        }
    }

    public static function handleServices(array $config, string $file = './config/services.php')
    {
        foreach ($config as $line) {
            self::removeLineFromArray($line, $file);
        }
    }

    public static function handleScripts(array $config)
    {
        foreach ($config as $script) {
            passthru($script);
        }
    }

    public static function removeLineFromArray(string $line, string $file)
    {
        $lineToRemove = str_replace(' ', '', $line);
        $lines        = explode("\n", file_get_contents($file));

        foreach ($lines as $lineNumber => $fileLine) {
            $fileLine = trim(str_replace(' ', '', $fileLine), " \t\n\r\0\x0B,;");
            if ($fileLine == $lineToRemove) {
                unset($lines[$lineNumber]);
                break;
            }
        }

        file_put_contents($file, implode("\n", $lines));
    }
}
