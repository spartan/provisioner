<?php

namespace Spartan\Provisioner\Command\App;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provision package
 *
 * @package Spartan\Provisioner
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Install extends Command
{
    protected function configure()
    {
        $this->withSynopsis(
            'pkg:install',
            'Provision app with spartan packages',
            ['provision'],
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

        // let's provision
        $packageJson = json_decode(file_get_contents("./vendor/{$packageName}/composer.json"), true);
        $provisionConfig = $packageJson['extra']['spartan']['install'] ?? [];

        if ($provisionConfig['env'] ?? false) {
            self::handleEnv($provisionConfig['env']);
        }

        if ($provisionConfig['copy'] ?? false) {
            self::handleCopy($provisionConfig['copy'], $packageName);
        }

        if ($provisionConfig['commands'] ?? false) {
            self::handleCommands($provisionConfig['commands']);
        }

        if ($provisionConfig['middleware'] ?? false) {
            self::handleMiddleware($provisionConfig['middleware']);
        }

        if ($provisionConfig['routes'] ?? false) {
            self::handleRoutes($provisionConfig['routes']);
        }

        if ($provisionConfig['services'] ?? false) {
            self::handleServices($provisionConfig['services']);
        }

        if ($provisionConfig['scripts'] ?? false) {
            self::handleScripts($provisionConfig['scripts']);
        }

        if ($provisionConfig['help'] ?? false) {
            $this->panel(implode("\n", $provisionConfig['help']));
        }

        return 0;
    }

    public static function handleEnv(array $config, string $file = './config/.env')
    {
        if (!file_exists($file)) {
            file_put_contents($file, '');
        }

        $env = parse_ini_file($file, false, INI_SCANNER_RAW);
        $env['space'] = '';

        foreach ($config as $key => $value) {
            if (substr($value, 0, 5) == '@php ') {
                $v   = null;
                $php = substr($value, 5);
                eval("\$v = {$php}");
                $env[strtoupper($key)] = $v;
            } else {
                $env[strtoupper($key)] = $value;
            }
        }

        $contents      = '';
        $prevNamespace = null;
        foreach ($env as $key => $value) {
            if ($key == 'space') {
                $contents .= "\n";
            } else {
                $contents .= "{$key}={$value}\n";
            }
        }

        file_put_contents($file, $contents);
    }

    public static function handleCopy(array $config, string $packageBasePath)
    {
        // path inside vendor package => path or file inside project
        $srcPath = "./vendor/{$packageBasePath}/";
        $dstPath = './';

        foreach ($config as $vendorPath => $projectPath) {
            if (file_exists("{$srcPath}{$vendorPath}")) {
                if (is_dir("{$srcPath}{$vendorPath}")) {
                    @mkdir("{$dstPath}{$projectPath}", 0777, true);
                    passthru("cp -nr {$srcPath}{$vendorPath}/. {$dstPath}{$projectPath}");
                } else {
                    passthru("cp -n {$srcPath}{$vendorPath} {$dstPath}{$projectPath}");
                }
            }
        }
    }

    public static function handleCommands(array $config, string $file = './config/commands.php')
    {
        foreach ($config as $line) {
            self::appendLineToArray($line, $file);
        }
    }

    public static function handleRoutes(array $config, string $file = './config/routes.php')
    {
        foreach ($config as $line) {
            self::appendLineToArray($line, $file);
        }
    }

    public static function handleMiddleware(array $config, string $file = './config/middleware.php')
    {
        foreach ($config as $line) {
            self::appendLineToArray($line, $file);
        }
    }

    public static function handleServices(array $config, string $file = './config/services.php')
    {
        foreach ($config as $line) {
            self::appendLineToArray($line, $file);
        }
    }

    public static function handleScripts(array $config)
    {
        foreach ($config as $script) {
            passthru($script);
        }
    }

    public static function appendLineToArray(string $line, string $file, string $pad = '    ')
    {
        $lines = explode("\n", file_get_contents($file));

        $lineNumber = 0;
        foreach ($lines as $lineNumber => $fileLine) {
            if (trim($fileLine) == '];') {
                break;
            }
        }

        if ($lineNumber) {
            array_splice($lines, $lineNumber, null, "{$pad}{$line},");
        }

        file_put_contents($file, implode("\n", $lines));
    }
}
