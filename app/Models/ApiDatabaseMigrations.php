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

class ApiDatabaseMigrations {

    /** Routes */

    public static $routes = [];

	/** General */

	public static $name = 'apiDatabaseMigrations';
	public static $nameSingle = 'apiDatabaseMigration';

	/** ORM */

	public static $database = 'local';
	public static $table = 'apiDatabaseMigrations';
    public static $key = 'id';
	public static $fields = [

        'id' => [ 'column' => 'id', 'datatype' => 'string', 'required' => true ],
        'migratedAt' => [ 'column' => 'migratedAt', 'datatype' => 'datetime', 'readonly' => true ] ];

    /** Contruct */

    public function __construct(){

        return $this; }

    /**
     * Get all
     * @param  string   $id Group ID hash
     * @return array    Group(s)
     */
    
    public function get( $id = null ){

        /** Get from cache or database */

        return ( new Sql( 'main' ) )

            -> select( 'apiDatabaseMigrations' )
            -> where( 'id' . ( $id ? ':' . $id : '!null' ) )
            -> order( 'migratedAt asc' )
            -> limit( 999999 )
            -> execute( ); }

}

?>