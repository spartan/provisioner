<?php

namespace Spartan\Provisioner\Command\App;

use Spartan\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make App
 *
 * @package Spartan\Provisioner
 * @author  Iulian N. <iulian@spartanphp.com>
 * @license LICENSE MIT
 */
class Make extends Command
{
    protected function configure()
    {
        $this->withSynopsis('app:make', 'Create a new spartan app')
             ->withArgument('name', 'App name')
             ->withOption('domain', 'App domain')
             ->withOption('recipe', 'Which recipe to use.')
             ->withOption('api', 'API recipe');
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
        $appName    = $input->getArgument('name');
        $appDomain  = $input->getOption('domain') ?: str_replace(' ', '', strtolower($appName)) . '.com';
        $appRecipes = array_filter(explode(',', $input->getOption('recipe') ?: ''));

        /*
         * 1. Copy files
         */

        $src = __DIR__ . "/../../../data/app";
        $dst = '.';
        passthru("cp -nr {$src}/. {$dst}");

        /*
         * 2. Env file
         */

        $env = [
            'APP_NAME'        => $appName,
            'APP_SLUG'        => strtolower($appName),
            'APP_EMAIL'       => trim(shell_exec('git config user.email')),
            'APP_ENV'         => 'dev',
            'APP_URL'         => 'https://' . $appDomain,
            'APP_DOMAIN'      => $appDomain,
            'APP_TIMEZONE'    => 'UTC',
            'APP_LOCALE'      => 'en-US',
            'APP_CRYPT_SALT'  => sha1(base64_encode(random_bytes(16))),
            'APP_CRYPT_NONCE' => base64_encode(random_bytes(24)),
            'APP_CRYPT_KEY'   => base64_encode(random_bytes(32)),
        ];

        $envContents = [];
        foreach ($env as $key => $value) {
            $envContents[] = "{$key}={$value}";
        }
        file_put_contents("{$dst}/config/.env", implode(PHP_EOL, $envContents));

        /*
         * 3. Recipe in composer
         */

        // get list of recipes
        $user               = get_current_user();
        $recipesJson        = json_decode(file_get_contents("/home/{$user}/.spartan/recipes.json"), true);
        $packagesRequire    = [];
        $packagesRequireDev = [];

        if (!$appRecipes) {
            $recipesChoose = [];
            foreach ($recipesJson['recipes'] as $name => $data) {
                $recipesChoose[$name] = $data['description'] ?: $name;
            }
            $appRecipes = $this->choose('Choose a recipe:', ['Choose one or more recipes:' => $recipesChoose]);
        }

        foreach ($appRecipes as $appRecipe) {
            $packagesRequire    += $recipesJson['recipes'][$appRecipe]['require'] ?? [];
            $packagesRequireDev += $recipesJson['recipes'][$appRecipe]['require-dev'] ?? [];

            $requireFrom    = $recipesJson['recipes'][$appRecipe]['require-from'] ?? null;
            $requireFromDev = $recipesJson['recipes'][$appRecipe]['require-from-dev'] ?? null;

            $inheritedPackagesRequire    = $requireFrom
                ? (array)($recipesJson['recipes'][$requireFrom]['require'] ?? [])
                : [];
            $inheritedPackagesRequireDev = $requireFromDev
                ? (array)($recipesJson['recipes'][$requireFromDev]['require-dev'] ?? [])
                : [];

            $packagesRequire += $inheritedPackagesRequire;
            $packagesRequireDev += $inheritedPackagesRequireDev;
        }

        $composer                = json_decode(file_get_contents('./composer.json'), true);
        $composer['require']     += $packagesRequire;
        $composer['require-dev'] += $packagesRequireDev;

        file_put_contents('./composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        /*
         * 4. Replace in Files
         */

        $files = [
            './composer.json',
            './src/Action/BaseAction.php',
            './src/Action/Home.php',
            './src/Responder/Home.php',
            './src/Responder/HtmlResponder.php',
            './src/Responder/JsonResponder.php',
            './src/Responder/NullResponder.php',
        ];

        foreach ($files as $path) {
            file_put_contents(
                $path,
                str_replace('App\\', $env['APP_NAME'] . '\\', file_get_contents($path)),
            );
        }

        /*
         * 5. Composer update
         */

        passthru('composer update');

        /*
         * 6. Provision
         */

        foreach ($packagesRequire as $packageName => $packageVersion) {
            $this->call("pkg:install", ['package' => $packageName]);
        }

        return 0;
    }
}
