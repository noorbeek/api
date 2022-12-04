<?php

namespace CC\Api;

/** PSR-4 Autoloader */

require 'vendor/autoload.php';
require 'options.php';
require 'init.php';

/** Get request */

new Request();

/** Default routes */

Router::add([ 'GET/test' => [ 'roles' => 'w:administrator', 'write' => function(){
        
    return 'Route added for quick testing purposes by an admin'; }] ]);

/** Ger request & execute route */

Router::execute( Router::find( Request::get( 'route' ) ) );

/** End script */

die();
