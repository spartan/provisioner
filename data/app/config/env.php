<?php

use Dotenv\Dotenv;

if (!getenv('APP_NAME')) {
    /*
     * Load .env file
     * This should be triggered ONLY in development mode
     * For example if .env is loaded by Docker this will not run
     */
    (Dotenv::createMutable(__DIR__))->load();
}

if (getenv('APP_ENV') == 'dev') {
    /*
     * In development overload the .env
     */
    (Dotenv::createMutable(__DIR__))->load();
}

// define common constants
define('APP_NAME', getenv('APP_NAME'));
define('APP_SLUG', getenv('APP_SLUG'));
define('APP_EMAIL', getenv('APP_EMAIL'));
define('APP_ENV', getenv('APP_ENV'));
define('APP_URL', getenv('APP_URL'));
define('APP_DOMAIN', getenv('APP_DOMAIN'));
define('APP_TIMEZONE', getenv('APP_TIMEZONE'));
define('APP_LOCALE', getenv('APP_LOCALE'));

// define env constants
define('IN_PRODUCTION', APP_ENV == 'prod' || APP_ENV == 'live');
define('IN_REVIEW', APP_ENV == 'rev' || APP_ENV == 'review');
define('IN_STAGING', APP_ENV == 'stage' || APP_ENV == 'staging');
define('IN_TESTING', APP_ENV == 'test');
define('IN_DEVELOPMENT', APP_ENV == 'dev' || APP_ENV == 'devel');
