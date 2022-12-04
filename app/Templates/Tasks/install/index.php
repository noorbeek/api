<?php

namespace {{ namespace }}\App;

use \CC\Api\Request;
use \CC\Api\Router;
use \CC\Api\Response;

/** PSR-4 Autoloader */

require 'vendor/autoload.php';

/** Add app options */

require 'options.php';

/** CC API initiator */

require 'vendor/cc/api/init.php';

/** Initialize HTTP request */

new Request();

/** Default routes */

Router::add([

    /**
     * KEY
     * ---
     * Methods: GET, POST, PUT, DELETE
     * Route:
     * - /endpoint
     * - /regularExpression (i.e. download|file|file[\d]{1,5})
     * - /:id (parameter to pass into callback)
     * 
     * PROPS
     * -----
     * roles: FORCE user-role AUTH: read/write/manage (r, w, x) : rolegroup (i.e. "r:administrator" means authenticated admins (and higher) can read but not write)
     * scope: FORCE scope AUTH: read/write/manage (r, w, x) : rolegroup (resource) <-> scopes (resource)
     * write: return data to ECHO (in requested content-Type or default JSON)
     * view: return PHP/HTML document view
     * run: execute custom PHP code
     * attachment: display/download/stream file (return object [ 'name' => FILENAME, 'data' => BLOB, 'disposition' => 'attachment|inline' ])
     */

    /** Test */

    'GET/test' => [ 'roles' => 'x:administrator', 'write' => function(){
        
        return 'Route added for quick testing purposes by an admin'; }]

]);

/** Ger request & execute route */

Router::execute( Router::find( Request::get( 'route' ) ) );

/** End script */

die();
