<?php

/**
 * 
 */

namespace CC\Api\Models;

    use CC\Api\Models;
    use CC\Api\Sql;
    use CC\Api\Error;
    use CC\Api\Request;
    use CC\Api\Response;
    use CC\Api\Cryptography;
    use CC\Api\Options;

class ApiCronjobs {

    /** Routes */

    public static $routes = [];

	/** General */

	public static $name = 'apiCronjobs';
	public static $nameSingle = 'apiCronjob';

	/** ORM */

	public static $database = 'local';
	public static $table = 'apiCronjobs';
    public static $key = 'id';
	public static $fields = [

        'task' => [ 'column' => 'task', 'datatype' => 'string', 'required' => true ],
        'schedule' => [ 'column' => 'schedule', 'datatype' => 'string', 'required' => true ],
        'enabled' => [ 'column' => 'enabled', 'datatype' => 'boolean' ] ];

    /** Contruct */

    public function __construct(){

        return $this; }

    /**
     * Get all
     * @param  string   $id User ID hash
     * @return array    User(s)
     */
    
    public function get( ){

        /** Get all migrations in array ID store */

        return ( new Sql( 'main' ) )

            -> select( 'apiCronjobs' )
            -> where( 'enabled:true' )
            -> order( 'task' )
            -> execute( );
    
    }

}

?>