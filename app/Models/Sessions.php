<?php

/**
 * 
 */

namespace CC\Api\Models;

    use CC\Api\Models;

class Sessions {

    /** Routes */

    public static $routes = [];

	/** Model */

	public static $name = 'Sessions';
	public static $nameSingle = 'Session';

	/** Database */

	public static $database = 'main';
	public static $table = 'sessions';
    public static $key = 'id';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'integer', 'readonly' => true ],
        'user' => [ 'column' => 'user', 'datatype' => 'hash' ],
        'organization' => [ 'column' => 'organization', 'datatype' => 'hash' ],
        'ip' => [ 'column' => 'ip', 'datatype' => 'string' ],
        'requests' => [ 'column' => 'requests', 'datatype' => 'integer' ],
        'startAt' => [ 'column' => 'startAt', 'datatype' => 'datetime' ],
        'validity' => [ 'column' => 'validity', 'datatype' => 'integer' ],
        'endAt' => [ 'column' => 'endAt', 'datatype' => 'datetime' ],
        'type' => [ 'column' => 'type', 'datatype' => 'switch', 'switch' => [

            'default' => [ 'value' => 'JSON webtoken', 'default' => true ],
            'api_token' => [ 'value' => 'API token' ],
            'http_basic' => [ 'value' => 'HTTP Basic' ],
            'session' => [ 'value' => 'HTTP form' ]

        ]]];

    /** Contruct */

    public function __construct(){

        /** Return */

        return $this; }

}

?>