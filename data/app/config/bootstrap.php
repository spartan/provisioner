<?php

/*
 * Hide errors until handler is instantiated
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);

/*
 * Load vendor
 */
require __DIR__ . '/../vendor/autoload.php';

/*
 * Load environment
 */
require __DIR__ . '/env.php';

/*
 * Include helper functions
 */
require __DIR__ . '/functions.php';

/*
 * Change relative paths to current project root
 */
chdir(__DIR__ . '/../');

/*
 * Boot services
 */
container(
    (new \Spartan\Service\Pipeline(require_once 'services.php'))
        ->handle(new Spartan\Service\Container())
);
