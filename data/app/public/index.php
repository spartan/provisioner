<?php

use Spartan\Http\Pipeline;

define('SPARTAN_BOOTSTRAP_START', microtime(true));

require __DIR__ . '/../config/bootstrap.php';

define('SPARTAN_REQUEST_START', microtime(true));

/*
 * Request middleware
 */
$response = (new Pipeline(require_once './config/middleware.php'))
    ->handle(container()->get('request'));

define('SPARTAN_RESPONSE_START', microtime(true));

/*
 * Send Response
 */
http($response)->send();

define('SPARTAN_END', microtime(true));
