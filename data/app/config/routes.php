<?php

/*
 * Definition:
 *  ['name', 'method', 'path|regex', 'handler', ['options']]
 *
 * Example:
 *  ['home', 'GET', '/', 'Home']
 *
 * Multiple methods:
 *  ['home', ['get', 'post'], '/', 'Handler']
 *
 * With options (depending on the adapter's capabilities)
 *  ['home', ['get', 'post'], '/', 'Handler', ['middleware' => [...]]
 */

return [
    ['home', 'GET', '/', 'Home'],
];
